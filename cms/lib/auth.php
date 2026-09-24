<?php
// Admin authentication: sessions, CSRF, login throttling, first-run setup.

if (!defined('CMS_BOOT')) { http_response_code(404); exit; }

const CMS_IDLE_TIMEOUT = 7200;      // 2 h without activity logs you out
const CMS_ABSOLUTE_TIMEOUT = 43200; // 12 h max session length
const CMS_MAX_FAILS = 5;            // per IP per window
const CMS_FAIL_WINDOW = 900;        // 15 min

function cms_is_https()
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

function cms_session_start()
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    // Keep sessions in our own directory rather than the server-wide one.
    $dir = cms_data_path('sessions');
    if (is_dir($dir) || @mkdir($dir, 0770, true)) {
        session_save_path($dir);
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '50');
        ini_set('session.gc_maxlifetime', (string) CMS_IDLE_TIMEOUT);
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('saood_admin');

    $path = cms_config('base_url') . 'admin/';
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params(array(
            'lifetime' => 0, 'path' => $path, 'secure' => cms_is_https(),
            'httponly' => true, 'samesite' => 'Strict',
        ));
    } else {
        // PHP < 7.3 has no samesite option; the path trick is the standard workaround.
        session_set_cookie_params(0, $path . '; samesite=Strict', '', cms_is_https(), true);
    }
    session_start();
}

function cms_is_logged_in()
{
    cms_session_start();
    if (empty($_SESSION['uid'])) return false;
    $now = time();
    if ($now - $_SESSION['last'] > CMS_IDLE_TIMEOUT || $now - $_SESSION['born'] > CMS_ABSOLUTE_TIMEOUT) {
        cms_logout();
        return false;
    }
    // A password change invalidates every other session.
    if (!hash_equals((string) cms_settings('session_epoch'), (string) $_SESSION['epoch'])) {
        cms_logout();
        return false;
    }
    $_SESSION['last'] = $now;
    return true;
}

function cms_logout()
{
    cms_session_start();
    $_SESSION = array();
    session_destroy();
}

function cms_csrf_token()
{
    cms_session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function cms_csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(cms_csrf_token()) . '">';
}

// Every state-changing request must carry the token and come from our own host.
function cms_require_csrf()
{
    $sent = isset($_POST['csrf']) ? $_POST['csrf'] : (isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '');
    $ok = is_string($sent) && $sent !== '' && hash_equals(cms_csrf_token(), $sent);
    if ($ok && isset($_SERVER['HTTP_ORIGIN'])) {
        $origin = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
        $ok = $origin === parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST);
    }
    if (!$ok) {
        http_response_code(403);
        exit('Security token expired. Go back, reload the page and try again.');
    }
}

// ---------------------------------------------------------------------------
// Throttling (used for login and first-run setup)
// ---------------------------------------------------------------------------

function cms_throttle_hit($bucket, $window, $max, $record = true)
{
    $file = cms_data_path('state/throttle_' . $bucket . '.json');
    $fp = @fopen($file, 'c+');
    if (!$fp) {
        if (!is_dir(dirname($file))) @mkdir(dirname($file), 0770, true);
        $fp = @fopen($file, 'c+');
        if (!$fp) return true; // can't track: fail open rather than lock the owner out
    }
    flock($fp, LOCK_EX);
    $data = json_decode(stream_get_contents($fp), true);
    if (!is_array($data)) $data = array();
    $now = time();
    $key = hash('sha256', cms_client_ip());
    foreach ($data as $k => $hits) {
        $data[$k] = array_values(array_filter($hits, function ($t) use ($now, $window) { return $t > $now - $window; }));
        if (!$data[$k]) unset($data[$k]);
    }
    $count = isset($data[$key]) ? count($data[$key]) : 0;
    $allowed = $count < $max;
    if ($record && $allowed) $data[$key][] = $now;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data));
    flock($fp, LOCK_UN);
    fclose($fp);
    return $allowed;
}

function cms_throttle_clear($bucket)
{
    $file = cms_data_path('state/throttle_' . $bucket . '.json');
    $data = cms_read_json($file, array());
    unset($data[hash('sha256', cms_client_ip())]);
    cms_write_json($file, $data);
}

// ---------------------------------------------------------------------------
// Login and setup
// ---------------------------------------------------------------------------

function cms_has_password()
{
    return (string) cms_settings('password_hash') !== '';
}

// Returns null on success or an error message.
function cms_attempt_login($password)
{
    if (!cms_throttle_hit('login', CMS_FAIL_WINDOW, CMS_MAX_FAILS, false)) {
        return 'Too many failed attempts. Wait 15 minutes and try again.';
    }
    $hash = (string) cms_settings('password_hash');
    if ($hash === '' || !password_verify((string) $password, $hash)) {
        cms_throttle_hit('login', CMS_FAIL_WINDOW, CMS_MAX_FAILS, true);
        usleep(400000);
        return 'Wrong password.';
    }
    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        cms_save_settings(array('password_hash' => password_hash($password, PASSWORD_DEFAULT)));
    }
    cms_throttle_clear('login');
    cms_session_start();
    session_regenerate_id(true);
    $_SESSION = array(
        'uid' => 1,
        'born' => time(),
        'last' => time(),
        'epoch' => (string) cms_settings('session_epoch'),
        'csrf' => bin2hex(random_bytes(32)),
    );
    return null;
}

// First run: whoever holds the setup token (a file only you can create over SSH)
// may choose the admin password. The token is deleted once used.
function cms_setup_token_file()
{
    return cms_data_path('SETUP_TOKEN');
}

function cms_complete_setup($token, $password)
{
    if (cms_has_password()) return 'Already set up.';
    if (!cms_throttle_hit('login', CMS_FAIL_WINDOW, CMS_MAX_FAILS, false)) {
        return 'Too many failed attempts. Wait 15 minutes and try again.';
    }
    $file = cms_setup_token_file();
    $expected = is_file($file) ? trim((string) file_get_contents($file)) : '';
    if (strlen($expected) < 16 || !hash_equals($expected, trim((string) $token))) {
        cms_throttle_hit('login', CMS_FAIL_WINDOW, CMS_MAX_FAILS, true);
        return 'Invalid setup token.';
    }
    $err = cms_password_problem($password);
    if ($err) return $err;
    if (!cms_save_settings(array(
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'session_epoch' => bin2hex(random_bytes(8)),
    ))) {
        return 'Could not write settings.json. Check that the data folder is writable by PHP.';
    }
    @unlink($file);
    return cms_attempt_login($password);
}

function cms_password_problem($password)
{
    if (strlen((string) $password) < 12) return 'Use at least 12 characters.';
    return null;
}

function cms_change_password($current, $new)
{
    if (!password_verify((string) $current, (string) cms_settings('password_hash'))) return 'Current password is wrong.';
    $err = cms_password_problem($new);
    if ($err) return $err;
    $epoch = bin2hex(random_bytes(8));
    cms_save_settings(array('password_hash' => password_hash($new, PASSWORD_DEFAULT), 'session_epoch' => $epoch));
    $_SESSION['epoch'] = $epoch; // keep this session, kill the others
    return null;
}
