<?php
// Admin panel: homepage, posts, news, projects, publications, talks, CV, repositories,
// analytics, files, trash, settings.
define('CMS_ADMIN', 1);
require_once __DIR__ . '/../cms/view.php'; // boot + shared renderers (talk kinds, pub types)

header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' data: https://cdnjs.cloudflare.com; img-src 'self' data: blob: https:; connect-src 'self' https://api.github.com; frame-ancestors 'none'; form-action 'self'; base-uri 'none'");
header('Referrer-Policy: same-origin');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

$page = isset($_GET['page']) && is_string($_GET['page']) ? $_GET['page'] : 'dashboard';
$types = cms_types();

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function admin_url(array $q = array())
{
    return cms_url('admin/') . ($q ? '?' . http_build_query($q) : '');
}

function admin_redirect(array $q = array())
{
    header('Location: ' . admin_url($q), true, 303);
    exit;
}

function admin_flash($type, $msg)
{
    $_SESSION['flash'][] = array($type, $msg);
}

function admin_post($key, $default = '')
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : $default;
}

function admin_json($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function admin_icon($name)
{
    $paths = array(
        'home' => 'M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z',
        'posts' => 'M5 4h14v16H5zM8 8h8M8 12h8M8 16h5',
        'news' => 'M4 5h13v14H6a2 2 0 0 1-2-2zM17 9h3v8a2 2 0 0 1-2 2M7 9h7M7 13h7',
        'folder' => 'M3 6a1 1 0 0 1 1-1h5l2 2h9a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z',
        'trash' => 'M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13',
        'gear' => 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 1 1-4 0v-.1a1.6 1.6 0 0 0-2.7-1.1l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0-1.1-2.7H3a2 2 0 1 1 0-4h.1a1.6 1.6 0 0 0 1.1-2.7l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 2.7-1.1V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0 1.1 2.7H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z',
        'plus' => 'M12 5v14M5 12h14',
        'cube' => 'M12 3l8 4.5v9L12 21l-8-4.5v-9zM12 12l8-4.5M12 12v9M12 12L4 7.5',
        'out' => 'M15 4h4v16h-4M10 8l-4 4 4 4M6 12h11',
        'ext' => 'M14 4h6v6M20 4l-9 9M18 14v6H4V6h6',
        'file' => 'M6 3h8l4 4v14H6zM14 3v4h4',
        'up' => 'M12 19V5M5 12l7-7 7 7',
        'book' => 'M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2zM4 19V5M8 7h7',
        'id' => 'M4 5h16v14H4zM8 10a2 2 0 1 0 4 0 2 2 0 0 0-4 0M7 16c.5-1.5 2-2 3-2s2.5.5 3 2M15 9h3M15 12h3',
        'code' => 'M8 8l-4 4 4 4M16 8l4 4-4 4M13 5l-2 14',
        'chart' => 'M4 20V10M10 20V4M16 20v-7M22 20H2',
        'grip' => 'M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01',
        'layout' => 'M4 4h16v6H4zM4 14h7v6H4zM15 14h5v6h-5z',
        'mic' => 'M12 3a3 3 0 0 0-3 3v5a3 3 0 0 0 6 0V6a3 3 0 0 0-3-3zM6 11a6 6 0 0 0 12 0M12 17v4M8 21h8',
        'download' => 'M12 4v12M6 10l6 6 6-6M4 20h16',
    );
    $d = isset($paths[$name]) ? $paths[$name] : '';
    return '<svg class="ic" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $d . '"/></svg>';
}

function admin_head($title)
{
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo e($title); ?> · Site admin</title>
<link rel="stylesheet" href="admin.css?v=6">
<?php
}

function admin_layout_start($title, $active)
{
    admin_head($title);
    if ($active === 'edit') {
        echo '<link rel="stylesheet" href="lib/easymde.min.css">';
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">';
    }
    $nav = array(
        'dashboard' => array('Dashboard', 'home', array()),
        'homepage' => array('Homepage', 'layout', array('page' => 'home')),
        'posts' => array('Blog posts', 'posts', array('page' => 'items', 'type' => 'posts')),
        'news' => array('News', 'news', array('page' => 'items', 'type' => 'news')),
        'projects' => array('Projects', 'cube', array('page' => 'items', 'type' => 'projects')),
        'publications' => array('Publications', 'book', array('page' => 'items', 'type' => 'publications')),
        'talks' => array('Talks & media', 'mic', array('page' => 'items', 'type' => 'talks')),
        'cv' => array('CV', 'id', array('page' => 'cv')),
        'repos' => array('Repositories', 'code', array('page' => 'repos')),
        'analytics' => array('Analytics', 'chart', array('page' => 'analytics')),
        'files' => array('Files', 'folder', array('page' => 'files')),
        'trash' => array('Trash', 'trash', array('page' => 'trash')),
        'settings' => array('Settings', 'gear', array('page' => 'settings')),
    );
    ?>
</head>
<body>
<div class="shell">
  <aside class="side">
    <a class="brand" href="<?php echo e(admin_url()); ?>"><span class="dot"></span><?php echo e(cms_config('site_name')); ?></a>
    <nav>
      <?php foreach ($nav as $key => $n): ?>
        <a class="<?php echo $active === $key ? 'on' : ''; ?>" href="<?php echo e(admin_url($n[2])); ?>">
          <?php echo admin_icon($n[1]); ?><span><?php echo e($n[0]); ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="side-foot">
      <a href="<?php echo e(cms_url()); ?>" target="_blank" rel="noopener"><?php echo admin_icon('ext'); ?><span>View site</span></a>
      <form method="post" action="<?php echo e(admin_url()); ?>">
        <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="logout">
        <button class="linklike"><?php echo admin_icon('out'); ?><span>Log out</span></button>
      </form>
    </div>
  </aside>
  <main class="main">
    <?php
    if (!empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $f) echo '<div class="flash ' . e($f[0]) . '">' . e($f[1]) . '</div>';
        $_SESSION['flash'] = array();
    }
}

function admin_layout_end($scripts = array())
{
    echo '</main></div>';
    foreach ($scripts as $s) echo '<script src="' . e($s) . '"></script>';
    echo '<script src="admin.js?v=6"></script></body></html>';
}

// ---------------------------------------------------------------------------
// Preconditions: data folder, first-run setup, login
// ---------------------------------------------------------------------------

$dataDir = cms_data_path();
if (!is_dir($dataDir) || !is_writable($dataDir)) {
    admin_head('Setup needed');
    echo '</head><body class="center"><div class="card narrow"><h1>Almost there</h1>'
        . '<p>The data folder <code>' . e($dataDir) . '</code> does not exist or PHP cannot write to it.</p>'
        . '<p>Create it over SSH with the commands in <code>ADMIN.md</code> (section “First-time setup”), then reload this page.</p></div></body></html>';
    exit;
}

cms_session_start();

if (!cms_has_password()) {
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        cms_require_csrf();
        if (admin_post('password') !== admin_post('password2')) $error = 'The two passwords do not match.';
        else $error = cms_complete_setup(admin_post('token'), admin_post('password'));
        if ($error === null) { admin_flash('ok', 'Welcome! Your admin panel is ready.'); admin_redirect(); }
    }
    admin_head('First-time setup');
    ?></head><body class="center">
    <form class="card narrow" method="post">
      <h1>Create your admin password</h1>
      <p class="muted">Paste the setup token printed by <code>deploy.sh init</code> (it's in <code>cms-data/SETUP_TOKEN</code> on the server).</p>
      <?php if ($error): ?><div class="flash err"><?php echo e($error); ?></div><?php endif; ?>
      <?php echo cms_csrf_field(); ?>
      <label>Setup token<input name="token" required autocomplete="off"></label>
      <label>New password <small>(12+ characters)</small><input type="password" name="password" minlength="12" required autocomplete="new-password"></label>
      <label>Repeat password<input type="password" name="password2" minlength="12" required autocomplete="new-password"></label>
      <button class="btn primary">Save and log in</button>
    </form></body></html><?php
    exit;
}

if (!cms_is_logged_in()) {
    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_post('action') === 'login') {
        cms_require_csrf();
        $error = cms_attempt_login(admin_post('password'));
        if ($error === null) {
            $next = isset($_GET['page']) ? $_GET : array();
            admin_redirect($next);
        }
    }
    admin_head('Log in');
    ?></head><body class="center">
    <form class="card narrow login" method="post">
      <div class="brand big"><span class="dot"></span><?php echo e(cms_config('site_name')); ?></div>
      <?php if ($error): ?><div class="flash err"><?php echo e($error); ?></div><?php endif; ?>
      <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="login">
      <label>Password<input type="password" name="password" required autofocus autocomplete="current-password"></label>
      <button class="btn primary">Log in</button>
    </form></body></html><?php
    exit;
}

