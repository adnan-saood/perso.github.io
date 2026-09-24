<?php
// One-shot server capability check. Upload to public_html, open
//   https://perso.ensta.fr/~saood/server-check.php?key=07f837cacfc568e82d364778
// copy the output, then DELETE this file from the server.
// Compatible with PHP 5.6+ on purpose (we don't know the version yet).

if (!isset($_GET['key']) || $_GET['key'] !== '07f837cacfc568e82d364778') {
    http_response_code(404);
    exit;
}
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

function line($k, $v) { echo str_pad($k, 28) . ': ' . $v . "\n"; }
function yn($b) { return $b ? 'yes' : 'no'; }

echo "== PHP ==\n";
line('PHP version', PHP_VERSION);
line('SAPI', PHP_SAPI);
line('Server software', isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '?');
$euid = function_exists('posix_geteuid') ? posix_geteuid() : null;
$pw = ($euid !== null && function_exists('posix_getpwuid')) ? posix_getpwuid($euid) : null;
line('Runs as user', $pw ? $pw['name'] . " (uid $euid)" : '?');
line('File owner', get_current_user());
line('memory_limit', ini_get('memory_limit'));
line('upload_max_filesize', ini_get('upload_max_filesize'));
line('post_max_size', ini_get('post_max_size'));
line('max_execution_time', ini_get('max_execution_time'));
line('open_basedir', ini_get('open_basedir') ?: '(none)');
line('disable_functions', ini_get('disable_functions') ?: '(none)');
line('file_uploads', ini_get('file_uploads'));

echo "\n== Extensions ==\n";
foreach (array('pdo_sqlite', 'sqlite3', 'pdo_mysql', 'mysqli', 'gd', 'imagick', 'mbstring',
               'json', 'fileinfo', 'zip', 'openssl', 'curl', 'intl', 'xml', 'dom') as $ext) {
    line($ext, yn(extension_loaded($ext)));
}
if (class_exists('PDO')) line('PDO drivers', implode(', ', PDO::getAvailableDrivers()));

echo "\n== Mail ==\n";
$disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
line('mail() available', yn(function_exists('mail') && !in_array('mail', $disabled)));
line('sendmail_path', ini_get('sendmail_path') ?: '(empty)');
line('SMTP ini', ini_get('SMTP') . ':' . ini_get('smtp_port'));
line('/usr/sbin/sendmail exists', yn(@file_exists('/usr/sbin/sendmail')));
$s = @fsockopen('smtp.gmail.com', 587, $e, $es, 4);
if ($s) fclose($s);
line('outbound socket smtp:587', $s ? 'open' : "blocked ($es)");

echo "\n== Filesystem ==\n";
$here = __DIR__;
$parent = dirname($here);
line('Script dir', $here);
line('Script dir writable', yn(is_writable($here)));
line('Parent dir', $parent);
line('Parent dir writable', yn(@is_writable($parent)));
$probe = $here . '/.probe_' . bin2hex(openssl_random_pseudo_bytes(4));
$ok = @file_put_contents($probe, 'x') !== false;
line('Can create file here', yn($ok));
if ($ok) {
    line('Created file owner uid', fileowner($probe));
    @unlink($probe);
}
$df = @disk_free_space($here);
line('Disk free', $df ? round($df / 1048576) . ' MB' : '?');

echo "\n== Apache ==\n";
if (function_exists('apache_get_modules')) {
    $mods = apache_get_modules();
    foreach (array('mod_rewrite', 'mod_headers', 'mod_php7', 'mod_php5', 'mod_userdir', 'mod_deflate', 'mod_expires') as $m) {
        line($m, yn(in_array($m, $mods)));
    }
} else {
    line('apache_get_modules', 'not available (PHP not running as Apache module)');
}

// Does Apache honour .htaccess here, and does it serve index.php as directory index?
if ($ok && function_exists('curl_init')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $t = $here . '/.probe_dir_' . bin2hex(openssl_random_pseudo_bytes(4));
    $name = basename($t);
    @mkdir($t);
    @mkdir($t . '/deny');
    @mkdir($t . '/idx');
    @file_put_contents($t . '/deny/.htaccess', "Require all denied\n");
    @file_put_contents($t . '/deny/a.txt', 'visible');
    @file_put_contents($t . '/idx/index.php', '<?php echo "php-index";');
    @file_put_contents($t . '/rw.htaccess.txt', '');
    @file_put_contents($t . '/.htaccess', "RewriteEngine On\nRewriteRule ^rewritten$ idx/index.php [L]\n");
    $get = function ($url) {
        $c = curl_init($url);
        curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6,
                                    CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0));
        $body = curl_exec($c);
        $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        curl_close($c);
        return array($code, $body);
    };
    list($c1) = $get("$base/$name/deny/a.txt");
    line('.htaccess honoured', $c1 == 403 ? 'yes (403)' : ($c1 == 500 ? 'error 500 (overrides restricted)' : "no ($c1)"));
    list($c2, $b2) = $get("$base/$name/idx/");
    line('index.php as dir index', ($c2 == 200 && trim($b2) === 'php-index') ? 'yes' : "no ($c2)");
    list($c3, $b3) = $get("$base/$name/rewritten");
    line('mod_rewrite in .htaccess', ($c3 == 200 && trim($b3) === 'php-index') ? 'yes' : "no ($c3)");
    foreach (array('deny/.htaccess', 'deny/a.txt', 'idx/index.php', 'rw.htaccess.txt', '.htaccess') as $f) @unlink("$t/$f");
    @rmdir("$t/deny"); @rmdir("$t/idx"); @rmdir($t);
} else {
    line('HTTP self-tests', 'skipped (no write access or no curl)');
}

echo "\nDone. Delete server-check.php from the server now.\n";