// ---------------------------------------------------------------------------
// Actions (POST, CSRF-protected, then redirect)
// ---------------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cms_require_csrf();
    $action = admin_post('action');

    switch ($action) {
        case 'logout':
            cms_logout();
            admin_redirect();

        case 'save_item':
            $type = admin_post('type');
            if (!isset($types[$type])) admin_redirect();
            $orig = admin_post('orig_slug');
            $title = trim(admin_post('title'));
            $slug = cms_slugify(admin_post('slug') !== '' ? admin_post('slug') : $title);
            if ($slug === '') $slug = date('Y-m-d') . '-' . $type;
            $existing = $orig !== '' ? cms_load_item($type, $orig) : null;

            $date = admin_post('date') !== '' ? admin_post('date') : date('Y-m-d');
            if (admin_post('time') !== '') $date .= ' ' . admin_post('time');
            if (strtotime($date) === false) $date = date('Y-m-d');

            // Keep front-matter keys we don't edit here (layout, giscus_comments, ...).
            $undated = array('projects', 'publications');
            $meta = $existing ? $existing['meta'] : (in_array($type, $undated, true) ? array() : array('layout' => 'post'));
            $meta['title'] = $title;
            if (!in_array($type, $undated, true)) $meta['date'] = $date;
            $meta['draft'] = !empty($_POST['draft']);
            if ($type === 'publications') {
                $meta['authors'] = cms_people(admin_post('authors'));
                foreach (array('venue', 'year', 'badge', 'award', 'note', 'doi', 'url', 'pdf', 'code', 'video', 'slides', 'image') as $k) {
                    $meta[$k] = trim(admin_post($k));
                }
                $meta['doi'] = preg_replace('#^https?://(dx\.)?doi\.org/#', '', $meta['doi']);
                $meta['pubtype'] = array_key_exists(admin_post('pubtype'), cms_pub_types_admin()) ? admin_post('pubtype') : 'other';
                $meta['bibtex'] = trim(str_replace("\r\n", "\n", admin_post('bibtex')));
                $meta['selected'] = !empty($_POST['selected']);
                if ($meta['selected']) {
                    $meta['home_order'] = max(1, (int) admin_post('home_order', '99'));
                } else {
                    unset($meta['selected'], $meta['home_order']);
                }
            } elseif ($type === 'talks') {
                foreach (array('event', 'location', 'image', 'video', 'slides', 'url', 'code', 'post', 'award') as $k) $meta[$k] = trim(admin_post($k));
                $meta['kind'] = array_key_exists(admin_post('kind'), cms_talk_kinds_admin()) ? admin_post('kind') : 'talk';
                $meta['featured'] = !empty($_POST['featured']);
            } elseif ($type === 'projects') {
                $meta['model'] = trim(admin_post('model'));
                $meta['model_home'] = !empty($_POST['model_home']);
                if (!$meta['model_home']) unset($meta['model_home']);
                $meta['description'] = trim(admin_post('description'));
                $meta['category'] = trim(admin_post('category'));
                $meta['img'] = trim(admin_post('img'));
                $meta['importance'] = max(1, (int) admin_post('importance', '50'));
                $meta['github'] = trim(admin_post('github'));
                $meta['url'] = trim(admin_post('url'));
                $meta['featured'] = !empty($_POST['featured']);
            } elseif ($type === 'posts') {
                $meta['description'] = trim(admin_post('description'));
                $meta['tags'] = cms_list(admin_post('tags'));
                $meta['categories'] = cms_list(admin_post('categories'));
                $meta['thumbnail'] = trim(admin_post('thumbnail'));
                $meta['featured'] = !empty($_POST['featured']);
            } else {
                $meta['inline'] = !empty($_POST['inline']);
            }
            // Optional French versions and the generated share image.
            foreach (array('title_fr', 'description_fr', 'event_fr', 'body_fr') as $k) {
                $v = trim(str_replace("\r\n", "\n", admin_post($k)));
                if ($v === '') unset($meta[$k]); else $meta[$k] = $v;
            }
            if (admin_post('og_image') !== '') $meta['og_image'] = trim(admin_post('og_image'));
            foreach (array('draft', 'featured', 'inline') as $flag) {
                if (isset($meta[$flag]) && $meta[$flag] === false && $flag !== 'inline') unset($meta[$flag]);
            }

            if ($slug !== $orig && cms_load_item($type, $slug)) {
                admin_flash('err', 'Another ' . $types[$type]['singular'] . ' already uses the address "' . $slug . '". Choose a different slug.');
                $_SESSION['draft_body'] = admin_post('body');
                admin_redirect(array('page' => 'edit', 'type' => $type, 'slug' => $orig));
            }
            if (!cms_save_item($type, $slug, $meta, admin_post('body'))) {
                admin_flash('err', 'Could not save. Is the data folder writable?');
                admin_redirect(array('page' => 'edit', 'type' => $type, 'slug' => $orig));
            }
            if ($orig !== '' && $orig !== $slug) cms_delete_item($type, $orig);
            if ($type === 'publications') admin_pub_renumber(array(), $slug);
            admin_flash('ok', 'Saved' . ($meta['draft'] ? ' as draft' : ' and published') . '.');
            admin_redirect(array('page' => 'edit', 'type' => $type, 'slug' => $slug));

        case 'pub_import':
            $pub = cms_bibtex_to_publication(admin_post('bibtex'));
            if (!$pub || $pub['meta']['title'] === '') {
                admin_flash('err', 'Could not read that BibTeX entry. Paste one complete @type{key, ...} entry.');
                admin_redirect(array('page' => 'items', 'type' => 'publications'));
            }
            $slug = $pub['slug'] !== '' ? $pub['slug'] : cms_slugify($pub['meta']['title']);
            $base = $slug;
            $n = 2;
            while (cms_load_item('publications', $slug)) $slug = $base . '-' . $n++;
            cms_save_item('publications', $slug, array_filter($pub['meta'], function ($v) { return $v !== '' && $v !== array(); }), $pub['abstract']);
            admin_flash('ok', 'Imported. Check the details, add a picture, then save.');
            admin_redirect(array('page' => 'edit', 'type' => 'publications', 'slug' => $slug));

        case 'pub_home_order': // AJAX: slugs of homepage publications, in order
            $order = isset($_POST['order']) && is_array($_POST['order']) ? array_map('strval', $_POST['order']) : array();
            admin_json(array('ok' => true, 'saved' => admin_pub_renumber($order)));

        case 'home_save':
            $doc = array('en' => cms_home_sanitize(isset($_POST['en']) && is_array($_POST['en']) ? $_POST['en'] : array()),
                         'fr' => cms_home_sanitize(isset($_POST['fr']) && is_array($_POST['fr']) ? $_POST['fr'] : array()));
            $ok = cms_doc_save('home', $doc);
            admin_flash($ok ? 'ok' : 'err', $ok ? 'Homepage saved. It is live now.' : 'Could not save the homepage.');
            admin_redirect(array('page' => 'home', 'lang' => admin_post('lang') === 'fr' ? 'fr' : 'en'));

        case 'og_upload': // AJAX: share image generated in the browser for one item
            $type = admin_post('type');
            $slug = cms_slugify(admin_post('slug'));
            $files = cms_files_from_request('image');
            if (!isset($types[$type]) || $slug === '' || !$files || $files[0]['error'] !== UPLOAD_ERR_OK) admin_json(array('error' => 'Bad request.'), 400);
            $info = @getimagesize($files[0]['tmp_name']);
            if (!$info || $info[2] !== IMAGETYPE_PNG) admin_json(array('error' => 'Not a PNG.'), 400);
            $dir = cms_files_ensure_dir('og');
            if (!$dir || !move_uploaded_file($files[0]['tmp_name'], $dir . '/' . $type . '-' . $slug . '.png')) admin_json(array('error' => 'Could not save.'), 500);
            @chmod($dir . '/' . $type . '-' . $slug . '.png', 0644);
            admin_json(array('url' => cms_config('files_url') . 'og/' . $type . '-' . $slug . '.png?v=' . time()));

        case 'backup_download':
            @set_time_limit(300);
            header('Content-Type: application/x-tar');
            header('Content-Disposition: attachment; filename="website-backup-' . date('Y-m-d') . '.tar"');
            header('Cache-Control: no-store');
            $out = fopen('php://output', 'wb');
            cms_backup_write($out);
            fclose($out);
            exit;

        case 'backup_restore':
            $files = cms_files_from_request('archive');
            if (!$files || $files[0]['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($files[0]['tmp_name'])) {
                admin_flash('err', 'No archive received (the file may be larger than ' . ini_get('upload_max_filesize') . ').');
                admin_redirect(array('page' => 'settings'));
            }
            $safety = cms_backup_snapshot('before-restore');
            list($n, $errors) = cms_backup_restore($files[0]['tmp_name']);
            admin_flash($n ? 'ok' : 'err', 'Restored ' . $n . ' files.' . ($safety ? ' The previous state was saved in cms-data/backups/.' : '')
                . ($errors ? ' ' . count($errors) . ' entries were skipped.' : ''));
            admin_redirect(array('page' => 'settings'));

        case 'cv_save':
            $cv = cms_cv_sanitize(isset($_POST['cv']) && is_array($_POST['cv']) ? $_POST['cv'] : array());
            $ok = cms_doc_save('cv', $cv);
            admin_flash($ok ? 'ok' : 'err', $ok ? 'CV saved. It is live on /cv/.' : 'Could not save the CV.');
            admin_redirect(array('page' => 'cv'));

        case 'repos_save':
            $doc = cms_repos_sanitize(array('user' => admin_post('user'), 'repos' => isset($_POST['repos']) && is_array($_POST['repos']) ? $_POST['repos'] : array()));
            admin_flash(cms_doc_save('repositories', $doc) ? 'ok' : 'err', 'Saved ' . count($doc['repos']) . ' repositories.');
            admin_redirect(array('page' => 'repos'));

        case 'delete_item':
            $type = admin_post('type');
            if (isset($types[$type]) && cms_delete_item($type, admin_post('slug'))) admin_flash('ok', 'Moved to trash.');
            else admin_flash('err', 'Could not delete.');
            admin_redirect(array('page' => 'items', 'type' => $type));

        case 'upload_image': // AJAX from the editor
            $dir = cms_files_ensure_dir('uploads/' . date('Y'));
            $files = cms_files_from_request('image');
            if (!$dir || !$files) admin_json(array('error' => 'No file received.'), 400);
            list($rel, $err) = cms_files_store_upload($files[0], $dir);
            if ($err) admin_json(array('error' => $err), 400);
            // Base-free path so the content works locally and on the server.
            admin_json(array('url' => cms_config('files_url') . $rel));

        case 'media_upload': // AJAX from the media picker
            $dir = cms_files_resolve(admin_post('dir'));
            if (!$dir || !is_dir($dir)) admin_json(array('error' => 'Folder not found.'), 400);
            $done = array();
            $errors = array();
            foreach (cms_files_from_request('files') as $f) {
                list($rel, $err) = cms_files_store_upload($f, $dir);
                if ($err) $errors[] = $err; else $done[] = $rel;
            }
            if (!$done && !$errors) $errors[] = 'Nothing was received. The files may exceed the server limit (' . ini_get('post_max_size') . ').';
            admin_json(array('uploaded' => $done, 'errors' => $errors), $done ? 200 : 400);

        case 'files_upload':
            $dirRel = admin_post('dir');
            $dir = cms_files_resolve($dirRel);
            if (!$dir || !is_dir($dir)) admin_redirect(array('page' => 'files'));
            $ok = 0;
            foreach (cms_files_from_request('files') as $f) {
                list($rel, $err) = cms_files_store_upload($f, $dir);
                if ($err) admin_flash('err', $err); else $ok++;
            }
            if ($ok) admin_flash('ok', $ok . ' file' . ($ok > 1 ? 's' : '') . ' uploaded.');
            if (!$_FILES) admin_flash('err', 'Nothing was uploaded. The files may exceed the server limit (' . ini_get('post_max_size') . ').');
            admin_redirect(array('page' => 'files', 'dir' => $dirRel));

        case 'files_mkdir':
            $dirRel = admin_post('dir');
            $parent = cms_files_resolve($dirRel);
            $name = cms_safe_filename(admin_post('name'), true);
            if ($parent && is_dir($parent) && !file_exists($parent . '/' . $name) && @mkdir($parent . '/' . $name, 0775)) {
                admin_flash('ok', 'Folder "' . $name . '" created.');
            } else {
                admin_flash('err', 'Could not create that folder (does it already exist?).');
            }
            admin_redirect(array('page' => 'files', 'dir' => $dirRel));

        case 'files_rename':
            $dirRel = admin_post('dir');
            $src = cms_files_resolve(admin_post('path'));
            $isDir = $src && is_dir($src);
            $name = cms_safe_filename(admin_post('name'), $isDir);
            if (!$src || $src === cms_files_root() || $name === null) {
                admin_flash('err', 'Invalid name or file type not allowed.');
            } elseif (file_exists(dirname($src) . '/' . $name)) {
                admin_flash('err', 'Something with that name already exists.');
            } elseif (@rename($src, dirname($src) . '/' . $name)) {
                admin_flash('ok', 'Renamed to "' . $name . '".');
            } else {
                admin_flash('err', 'Rename failed.');
            }
            admin_redirect(array('page' => 'files', 'dir' => $dirRel));

        case 'files_delete':
            $dirRel = admin_post('dir');
            $paths = isset($_POST['paths']) && is_array($_POST['paths']) ? $_POST['paths'] : array(admin_post('path'));
            $n = 0;
            foreach ($paths as $p) {
                $abs = cms_files_resolve((string) $p);
                if (!$abs || $abs === cms_files_root()) continue;
                if (cms_move_to_trash($abs, basename($abs), array('kind' => 'file', 'path' => cms_files_rel($abs)))) $n++;
            }
            admin_flash($n ? 'ok' : 'err', $n ? $n . ' item' . ($n > 1 ? 's' : '') . ' moved to trash.' : 'Nothing deleted.');
            admin_redirect(array('page' => 'files', 'dir' => $dirRel));

        case 'trash_restore':
            $err = cms_trash_restore(admin_post('entry'));
            admin_flash($err ? 'err' : 'ok', $err ? $err : 'Restored.');
            admin_redirect(array('page' => 'trash'));

        case 'trash_empty':
            cms_trash_empty();
            admin_flash('ok', 'Trash emptied.');
            admin_redirect(array('page' => 'trash'));

        case 'settings_password':
            if (admin_post('new') !== admin_post('new2')) $err = 'The new passwords do not match.';
            else $err = cms_change_password(admin_post('current'), admin_post('new'));
            admin_flash($err ? 'err' : 'ok', $err ? $err : 'Password changed. Other sessions were logged out.');
            admin_redirect(array('page' => 'settings'));
    }
    admin_redirect();
}

// ---------------------------------------------------------------------------
// Pages
// ---------------------------------------------------------------------------

switch ($page) {

case 'media': // JSON listing used by the media picker in the editor
    $src = isset($_GET['src']) && $_GET['src'] === 'site' ? 'site' : 'files';
    $abs = cms_files_resolve(isset($_GET['dir']) ? (string) $_GET['dir'] : '', true, $src);
    if (!$abs || !is_dir($abs)) admin_json(array('error' => 'Folder not found.'), 404);
    list($dirs, $files) = cms_files_list($abs, $src);
    $rel = cms_files_rel($abs, $src);
    $images = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'ico');
    $out = array('src' => $src, 'writable' => cms_media_source($src)['writable'], 'dir' => $rel,
                 'parent' => $rel === '' ? null : (dirname($rel) === '.' ? '' : dirname($rel)), 'dirs' => array(), 'files' => array());
    foreach ($dirs as $d) $out['dirs'][] = array('name' => $d['name'], 'rel' => $d['rel']);
    foreach ($files as $f) {
        $out['files'][] = array(
            'name' => $f['name'],
            'path' => cms_files_content_path($f['rel'], $src), // base-free, for content
            'url' => cms_files_public_url($f['rel'], $src),     // for thumbnails
            'image' => in_array($f['ext'], $images, true),
            'size' => cms_human_size($f['size']),
        );
    }
    admin_json($out);

case 'items':
    $type = isset($_GET['type'], $types[$_GET['type']]) ? $_GET['type'] : 'posts';
    $items = cms_list_items($type, true);
    admin_layout_start($types[$type]['label'], $type);
    ?>
    <header class="top">
      <h1><?php echo e($types[$type]['label']); ?></h1>
      <a class="btn primary" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => $type))); ?>"><?php echo admin_icon('plus'); ?> New <?php echo e($types[$type]['singular']); ?></a>
    </header>
    <?php if ($type === 'projects'): ?>
      <p class="muted small">Projects are shown in order of their number (1 first). Ticked “Show on homepage” projects appear on the homepage.</p>
    <?php endif; ?>
    <?php if ($type === 'publications'):
        $home = array_values(array_filter($items, function ($x) { return $x['selected']; }));
        usort($home, function ($a, $b) { return $a['home_order'] - $b['home_order']; }); ?>
      <div class="cards two">
        <section class="card">
          <h2>On the homepage</h2>
          <p class="muted small">Drag to reorder, then save. Tick “Show on homepage” inside a publication to add it here.</p>
          <ol class="sortable" id="home-order" data-save-url="<?php echo e(admin_url()); ?>" data-csrf="<?php echo e(cms_csrf_token()); ?>">
            <?php foreach ($home as $h): ?>
              <li draggable="true" data-slug="<?php echo e($h['slug']); ?>"><span class="grip"><?php echo admin_icon('grip'); ?></span>
                <span class="grow"><?php echo e($h['title']); ?></span><span class="muted small"><?php echo e($h['badge'] . ' ' . $h['year']); ?></span></li>
            <?php endforeach; ?>
            <?php if (!$home): ?><li class="empty">No publication is on the homepage yet.</li><?php endif; ?>
          </ol>
          <div class="actions"><button type="button" class="btn primary small" data-save-order>Save order</button></div>
        </section>
        <form method="post" class="card">
          <h2>Import from BibTeX</h2>
          <p class="muted small">Paste one entry (from Google Scholar, IEEE, …). Title, authors, venue, year, DOI and abstract are filled in for you.</p>
          <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="pub_import">
          <textarea name="bibtex" rows="6" placeholder="@inproceedings{key,&#10;  title={...},&#10;  author={...},&#10;  ...&#10;}" required></textarea>
          <div class="actions"><button class="btn primary small">Import</button></div>
        </form>
      </div>
    <?php endif; ?>
    <input class="search" type="search" placeholder="Filter…" data-filter="#item-list">
    <div class="list" id="item-list">
      <?php if (!$items): ?><p class="empty">Nothing here yet.</p><?php endif; ?>
      <?php foreach ($items as $it): ?>
        <a class="row" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => $type, 'slug' => $it['slug']))); ?>">
          <?php if ($type === 'projects'): ?>
            <span class="date">#<?php echo (int) $it['importance']; ?></span>
          <?php elseif ($type === 'publications'): ?>
            <span class="date"><?php echo e($it['year']); ?></span>
          <?php elseif ($type === 'talks'): ?>
            <span class="date"><?php echo date('M j, Y', $it['date']); ?></span>
          <?php else: ?>
            <span class="date"><?php echo date('M j, Y', $it['date']); ?></span>
          <?php endif; ?>
          <span class="grow"><strong><?php echo e($it['title'] !== '' ? $it['title'] : cms_excerpt($it, 80)); ?></strong>
            <?php if ($it['draft']): ?><em class="pill">draft</em><?php endif; ?>
            <?php if (in_array($type, array('posts', 'news'), true) && $it['date'] > time()): ?><em class="pill blue">scheduled</em><?php endif; ?>
            <?php if ($type === 'talks' && $it['date'] > time()): ?><em class="pill blue">upcoming</em><?php endif; ?>
            <?php if (!empty($it['model'])): ?><em class="pill">3D</em><?php endif; ?>
            <?php if (!empty($it['meta']['title_fr']) || !empty($it['meta']['body_fr'])): ?><em class="pill">FR</em><?php endif; ?>
            <?php if (!empty($it['selected'])): ?><em class="pill gold">homepage #<?php echo (int) $it['home_order']; ?></em><?php endif; ?>
            <?php if (!empty($it['featured'])): ?><em class="pill gold"><?php echo $type === 'projects' ? 'on homepage' : 'featured'; ?></em><?php endif; ?>
          </span>
          <span class="muted small"><?php echo e($type === 'projects' ? $it['category'] : ($type === 'publications' ? $it['badge'] : ($type === 'talks' ? $it['event'] : implode(', ', $it['tags'])))); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php
    admin_layout_end();
    break;

case 'edit':
    $type = isset($_GET['type'], $types[$_GET['type']]) ? $_GET['type'] : 'posts';
    $slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
    $it = $slug !== '' ? cms_load_item($type, $slug) : null;
    if ($slug !== '' && !$it) { admin_flash('err', 'Not found.'); admin_redirect(array('page' => 'items', 'type' => $type)); }
    $body = isset($_SESSION['draft_body']) ? $_SESSION['draft_body'] : ($it ? $it['body'] : '');
    unset($_SESSION['draft_body']);
    $date = $it ? $it['date'] : time();
    $hasTime = $it && isset($it['meta']['date']) && preg_match('/\d{1,2}:\d{2}/', (string) $it['meta']['date']);
    $urlFns = array('posts' => 'cms_post_url', 'news' => 'cms_news_url', 'projects' => 'cms_project_url', 'publications' => 'cms_publication_url', 'talks' => 'cms_talk_url');
    $publicUrl = $it ? call_user_func($urlFns[$type], $it['slug']) : '';
    $categories = array();
    if ($type === 'projects') {
        foreach (cms_list_items('projects', true) as $pr) if ($pr['category'] !== '') $categories[$pr['category']] = true;
    }

    admin_layout_start($it ? 'Edit ' . $types[$type]['singular'] : 'New ' . $types[$type]['singular'], 'edit');
    ?>
    <form method="post" class="editor" id="editor-form" data-type="<?php echo e($type); ?>" data-og="<?php echo $type === 'publications' ? '0' : '1'; ?>" data-site="<?php echo e(cms_config('site_name')); ?>" data-base="<?php echo e(cms_config('base_url')); ?>" data-upload-url="<?php echo e(admin_url()); ?>" data-csrf="<?php echo e(cms_csrf_token()); ?>">
      <?php echo cms_csrf_field(); ?>
      <input type="hidden" name="action" value="save_item">
      <input type="hidden" name="type" value="<?php echo e($type); ?>">
      <input type="hidden" name="orig_slug" value="<?php echo e($slug); ?>">
      <input type="hidden" name="og_image" value="">

      <header class="top">
        <a class="back" href="<?php echo e(admin_url(array('page' => 'items', 'type' => $type))); ?>">&larr; <?php echo e($types[$type]['label']); ?></a>
        <div class="actions">
          <?php if ($it && !$it['draft']): ?><a class="btn" href="<?php echo e($publicUrl); ?>" target="_blank" rel="noopener"><?php echo admin_icon('ext'); ?> View</a><?php endif; ?>
          <label class="toggle"><input type="checkbox" name="draft" value="1" <?php echo $it && $it['draft'] ? 'checked' : ''; ?>> Draft</label>
          <button class="btn primary" accesskey="s">Save</button>
        </div>
      </header>

      <input class="title-input" name="title" placeholder="<?php echo $type === 'news' ? 'Headline (optional for short items)' : ($type === 'projects' ? 'Project name' : ($type === 'publications' ? 'Paper title' : 'Post title')); ?>"
             value="<?php echo e($it ? $it['title'] : ''); ?>" <?php echo $type !== 'news' ? 'required' : ''; ?> autofocus>

      <div class="grid meta">
        <?php if (!in_array($type, array('projects', 'publications'), true)): ?>
          <label>Date<input type="date" name="date" value="<?php echo date('Y-m-d', $date); ?>" required></label>
          <label>Time <small>(optional)</small><input type="time" name="time" value="<?php echo $hasTime ? date('H:i', $date) : ''; ?>"></label>
        <?php endif; ?>
        <label>Slug <small>(address)</small><input name="slug" value="<?php echo e($slug); ?>" placeholder="auto from title" pattern="[a-z0-9][a-z0-9-]*"></label>
        <?php if ($type === 'publications'): $v = function ($k) use ($it) { return e($it ? $it[$k] : ''); }; ?>
          <label>Type<select name="pubtype"><?php foreach (cms_pub_types_admin() as $k => $label): ?><option value="<?php echo $k; ?>" <?php echo $it && $it['pubtype'] === $k ? 'selected' : ''; ?>><?php echo e($label); ?></option><?php endforeach; ?></select></label>
          <label>Year<input name="year" value="<?php echo $v('year'); ?>" placeholder="2026"></label>
          <label>Badge <small>(short venue, e.g. ICRA)</small><input name="badge" value="<?php echo $v('badge'); ?>"></label>
          <label class="wide">Authors <small>(one per line; your name is highlighted automatically)</small><textarea name="authors" rows="3"><?php echo e($it ? implode("\n", $it['authors']) : cms_config('site_name')); ?></textarea></label>
          <label class="wide">Venue <small>(journal / conference)</small><input name="venue" value="<?php echo $v('venue'); ?>"></label>
          <label>Award <small>(optional)</small><input name="award" value="<?php echo $v('award'); ?>" placeholder="Best Paper Award"></label>
          <label>Note <small>(optional)</small><input name="note" value="<?php echo $v('note'); ?>"></label>
          <label class="wide">Picture shown next to it <small>(image or animated GIF)</small>
            <span class="with-btn"><input name="image" id="pubimg" value="<?php echo $v('image'); ?>"><button type="button" class="btn small" data-library-into="#pubimg">Library</button><button type="button" class="btn small" data-upload-into="#pubimg" data-accept="image/*">Upload</button></span>
          </label>
          <label>DOI<input name="doi" value="<?php echo $v('doi'); ?>" placeholder="10.1109/..."></label>
          <label>Paper page <small>(URL)</small><input name="url" value="<?php echo $v('url'); ?>"></label>
          <label>PDF
            <span class="with-btn"><input name="pdf" id="pubpdf" value="<?php echo $v('pdf'); ?>"><button type="button" class="btn small" data-library-into="#pubpdf">Library</button><button type="button" class="btn small" data-upload-into="#pubpdf" data-accept="application/pdf">Upload</button></span>
          </label>
          <label>Code <small>(URL)</small><input name="code" value="<?php echo $v('code'); ?>"></label>
          <label>Video <small>(URL)</small><input name="video" value="<?php echo $v('video'); ?>"></label>
          <label>Slides <small>(URL or file)</small><input name="slides" value="<?php echo $v('slides'); ?>"></label>
          <label class="toggle"><input type="checkbox" name="selected" value="1" <?php echo $it && $it['selected'] ? 'checked' : ''; ?>> Show on homepage</label>
          <label>Homepage position<input type="number" min="1" name="home_order" value="<?php echo e($it && $it['selected'] ? $it['home_order'] : 1); ?>"></label>
          <label class="wide">BibTeX <small>(shown with a copy button)</small><textarea name="bibtex" rows="6" class="mono"><?php echo $v('bibtex'); ?></textarea></label>
        <?php elseif ($type === 'talks'): $v = function ($k) use ($it) { return e($it ? $it[$k] : ''); }; ?>
          <label>Type<select name="kind"><?php foreach (cms_talk_kinds_admin() as $k => $label): ?><option value="<?php echo $k; ?>" <?php echo $it && $it['kind'] === $k ? 'selected' : ''; ?>><?php echo e($label); ?></option><?php endforeach; ?></select></label>
          <label>Event<input name="event" value="<?php echo $v('event'); ?>" placeholder="e.g. IEEE ICRA 2026"></label>
          <label>Place<input name="location" value="<?php echo $v('location'); ?>" placeholder="City, country"></label>
          <label>Award <small>(optional)</small><input name="award" value="<?php echo $v('award'); ?>"></label>
          <label class="wide">Video <small>(YouTube / Vimeo link, or an .mp4 from the library)</small>
            <span class="with-btn"><input name="video" id="talkvideo" value="<?php echo $v('video'); ?>"><button type="button" class="btn small" data-library-into="#talkvideo">Library</button></span></label>
          <label class="wide">Picture <small>(optional; YouTube thumbnails are used automatically)</small>
            <span class="with-btn"><input name="image" id="talkimg" value="<?php echo $v('image'); ?>"><button type="button" class="btn small" data-library-into="#talkimg">Library</button><button type="button" class="btn small" data-upload-into="#talkimg">Upload</button></span></label>
          <label class="wide">Slides <small>(PDF or link)</small>
            <span class="with-btn"><input name="slides" id="talkslides" value="<?php echo $v('slides'); ?>"><button type="button" class="btn small" data-library-into="#talkslides">Library</button><button type="button" class="btn small" data-upload-into="#talkslides" data-accept="application/pdf">Upload</button></span></label>
          <label>Event page <small>(URL)</small><input name="url" value="<?php echo $v('url'); ?>"></label>
          <label>Code <small>(URL)</small><input name="code" value="<?php echo $v('code'); ?>"></label>
          <label>Related blog post <small>(e.g. blog/?p=my-post)</small><input name="post" value="<?php echo $v('post'); ?>"></label>
          <label class="toggle"><input type="checkbox" name="featured" value="1" <?php echo $it && $it['featured'] ? 'checked' : ''; ?>> Show on homepage</label>
        <?php elseif ($type === 'projects'): ?>
          <label class="wide">3D model <small>(.glb file — shown as an interactive 3D view on the project page)</small>
            <span class="with-btn"><input name="model" id="model3d" value="<?php echo e($it ? $it['model'] : ''); ?>" placeholder="files/models/robot.glb"><button type="button" class="btn small" data-library-into="#model3d">Library</button><button type="button" class="btn small" data-upload-into="#model3d" data-accept=".glb,.usdz,model/gltf-binary">Upload</button></span></label>
          <label class="toggle"><input type="checkbox" name="model_home" value="1" <?php echo $it && $it['model_home'] ? 'checked' : ''; ?>> Show in 3D on homepage</label>
          <label>Category <small>(used for the filter buttons)</small>
            <input name="category" list="project-categories" value="<?php echo e($it ? $it['category'] : ''); ?>">
            <datalist id="project-categories"><?php foreach (array_keys($categories) as $c): ?><option value="<?php echo e($c); ?>"><?php endforeach; ?></datalist>
          </label>
          <label>Order <small>(1 = first)</small><input type="number" min="1" name="importance" value="<?php echo e($it ? $it['importance'] : 50); ?>"></label>
          <label class="wide">Short description <small>(shown on the card)</small><input name="description" value="<?php echo e($it ? $it['description'] : ''); ?>"></label>
          <label class="wide">Cover image
            <span class="with-btn"><input name="img" id="cover" value="<?php echo e($it ? $it['img'] : ''); ?>" placeholder="assets/img/... or upload"><button type="button" class="btn small" data-library-into="#cover">Library</button><button type="button" class="btn small" data-upload-into="#cover">Upload</button></span>
          </label>
          <label>GitHub repository <small>(optional)</small><input type="url" name="github" value="<?php echo e($it ? $it['github'] : ''); ?>" placeholder="https://github.com/..."></label>
          <label>Other link <small>(paper, demo, video)</small><input type="url" name="url" value="<?php echo e($it ? $it['url'] : ''); ?>"></label>
          <label class="toggle"><input type="checkbox" name="featured" value="1" <?php echo $it && $it['featured'] ? 'checked' : ''; ?>> Show on homepage</label>
        <?php elseif ($type === 'posts'): ?>
          <label class="wide">Summary <small>(shown in lists and search results)</small><input name="description" value="<?php echo e($it ? $it['description'] : ''); ?>"></label>
          <label>Tags <small>(space or comma separated)</small><input name="tags" value="<?php echo e($it ? implode(' ', $it['tags']) : ''); ?>"></label>
          <label>Categories<input name="categories" value="<?php echo e($it ? implode(' ', $it['categories']) : ''); ?>"></label>
          <label>Cover image URL <small>(optional)</small>
            <span class="with-btn"><input name="thumbnail" id="thumb" value="<?php echo e($it ? $it['thumbnail'] : ''); ?>"><button type="button" class="btn small" data-library-into="#thumb">Library</button><button type="button" class="btn small" data-upload-into="#thumb">Upload</button></span>
          </label>
          <label class="toggle"><input type="checkbox" name="featured" value="1" <?php echo $it && $it['featured'] ? 'checked' : ''; ?>> Pin as featured</label>
        <?php else: ?>
          <label class="toggle wide"><input type="checkbox" name="inline" value="1" <?php echo !$it || $it['inline'] ? 'checked' : ''; ?>>
            Short item: show the text directly in the news list (untick to link to a full page with the headline)</label>
        <?php endif; ?>
      </div>

      <?php if ($type === 'publications'): ?><h2 class="editor-label">Abstract</h2><?php endif; ?>
      <textarea name="body" id="body"><?php echo e($body); ?></textarea>
      <p class="muted small">Markdown. Drag &amp; drop or paste images straight into the editor. Ctrl+S saves.</p>
      <?php if ($type !== 'publications'): $m = $it ? $it['meta'] : array(); ?>
        <details class="card fr-card" <?php echo !empty($m['title_fr']) || !empty($m['body_fr']) ? 'open' : ''; ?>>
          <summary><strong>Français</strong> <span class="muted small">— optional; visitors who switch to French see this version (empty fields fall back to English)</span></summary>
          <div class="grid meta">
            <label class="wide">Titre<input name="title_fr" value="<?php echo e(isset($m['title_fr']) ? $m['title_fr'] : ''); ?>"></label>
            <?php if ($type === 'posts' || $type === 'projects'): ?>
              <label class="wide">Résumé<input name="description_fr" value="<?php echo e(isset($m['description_fr']) ? $m['description_fr'] : ''); ?>"></label>
            <?php endif; ?>
            <?php if ($type === 'talks'): ?>
              <label class="wide">Événement<input name="event_fr" value="<?php echo e(isset($m['event_fr']) ? $m['event_fr'] : ''); ?>"></label>
            <?php endif; ?>
            <label class="wide">Texte <small>(Markdown)</small><textarea name="body_fr" rows="10" class="mono"><?php echo e(isset($m['body_fr']) ? $m['body_fr'] : ''); ?></textarea></label>
          </div>
        </details>
      <?php endif; ?>
    </form>

    <?php if ($it): ?>
      <form method="post" class="danger-zone" data-confirm="Move this <?php echo e($types[$type]['singular']); ?> to the trash?">
        <?php echo cms_csrf_field(); ?>
        <input type="hidden" name="action" value="delete_item"><input type="hidden" name="type" value="<?php echo e($type); ?>">
        <input type="hidden" name="slug" value="<?php echo e($slug); ?>">
        <button class="btn danger"><?php echo admin_icon('trash'); ?> Delete</button>
        <span class="muted small">Last saved <?php echo date('M j, Y H:i', $it['updated']); ?>. Previous versions are kept in <code>cms-data/history</code>.</span>
      </form>
    <?php endif;
    ?>
    <dialog class="media" id="media" data-list-url="<?php echo e(admin_url(array('page' => 'media'))); ?>" data-csrf="<?php echo e(cms_csrf_token()); ?>" data-upload-url="<?php echo e(admin_url()); ?>">
      <div class="media__head">
        <strong class="media__title">Media library</strong>
        <span class="media__tabs" role="tablist">
          <button type="button" class="on" data-media-src="files">Uploads</button>
          <button type="button" data-media-src="site">Site images</button>
        </span>
        <nav class="media__crumbs" aria-label="Folder"></nav>
        <label class="btn small media__upload"><?php echo admin_icon('up'); ?> Upload<input type="file" multiple hidden></label>
        <button type="button" class="btn small" data-media-close aria-label="Close">&times;</button>
      </div>
      <p class="media__hint muted small">Click a picture to insert it. Drop files anywhere here to upload them into this folder.</p>
      <div class="media__grid"></div>
      <p class="media__status small" role="status"></p>
    </dialog>
    <?php
    admin_layout_end(array('lib/easymde.min.js'));
    break;

case 'home':
    $doc = cms_doc('home');
    $tab = isset($_GET['lang']) && $_GET['lang'] === 'fr' ? 'fr' : 'en';
    admin_layout_start('Homepage', 'homepage');
    ?>
    <form method="post" class="editor" id="home-form" data-upload-url="<?php echo e(admin_url()); ?>" data-csrf="<?php echo e(cms_csrf_token()); ?>">
      <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="home_save"><input type="hidden" name="lang" value="<?php echo $tab; ?>" data-lang-field>
      <header class="top">
        <h1>Homepage</h1>
        <div class="actions">
          <nav class="tabs" data-lang-tabs>
            <a href="#en" data-tab="en" class="<?php echo $tab === 'en' ? 'on' : ''; ?>">English</a>
            <a href="#fr" data-tab="fr" class="<?php echo $tab === 'fr' ? 'on' : ''; ?>">Français</a>
          </nav>
          <a class="btn" href="<?php echo e(cms_url()); ?>" target="_blank" rel="noopener"><?php echo admin_icon('ext'); ?> View</a>
          <button class="btn primary">Save</button>
        </div>
      </header>
      <?php foreach (array('en', 'fr') as $lang): $data = isset($doc[$lang]) ? $doc[$lang] : array(); ?>
        <div class="lang-pane" data-pane="<?php echo $lang; ?>" <?php echo $lang === $tab ? '' : 'hidden'; ?>>
          <?php if ($lang === 'fr'): ?><p class="notice">French version. Leave a field empty to show the English text instead.</p><?php endif; ?>
          <?php foreach (cms_home_schema() as $group => $fields): ?>
            <section class="card">
              <h2><?php echo e($group); ?></h2>
              <div class="grid meta">
                <?php foreach ($fields as $path => $spec) admin_home_field($lang, $path, $spec, cms_path_get($data, $path)); ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
      <div class="actions"><button class="btn primary">Save</button></div>
    </form>
    <?php
    admin_media_dialog();
    admin_layout_end();
    break;

case 'cv':
    $cv = cms_doc('cv');
    $b = isset($cv['basics']) ? $cv['basics'] : array();
    admin_layout_start('CV', 'cv');
    ?>
    <form method="post" class="editor cv-editor" id="cv-form" data-upload-url="<?php echo e(admin_url()); ?>" data-csrf="<?php echo e(cms_csrf_token()); ?>">
      <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="cv_save">
      <header class="top">
        <h1>CV</h1>
        <div class="actions">
          <a class="btn" href="<?php echo e(cms_url('cv/')); ?>" target="_blank" rel="noopener"><?php echo admin_icon('ext'); ?> View</a>
          <button class="btn primary">Save CV</button>
        </div>
      </header>
      <section class="card">
        <h2>Header</h2>
        <div class="grid meta">
          <?php foreach (cms_cv_basics_fields() as $k => $label): ?>
            <?php if ($k === 'summary'): ?>
              <label class="wide"><?php echo e($label); ?><textarea name="cv[basics][summary]" rows="3"><?php echo e(isset($b['summary']) ? $b['summary'] : ''); ?></textarea></label>
            <?php else: ?>
              <label><?php echo e($label); ?><input name="cv[basics][<?php echo $k; ?>]" value="<?php echo e(isset($b[$k]) ? $b[$k] : ''); ?>"></label>
            <?php endif; ?>
          <?php endforeach; ?>
          <label class="wide">Downloadable PDF <small>(people get this file from the “Download CV” button)</small>
            <span class="with-btn"><input name="cv[basics][pdf]" id="cvpdf" value="<?php echo e(isset($b['pdf']) ? $b['pdf'] : ''); ?>" placeholder="files/cv/your-cv.pdf">
              <button type="button" class="btn small" data-library-into="#cvpdf">Library</button>
              <button type="button" class="btn small" data-upload-into="#cvpdf" data-accept="application/pdf">Upload new PDF</button></span>
          </label>
        </div>
      </section>
      <?php foreach (cms_cv_schema() as $section => $def):
          $rows = isset($cv[$section]) && is_array($cv[$section]) ? $cv[$section] : array(); ?>
        <section class="card cv-section-edit">
          <header class="top"><h2><?php echo e($def['label']); ?> <small class="muted">(<?php echo count($rows); ?>)</small></h2>
            <button type="button" class="btn small" data-add-row="<?php echo $section; ?>"><?php echo admin_icon('plus'); ?> Add</button></header>
          <div class="rows" data-rows="<?php echo $section; ?>">
            <?php foreach ($rows as $i => $row) admin_cv_row($section, $def, $i, $row); ?>
          </div>
          <template data-row-template="<?php echo $section; ?>"><?php admin_cv_row($section, $def, '__i__', array(), true); ?></template>
        </section>
      <?php endforeach; ?>
      <p class="muted small">Publications on the CV come from the Publications section automatically.</p>
      <div class="actions"><button class="btn primary">Save CV</button></div>
    </form>
    <?php
    admin_media_dialog();
    admin_layout_end();
    break;

case 'repos':
    $doc = cms_doc('repositories', array('user' => '', 'repos' => array()));
    admin_layout_start('Repositories', 'repos');
    ?>
    <form method="post" class="editor" id="repos-form">
      <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="repos_save">
      <header class="top">
        <h1>Repositories</h1>
        <div class="actions">
          <a class="btn" href="<?php echo e(cms_url('repositories/')); ?>" target="_blank" rel="noopener"><?php echo admin_icon('ext'); ?> View</a>
          <button class="btn primary">Save</button>
        </div>
      </header>
      <p class="muted small">Drag to reorder. <b>Large</b> cards span two columns, <b>Compact</b> shows just the name and stats. Tags become the filter buttons. Untick “Shown” to hide a repository without losing its settings.</p>
      <div class="grid meta">
        <label>GitHub user<input name="user" value="<?php echo e($doc['user']); ?>" data-gh-user></label>
        <label class="wide">Add a repository
          <span class="with-btn"><input id="repo-add" list="gh-repos" placeholder="owner/name — start typing, your repositories are suggested"><button type="button" class="btn small" data-repo-add>Add</button></span>
          <datalist id="gh-repos"></datalist>
        </label>
      </div>
      <ol class="sortable repo-rows" id="repo-rows">
        <?php foreach ($doc['repos'] as $i => $r) admin_repo_row($i, $r); ?>
      </ol>
      <template id="repo-template"><?php admin_repo_row('__i__', array('repo' => '', 'tags' => array(), 'note' => '', 'size' => 'normal', 'visible' => true)); ?></template>
      <div class="actions"><button class="btn primary">Save</button></div>
    </form>
    <?php
    admin_layout_end();
    break;

case 'analytics':
    $days = isset($_GET['days']) ? max(1, min(365, (int) $_GET['days'])) : 30;
    $st = cms_stats_summary($days);
    admin_layout_start('Analytics', 'analytics');
    $max = 1;
    foreach ($st['days'] as $d) $max = max($max, $d['views']);
    $avgTime = $st['timeCount'] ? round($st['time'] / $st['timeCount']) : 0;
    $bounce = $st['visitors'] ? round($st['bounces'] / $st['visitors'] * 100) : 0;
    ?>
    <header class="top"><h1>Analytics</h1>
      <nav class="tabs"><?php foreach (array(7 => '7 days', 30 => '30 days', 90 => '90 days', 365 => '12 months') as $n => $label): ?>
        <a class="<?php echo $days === $n ? 'on' : ''; ?>" href="<?php echo e(admin_url(array('page' => 'analytics', 'days' => $n))); ?>"><?php echo $label; ?></a><?php endforeach; ?></nav>
    </header>
    <div class="stats">
      <div class="stat"><b><span class="live-dot"></span><?php echo $st['live']; ?></b><span>on the site right now</span></div>
      <div class="stat"><b><?php echo number_format($st['visitors']); ?></b><span>visitors</span></div>
      <div class="stat"><b><?php echo number_format($st['views']); ?></b><span>page views</span></div>
      <div class="stat"><b><?php echo $avgTime >= 60 ? floor($avgTime / 60) . 'm ' . ($avgTime % 60) . 's' : $avgTime . 's'; ?></b><span>average time on a page</span></div>
      <div class="stat"><b><?php echo $bounce; ?>%</b><span>left after one page</span></div>
    </div>
    <section class="card chart-card">
      <h2>Visitors and page views per day</h2>
      <?php $n = count($st['days']); $w = 1000; $h = 220; $bw = $w / $n; ?>
      <svg class="chart" viewBox="0 0 <?php echo $w; ?> <?php echo $h + 24; ?>" role="img" aria-label="Daily visitors and page views">
        <?php foreach (array(0.25, 0.5, 0.75, 1) as $g): ?><line x1="0" x2="<?php echo $w; ?>" y1="<?php echo $h - $h * $g; ?>" y2="<?php echo $h - $h * $g; ?>" class="grid"/><?php endforeach; ?>
        <?php foreach ($st['days'] as $i => $d):
            $vh = $d['views'] / $max * ($h - 10); $uh = $d['visitors'] / $max * ($h - 10); $x = $i * $bw; ?>
          <g><title><?php echo e(date('D j M', strtotime($d['day']))) . ': ' . $d['visitors'] . ' visitors, ' . $d['views'] . ' views'; ?></title>
            <rect class="views" x="<?php echo $x + $bw * 0.12; ?>" y="<?php echo $h - $vh; ?>" width="<?php echo $bw * 0.76; ?>" height="<?php echo max(0, $vh); ?>" rx="3"/>
            <rect class="visitors" x="<?php echo $x + $bw * 0.3; ?>" y="<?php echo $h - $uh; ?>" width="<?php echo $bw * 0.4; ?>" height="<?php echo max(0, $uh); ?>" rx="2"/>
            <rect x="<?php echo $x; ?>" y="0" width="<?php echo $bw; ?>" height="<?php echo $h; ?>" fill="transparent"/></g>
        <?php endforeach; ?>
        <text x="0" y="<?php echo $h + 18; ?>" class="axis"><?php echo e(date('j M', strtotime($st['days'][0]['day']))); ?></text>
        <text x="<?php echo $w; ?>" y="<?php echo $h + 18; ?>" class="axis" text-anchor="end">Today</text>
      </svg>
      <p class="legend"><span class="sw views"></span> page views <span class="sw visitors"></span> visitors</p>
    </section>
    <div class="cards">
      <section class="card"><h2>Top pages</h2><?php admin_stat_table($st['pages'], 'Page', true); ?></section>
      <section class="card"><h2>Where visitors come from</h2><?php admin_stat_table($st['referrers'], 'Source'); ?></section>
      <section class="card"><h2>Devices</h2><?php admin_stat_table($st['devices'], 'Device'); ?></section>
      <section class="card"><h2>Browsers</h2><?php admin_stat_table($st['browsers'], 'Browser'); ?></section>
      <section class="card"><h2>Operating systems</h2><?php admin_stat_table($st['os'], 'System'); ?></section>
      <section class="card"><h2>Location <small class="muted">(time zone)</small></h2><?php admin_stat_table($st['zones'], 'Time zone'); ?></section>
      <section class="card"><h2>Languages</h2><?php admin_stat_table($st['langs'], 'Language'); ?></section>
    </div>
    <p class="muted small">Privacy-friendly: no cookies, no IP addresses stored, visitors counted with a hash that changes every day. Your own visits are not counted once you have opened this admin panel in that browser. Visitors with “Do Not Track” are not counted.</p>
    <?php
    admin_layout_end();
    break;

case 'files':
    $src = isset($_GET['src']) && $_GET['src'] === 'site' ? 'site' : 'files';
    $source = cms_media_source($src);
    $ro = !$source['writable'];
    $dirRel = isset($_GET['dir']) ? (string) $_GET['dir'] : '';
    $abs = cms_files_resolve($dirRel, true, $src);
    if (!$abs || !is_dir($abs)) { admin_flash('err', 'Folder not found.'); admin_redirect(array('page' => 'files')); }
    $dirRel = cms_files_rel($abs, $src);
    list($dirs, $files) = cms_files_list($abs, $src);
    $crumbs = array(array($ro ? 'assets/img' : 'files', ''));
    $acc = '';
    foreach ($dirRel === '' ? array() : explode('/', $dirRel) as $part) {
        $acc = ltrim($acc . '/' . $part, '/');
        $crumbs[] = array($part, $acc);
    }
    admin_layout_start('Files', 'files');
    ?>
    <nav class="tabs" aria-label="Library">
      <a class="<?php echo $ro ? '' : 'on'; ?>" href="<?php echo e(admin_url(array('page' => 'files'))); ?>">Uploads</a>
      <a class="<?php echo $ro ? 'on' : ''; ?>" href="<?php echo e(admin_url(array('page' => 'files', 'src' => 'site'))); ?>">Site images <small>(read-only)</small></a>
    </nav>
    <header class="top">
      <h1 class="crumbs"><?php foreach ($crumbs as $i => $c): ?><?php if ($i): ?><span>/</span><?php endif; ?><a href="<?php echo e(admin_url(array('page' => 'files', 'src' => $src, 'dir' => $c[1]))); ?>"><?php echo e($c[0]); ?></a><?php endforeach; ?></h1>
      <?php if (!$ro): ?>
      <form method="post" class="inline" data-prompt="New folder name" data-prompt-field="name">
        <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="files_mkdir"><input type="hidden" name="dir" value="<?php echo e($dirRel); ?>"><input type="hidden" name="name">
        <button class="btn"><?php echo admin_icon('plus'); ?> New folder</button>
      </form>
      <?php endif; ?>
    </header>

    <?php if ($ro): ?>
      <p class="notice">These pictures ship with the site from the Git repository (<code>assets/img/</code>). You can reuse them in posts and projects with <em>Copy Markdown</em> or the editor's media library. To add, rename or delete them, change the repository and redeploy; uploads belong in <a href="<?php echo e(admin_url(array('page' => 'files'))); ?>">Uploads</a>.</p>
    <?php else: ?>
    <form method="post" enctype="multipart/form-data" class="dropzone" id="dropzone">
      <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="files_upload"><input type="hidden" name="dir" value="<?php echo e($dirRel); ?>">
      <input type="file" name="files[]" id="file-input" multiple>
      <label for="file-input"><?php echo admin_icon('up'); ?><strong>Drop files here</strong> or click to choose · max <?php echo e(ini_get('upload_max_filesize')); ?> each</label>
    </form>

    <form method="post" id="bulk" data-confirm="Move the selected items to the trash?">
      <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="files_delete"><input type="hidden" name="dir" value="<?php echo e($dirRel); ?>">
      <div class="bulkbar" hidden><span data-count></span> selected <button class="btn danger small"><?php echo admin_icon('trash'); ?> Delete</button></div>
    </form>
    <?php endif; ?>

    <div class="files">
      <?php if ($dirRel !== ''): ?>
        <a class="file dir" href="<?php echo e(admin_url(array('page' => 'files', 'src' => $src, 'dir' => dirname($dirRel) === '.' ? '' : dirname($dirRel)))); ?>"><span class="thumb"><?php echo admin_icon('folder'); ?></span><span class="name">..</span></a>
      <?php endif; ?>
      <?php foreach ($dirs as $d): ?>
        <div class="file dir">
          <?php if (!$ro): ?><input type="checkbox" form="bulk" name="paths[]" value="<?php echo e($d['rel']); ?>" aria-label="Select"><?php endif; ?>
          <a class="thumb" href="<?php echo e(admin_url(array('page' => 'files', 'src' => $src, 'dir' => $d['rel']))); ?>"><?php echo admin_icon('folder'); ?></a>
          <a class="name" href="<?php echo e(admin_url(array('page' => 'files', 'src' => $src, 'dir' => $d['rel']))); ?>"><?php echo e($d['name']); ?></a>
          <?php if (!$ro) admin_file_menu($d, $dirRel, false); ?>
        </div>
      <?php endforeach; ?>
      <?php foreach ($files as $f): $url = cms_files_public_url($f['rel'], $src); ?>
        <div class="file">
          <?php if (!$ro): ?><input type="checkbox" form="bulk" name="paths[]" value="<?php echo e($f['rel']); ?>" aria-label="Select"><?php endif; ?>
          <a class="thumb" href="<?php echo e($url); ?>" target="_blank" rel="noopener">
            <?php if (in_array($f['ext'], array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'ico'), true)): ?>
              <img src="<?php echo e($url); ?>" alt="" loading="lazy">
            <?php else: ?><?php echo admin_icon('file'); ?><em><?php echo e($f['ext']); ?></em><?php endif; ?>
          </a>
          <span class="name" title="<?php echo e($f['name']); ?>"><?php echo e($f['name']); ?></span>
          <span class="muted small"><?php echo cms_human_size($f['size']); ?></span>
          <?php admin_file_menu($f, $dirRel, $url, $src); ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (!$dirs && !$files): ?><p class="empty">This folder is empty.</p><?php endif; ?>
    <?php
    admin_layout_end();
    break;

case 'trash':
    $entries = cms_trash_list();
    admin_layout_start('Trash', 'trash');
    ?>
    <header class="top"><h1>Trash</h1>
      <?php if ($entries): ?>
        <form method="post" data-confirm="Permanently delete everything in the trash? This cannot be undone.">
          <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="trash_empty"><button class="btn danger"><?php echo admin_icon('trash'); ?> Empty trash</button>
        </form>
      <?php endif; ?>
    </header>
    <div class="list">
      <?php if (!$entries): ?><p class="empty">The trash is empty.</p><?php endif; ?>
      <?php foreach ($entries as $t): ?>
        <div class="row">
          <span class="date"><?php echo date('M j, H:i', $t['deleted']); ?></span>
          <span class="grow"><strong><?php echo e($t['name']); ?></strong> <span class="muted small"><?php echo e($t['kind'] === 'file' ? 'files/' . $t['path'] : $t['path']); ?></span></span>
          <?php if ($t['kind'] !== 'unknown'): ?>
            <form method="post"><?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="trash_restore"><input type="hidden" name="entry" value="<?php echo e($t['entry']); ?>"><button class="btn small">Restore</button></form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php
    admin_layout_end();
    break;

case 'settings':
    $s = cms_settings();
    $checks = array(
        array('PHP version', PHP_VERSION, version_compare(PHP_VERSION, '7.2', '>=')),
        array('Data folder writable', cms_data_path(), is_writable(cms_data_path())),
        array('Files folder writable', cms_config('files_dir'), is_writable(cms_config('files_dir'))),
        array('Max upload size', ini_get('upload_max_filesize') . ' (post ' . ini_get('post_max_size') . ')', true),
    );
    admin_layout_start('Settings', 'settings');
    ?>
    <header class="top"><h1>Settings</h1></header>
    <div class="cards">

      <form method="post" class="card">
        <h2>Change password</h2>
        <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="settings_password">
        <label>Current password<input type="password" name="current" required autocomplete="current-password"></label>
        <label>New password <small>(12+ characters)</small><input type="password" name="new" minlength="12" required autocomplete="new-password"></label>
        <label>Repeat new password<input type="password" name="new2" minlength="12" required autocomplete="new-password"></label>
        <div class="actions"><button class="btn primary">Change password</button></div>
      </form>

      <div class="card">
        <h2>Backup &amp; restore</h2>
        <p class="muted small">Download everything you created in this admin (posts, news, projects, publications, talks, CV, homepage, uploads, statistics) as one file. Keep a copy on your computer.</p>
        <form method="post"><?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="backup_download">
          <button class="btn primary"><?php echo admin_icon('download'); ?> Download backup</button></form>
        <form method="post" enctype="multipart/form-data" data-confirm="Restore this backup? Files in it replace the current versions. The current state is saved first, so this can be undone.">
          <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="backup_restore">
          <label>Restore from a backup file (.tar)<input type="file" name="archive" accept=".tar" required></label>
          <div class="actions"><button class="btn danger">Restore</button></div>
        </form>
      </div>

      <div class="card">
        <h2>Server status</h2>
        <table class="kv">
          <?php foreach ($checks as $c): ?>
            <tr><th><?php echo e($c[0]); ?></th><td><span class="dotstat <?php echo $c[2] ? 'good' : 'bad'; ?>"></span><?php echo e($c[1]); ?></td></tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
    <?php
    admin_layout_end();
    break;

default: // dashboard
    $posts = cms_list_items('posts', true);
    $news = cms_list_items('news', true);
    $projects = cms_list_items('projects', true);
    $drafts = 0;
    foreach (array_merge($posts, $news, $projects) as $d) if ($d['draft']) $drafts++;
    $week = cms_stats_summary(7);
    admin_layout_start('Dashboard', 'dashboard');
    ?>
    <header class="top"><h1>Hello, <?php echo e(strtok(cms_config('site_name'), ' ')); ?> 👋</h1>
      <div class="actions">
        <a class="btn primary" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => 'news'))); ?>"><?php echo admin_icon('plus'); ?> News</a>
        <a class="btn primary" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => 'posts'))); ?>"><?php echo admin_icon('plus'); ?> Blog post</a>
        <a class="btn primary" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => 'projects'))); ?>"><?php echo admin_icon('plus'); ?> Project</a>
        <a class="btn primary" href="<?php echo e(admin_url(array('page' => 'items', 'type' => 'publications'))); ?>"><?php echo admin_icon('plus'); ?> Publication</a>
        <a class="btn primary" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => 'talks'))); ?>"><?php echo admin_icon('plus'); ?> Talk</a>
      </div>
    </header>
    <div class="stats">
      <a class="stat" href="<?php echo e(admin_url(array('page' => 'items', 'type' => 'posts'))); ?>"><b><?php echo count($posts); ?></b><span>blog posts</span></a>
      <a class="stat" href="<?php echo e(admin_url(array('page' => 'items', 'type' => 'news'))); ?>"><b><?php echo count($news); ?></b><span>news items</span></a>
      <a class="stat" href="<?php echo e(admin_url(array('page' => 'items', 'type' => 'projects'))); ?>"><b><?php echo count($projects); ?></b><span>projects</span></a>
      <div class="stat"><b><?php echo $drafts; ?></b><span>drafts</span></div>
      <a class="stat" href="<?php echo e(admin_url(array('page' => 'analytics', 'days' => 7))); ?>"><b><?php echo number_format($week['visitors']); ?></b><span>visitors this week
        <svg class="spark" viewBox="0 0 70 20" aria-hidden="true"><?php $mx = 1; foreach ($week['days'] as $d) $mx = max($mx, $d['visitors']);
          $pts = array(); foreach ($week['days'] as $i => $d) $pts[] = ($i * 70 / 6) . ',' . (19 - $d['visitors'] / $mx * 17); ?>
          <polyline points="<?php echo implode(' ', $pts); ?>"/></svg></span></a>
    </div>
    <div class="cards">
      <section class="card">
        <h2>Recently edited</h2>
        <div class="list compact">
          <?php
          $recent = array_merge($posts, $news, $projects);
          usort($recent, function ($a, $b) { return $b['updated'] - $a['updated']; });
          foreach (array_slice($recent, 0, 6) as $it): ?>
            <a class="row" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => $it['type'], 'slug' => $it['slug']))); ?>">
              <span class="pill <?php echo $it['type'] === 'news' ? 'blue' : ($it['type'] === 'projects' ? 'gold' : ''); ?>"><?php echo array('news' => 'news', 'posts' => 'post', 'projects' => 'project')[$it['type']]; ?></span>
              <span class="grow"><?php echo e($it['title'] !== '' ? $it['title'] : cms_excerpt($it, 60)); ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
    <?php
    admin_layout_end();
}

// Renumbers homepage publications 1..n. $first: slugs in the order the user dragged them;
// $winner: the publication just saved, which wins ties for its chosen position.
function admin_pub_renumber(array $first = array(), $winner = null)
{
    $sel = array_values(array_filter(cms_list_items('publications', true), function ($p) { return $p['selected']; }));
    usort($sel, function ($a, $b) use ($winner) {
        if ($a['home_order'] !== $b['home_order']) return $a['home_order'] - $b['home_order'];
        if ($a['slug'] === $winner) return -1;
        if ($b['slug'] === $winner) return 1;
        return strcmp($a['slug'], $b['slug']);
    });
    $bySlug = array();
    foreach ($sel as $pub) $bySlug[$pub['slug']] = $pub;
    $ordered = array();
    foreach ($first as $slug) {
        if (isset($bySlug[$slug])) { $ordered[] = $bySlug[$slug]; unset($bySlug[$slug]); }
    }
    foreach ($sel as $pub) if (isset($bySlug[$pub['slug']])) $ordered[] = $pub;
    foreach ($ordered as $i => $pub) {
        if ($pub['home_order'] === $i + 1) continue;
        $meta = $pub['meta'];
        $meta['home_order'] = $i + 1;
        cms_save_item('publications', $pub['slug'], $meta, $pub['body']);
    }
    return count($ordered);
}

function cms_talk_kinds_admin()
{
    return cms_talk_kinds();
}

function admin_home_field($lang, $path, array $spec, $value)
{
    $name = $lang . '[' . str_replace('.', '__', $path) . ']';
    $label = e($spec[0]);
    switch ($spec[1]) {
        case 'text':
            echo '<label class="wide">' . $label . '<input name="' . e($name) . '" value="' . e((string) $value) . '"></label>';
            break;
        case 'textarea':
            echo '<label class="wide">' . $label . '<textarea name="' . e($name) . '" rows="3">' . e((string) $value) . '</textarea></label>';
            break;
        case 'paragraphs':
            echo '<label class="wide">' . $label . '<textarea name="' . e($name) . '" rows="8">' . e(implode("\n\n", (array) $value)) . '</textarea></label>';
            break;
        case 'list':
            echo '<label class="wide">' . $label . '<textarea name="' . e($name) . '" rows="8">' . e(implode("\n", (array) $value)) . '</textarea></label>';
            break;
        case 'image':
            $id = 'img-' . $lang . '-' . str_replace('.', '-', $path);
            echo '<label class="wide">' . $label . '<span class="with-btn"><input name="' . e($name) . '" id="' . $id . '" value="' . e((string) $value) . '">'
                . '<button type="button" class="btn small" data-library-into="#' . $id . '">Library</button>'
                . '<button type="button" class="btn small" data-upload-into="#' . $id . '">Upload</button></span></label>';
            break;
        case 'rows':
            $key = $lang . '-' . str_replace('.', '-', $path);
            echo '<div class="wide"><div class="top"><strong>' . $label . '</strong><button type="button" class="btn small" data-add-row="' . e($key) . '">' . admin_icon('plus') . ' Add</button></div>';
            echo '<div class="rows" data-rows="' . e($key) . '">';
            foreach ((array) $value as $i => $row) admin_home_row($name, $spec[2], $i, (array) $row);
            echo '</div><template data-row-template="' . e($key) . '">';
            admin_home_row($name, $spec[2], '__i__', array(), true);
            echo '</template></div>';
            break;
    }
}

function admin_home_row($name, array $fields, $i, array $row, $open = false)
{
    $first = key($fields);
    $title = isset($row[$first]) && $row[$first] !== '' ? $row[$first] : 'New entry';
    echo '<details class="row-edit"' . ($open ? ' open' : '') . '><summary><span class="grow">' . e($title) . '</span><span class="row-tools">'
        . '<button type="button" class="btn tiny" data-move="-1">↑</button><button type="button" class="btn tiny" data-move="1">↓</button>'
        . '<button type="button" class="btn tiny danger" data-remove-row>Remove</button></span></summary><div class="grid meta">';
    foreach ($fields as $f => $label) {
        $attr = $f === $first ? ' data-row-title' : '';
        $val = isset($row[$f]) ? $row[$f] : '';
        if ($f === 'text') {
            echo '<label class="wide">' . e($label) . '<textarea name="' . e($name . '[' . $i . '][' . $f . ']') . '" rows="3">' . e($val) . '</textarea></label>';
        } else {
            echo '<label>' . e($label) . '<input name="' . e($name . '[' . $i . '][' . $f . ']') . '" value="' . e($val) . '"' . $attr . '></label>';
        }
    }
    echo '</div></details>';
}

function cms_pub_types_admin()
{
    return array('journal' => 'Journal article', 'conference' => 'Conference paper', 'workshop' => 'Workshop paper', 'patent' => 'Patent',
                 'thesis' => 'Thesis', 'preprint' => 'Preprint', 'talk' => 'Talk / poster', 'other' => 'Other');
}

function admin_media_dialog()
{
    ?>
    <dialog class="media" id="media" data-list-url="<?php echo e(admin_url(array('page' => 'media'))); ?>" data-csrf="<?php echo e(cms_csrf_token()); ?>" data-upload-url="<?php echo e(admin_url()); ?>">
      <div class="media__head">
        <strong class="media__title">Media library</strong>
        <span class="media__tabs" role="tablist"><button type="button" class="on" data-media-src="files">Uploads</button><button type="button" data-media-src="site">Site images</button></span>
        <nav class="media__crumbs" aria-label="Folder"></nav>
        <label class="btn small media__upload"><?php echo admin_icon('up'); ?> Upload<input type="file" multiple hidden></label>
        <button type="button" class="btn small" data-media-close aria-label="Close">&times;</button>
      </div>
      <p class="media__hint muted small">Click a file to use it. Drop files anywhere here to upload them into this folder.</p>
      <div class="media__grid"></div>
      <p class="media__status small" role="status"></p>
    </dialog>
    <?php
}

function admin_cv_row($section, $def, $i, array $row, $open = false)
{
    $name = function ($f) use ($section, $i) { return 'cv[' . $section . '][' . $i . '][' . $f . ']'; };
    $titleField = $def['title'];
    $title = isset($row[$titleField]) && $row[$titleField] !== '' ? $row[$titleField] : 'New entry';
    ?>
    <details class="row-edit" <?php echo $open ? 'open' : ''; ?>>
      <summary><span class="grow"><?php echo e($title); ?></span>
        <span class="row-tools"><button type="button" class="btn tiny" data-move="-1" title="Move up">↑</button><button type="button" class="btn tiny" data-move="1" title="Move down">↓</button><button type="button" class="btn tiny danger" data-remove-row title="Remove">Remove</button></span></summary>
      <div class="grid meta">
        <?php foreach ($def['fields'] as $f => $spec):
            $val = isset($row[$f]) ? $row[$f] : '';
            if (is_array($val)) $val = implode("\n", $val); ?>
          <?php if ($spec[1] === 'text'): ?>
            <label><?php echo e($spec[0]); ?><input name="<?php echo e($name($f)); ?>" value="<?php echo e($val); ?>" <?php echo $f === $titleField ? 'data-row-title' : ''; ?>></label>
          <?php else: ?>
            <label class="wide"><?php echo e($spec[0]); ?><textarea name="<?php echo e($name($f)); ?>" rows="<?php echo $spec[1] === 'list' ? 4 : 3; ?>"><?php echo e($val); ?></textarea></label>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </details>
    <?php
}

function admin_repo_row($i, array $r)
{
    $n = function ($f) use ($i) { return 'repos[' . $i . '][' . $f . ']'; };
    ?>
    <li class="repo-row" draggable="true">
      <span class="grip" title="Drag to reorder"><?php echo admin_icon('grip'); ?></span>
      <input class="repo-row__name" name="<?php echo e($n('repo')); ?>" value="<?php echo e($r['repo']); ?>" readonly>
      <select name="<?php echo e($n('size')); ?>" title="Card size">
        <?php foreach (array('large' => 'Large', 'normal' => 'Normal', 'compact' => 'Compact') as $k => $label): ?>
          <option value="<?php echo $k; ?>" <?php echo $r['size'] === $k ? 'selected' : ''; ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
      <input name="<?php echo e($n('tags')); ?>" value="<?php echo e(implode(', ', $r['tags'])); ?>" placeholder="Tags, comma separated">
      <input name="<?php echo e($n('note')); ?>" value="<?php echo e($r['note']); ?>" placeholder="Description (optional; GitHub's is used otherwise)">
      <label class="toggle"><input type="checkbox" name="<?php echo e($n('visible')); ?>" value="1" <?php echo !empty($r['visible']) ? 'checked' : ''; ?>> Shown</label>
      <button type="button" class="btn tiny danger" data-remove-row>Remove</button>
    </li>
    <?php
}

function admin_stat_table(array $rows, $label, $isPage = false)
{
    if (!$rows) {
        echo '<p class="empty">No data yet.</p>';
        return;
    }
    $top = array_slice($rows, 0, 10, true);
    $max = 1;
    foreach ($top as $v) $max = max($max, is_array($v) ? $v['views'] : $v);
    echo '<table class="stat-table"><thead><tr><th>' . e($label) . '</th>' . ($isPage ? '<th>Views</th><th>Visitors</th><th>Avg. time</th>' : '<th>Visitors</th>') . '</tr></thead><tbody>';
    foreach ($top as $k => $v) {
        $count = is_array($v) ? $v['views'] : $v;
        $shown = $isPage ? '<a href="' . e(rtrim(cms_config('base_url'), '/') . $k) . '" target="_blank" rel="noopener">' . e($k) . '</a>' : e($k);
        echo '<tr><td><span class="bar" style="width:' . round($count / $max * 100) . '%"></span><span class="label">' . $shown . '</span></td>';
        if ($isPage) echo '<td>' . $v['views'] . '</td><td>' . $v['visitors'] . '</td><td>' . ($v['time'] ? $v['time'] . 's' : '–') . '</td>';
        else echo '<td>' . $v . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

function admin_file_menu($f, $dirRel, $url, $src = 'files')
{
    $ro = !cms_media_source($src)['writable'];
    ?>
    <div class="file-actions">
      <?php if ($url): ?>
        <?php $isImg = isset($f['ext']) && in_array($f['ext'], array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'), true); ?>
        <button type="button" class="btn tiny" data-copy-text="<?php echo e(($isImg ? '!' : '') . '[' . pathinfo($f['name'], PATHINFO_FILENAME) . '](' . cms_files_content_path($f['rel'], $src) . ')'); ?>" title="Copy Markdown to paste into a post or project">Copy Markdown</button>
        <button type="button" class="btn tiny" data-copy="<?php echo e($url); ?>" title="Copy full link">Copy link</button>
      <?php endif; ?>
      <?php if ($ro): ?></div><?php return; endif; ?>
      <form method="post" data-prompt="Rename to" data-prompt-field="name" data-prompt-default="<?php echo e($f['name']); ?>">
        <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="files_rename"><input type="hidden" name="dir" value="<?php echo e($dirRel); ?>">
        <input type="hidden" name="path" value="<?php echo e($f['rel']); ?>"><input type="hidden" name="name"><button class="btn tiny">Rename</button>
      </form>
      <form method="post" data-confirm="Move “<?php echo e($f['name']); ?>” to the trash?">
        <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="files_delete"><input type="hidden" name="dir" value="<?php echo e($dirRel); ?>">
        <input type="hidden" name="path" value="<?php echo e($f['rel']); ?>"><button class="btn tiny danger">Delete</button>
      </form>
    </div>
    <?php
}
