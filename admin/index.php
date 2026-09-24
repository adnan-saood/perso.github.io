<?php
// Admin panel: posts, news, files, trash, settings.
require_once __DIR__ . '/../cms/boot.php';

header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; font-src 'self' data: https://cdnjs.cloudflare.com; img-src 'self' data: blob: https:; frame-ancestors 'none'; form-action 'self'; base-uri 'none'");
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
<link rel="stylesheet" href="admin.css?v=3">
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
        'posts' => array('Blog posts', 'posts', array('page' => 'items', 'type' => 'posts')),
        'news' => array('News', 'news', array('page' => 'items', 'type' => 'news')),
        'projects' => array('Projects', 'cube', array('page' => 'items', 'type' => 'projects')),
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
    echo '<script src="admin.js?v=3"></script></body></html>';
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
            $meta = $existing ? $existing['meta'] : ($type === 'projects' ? array() : array('layout' => 'post'));
            $meta['title'] = $title;
            if ($type !== 'projects') $meta['date'] = $date;
            $meta['draft'] = !empty($_POST['draft']);
            if ($type === 'projects') {
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
            admin_flash('ok', 'Saved' . ($meta['draft'] ? ' as draft' : ' and published') . '.');
            admin_redirect(array('page' => 'edit', 'type' => $type, 'slug' => $slug));

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
    $abs = cms_files_resolve(isset($_GET['dir']) ? (string) $_GET['dir'] : '');
    if (!$abs || !is_dir($abs)) admin_json(array('error' => 'Folder not found.'), 404);
    list($dirs, $files) = cms_files_list($abs);
    $rel = cms_files_rel($abs);
    $images = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'ico');
    $out = array('dir' => $rel, 'parent' => $rel === '' ? null : (dirname($rel) === '.' ? '' : dirname($rel)), 'dirs' => array(), 'files' => array());
    foreach ($dirs as $d) $out['dirs'][] = array('name' => $d['name'], 'rel' => $d['rel']);
    foreach ($files as $f) {
        $out['files'][] = array(
            'name' => $f['name'],
            'path' => cms_config('files_url') . $f['rel'], // base-free, for content
            'url' => cms_files_public_url($f['rel']),       // for thumbnails
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
    <input class="search" type="search" placeholder="Filter…" data-filter="#item-list">
    <div class="list" id="item-list">
      <?php if (!$items): ?><p class="empty">Nothing here yet.</p><?php endif; ?>
      <?php foreach ($items as $it): ?>
        <a class="row" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => $type, 'slug' => $it['slug']))); ?>">
          <?php if ($type === 'projects'): ?>
            <span class="date">#<?php echo (int) $it['importance']; ?></span>
          <?php else: ?>
            <span class="date"><?php echo date('M j, Y', $it['date']); ?></span>
          <?php endif; ?>
          <span class="grow"><strong><?php echo e($it['title'] !== '' ? $it['title'] : cms_excerpt($it, 80)); ?></strong>
            <?php if ($it['draft']): ?><em class="pill">draft</em><?php endif; ?>
            <?php if ($type !== 'projects' && $it['date'] > time()): ?><em class="pill blue">scheduled</em><?php endif; ?>
            <?php if (!empty($it['featured'])): ?><em class="pill gold"><?php echo $type === 'projects' ? 'on homepage' : 'featured'; ?></em><?php endif; ?>
          </span>
          <span class="muted small"><?php echo e($type === 'projects' ? $it['category'] : implode(', ', $it['tags'])); ?></span>
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
    $urlFns = array('posts' => 'cms_post_url', 'news' => 'cms_news_url', 'projects' => 'cms_project_url');
    $publicUrl = $it ? call_user_func($urlFns[$type], $it['slug']) : '';
    $categories = array();
    if ($type === 'projects') {
        foreach (cms_list_items('projects', true) as $pr) if ($pr['category'] !== '') $categories[$pr['category']] = true;
    }

    admin_layout_start($it ? 'Edit ' . $types[$type]['singular'] : 'New ' . $types[$type]['singular'], 'edit');
    ?>
    <form method="post" class="editor" id="editor-form" data-base="<?php echo e(cms_config('base_url')); ?>" data-upload-url="<?php echo e(admin_url()); ?>" data-csrf="<?php echo e(cms_csrf_token()); ?>">
      <?php echo cms_csrf_field(); ?>
      <input type="hidden" name="action" value="save_item">
      <input type="hidden" name="type" value="<?php echo e($type); ?>">
      <input type="hidden" name="orig_slug" value="<?php echo e($slug); ?>">

      <header class="top">
        <a class="back" href="<?php echo e(admin_url(array('page' => 'items', 'type' => $type))); ?>">&larr; <?php echo e($types[$type]['label']); ?></a>
        <div class="actions">
          <?php if ($it && !$it['draft']): ?><a class="btn" href="<?php echo e($publicUrl); ?>" target="_blank" rel="noopener"><?php echo admin_icon('ext'); ?> View</a><?php endif; ?>
          <label class="toggle"><input type="checkbox" name="draft" value="1" <?php echo $it && $it['draft'] ? 'checked' : ''; ?>> Draft</label>
          <button class="btn primary" accesskey="s">Save</button>
        </div>
      </header>

      <input class="title-input" name="title" placeholder="<?php echo $type === 'news' ? 'Headline (optional for short items)' : ($type === 'projects' ? 'Project name' : 'Post title'); ?>"
             value="<?php echo e($it ? $it['title'] : ''); ?>" <?php echo $type !== 'news' ? 'required' : ''; ?> autofocus>

      <div class="grid meta">
        <?php if ($type !== 'projects'): ?>
          <label>Date<input type="date" name="date" value="<?php echo date('Y-m-d', $date); ?>" required></label>
          <label>Time <small>(optional)</small><input type="time" name="time" value="<?php echo $hasTime ? date('H:i', $date) : ''; ?>"></label>
        <?php endif; ?>
        <label>Slug <small>(address)</small><input name="slug" value="<?php echo e($slug); ?>" placeholder="auto from title" pattern="[a-z0-9][a-z0-9-]*"></label>
        <?php if ($type === 'projects'): ?>
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

      <textarea name="body" id="body"><?php echo e($body); ?></textarea>
      <p class="muted small">Markdown. Drag &amp; drop or paste images straight into the editor. Ctrl+S saves.</p>
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

case 'files':
    $dirRel = isset($_GET['dir']) ? (string) $_GET['dir'] : '';
    $abs = cms_files_resolve($dirRel);
    if (!$abs || !is_dir($abs)) { admin_flash('err', 'Folder not found.'); admin_redirect(array('page' => 'files')); }
    $dirRel = cms_files_rel($abs);
    list($dirs, $files) = cms_files_list($abs);
    $crumbs = array(array('files', ''));
    $acc = '';
    foreach ($dirRel === '' ? array() : explode('/', $dirRel) as $part) {
        $acc = ltrim($acc . '/' . $part, '/');
        $crumbs[] = array($part, $acc);
    }
    admin_layout_start('Files', 'files');
    ?>
    <header class="top">
      <h1 class="crumbs"><?php foreach ($crumbs as $i => $c): ?><?php if ($i): ?><span>/</span><?php endif; ?><a href="<?php echo e(admin_url(array('page' => 'files', 'dir' => $c[1]))); ?>"><?php echo e($c[0]); ?></a><?php endforeach; ?></h1>
      <form method="post" class="inline" data-prompt="New folder name" data-prompt-field="name">
        <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="files_mkdir"><input type="hidden" name="dir" value="<?php echo e($dirRel); ?>"><input type="hidden" name="name">
        <button class="btn"><?php echo admin_icon('plus'); ?> New folder</button>
      </form>
    </header>

    <form method="post" enctype="multipart/form-data" class="dropzone" id="dropzone">
      <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="files_upload"><input type="hidden" name="dir" value="<?php echo e($dirRel); ?>">
      <input type="file" name="files[]" id="file-input" multiple>
      <label for="file-input"><?php echo admin_icon('up'); ?><strong>Drop files here</strong> or click to choose · max <?php echo e(ini_get('upload_max_filesize')); ?> each</label>
    </form>

    <form method="post" id="bulk" data-confirm="Move the selected items to the trash?">
      <?php echo cms_csrf_field(); ?><input type="hidden" name="action" value="files_delete"><input type="hidden" name="dir" value="<?php echo e($dirRel); ?>">
      <div class="bulkbar" hidden><span data-count></span> selected <button class="btn danger small"><?php echo admin_icon('trash'); ?> Delete</button></div>
    </form>

    <div class="files">
      <?php if ($dirRel !== ''): ?>
        <a class="file dir" href="<?php echo e(admin_url(array('page' => 'files', 'dir' => dirname($dirRel) === '.' ? '' : dirname($dirRel)))); ?>"><span class="thumb"><?php echo admin_icon('folder'); ?></span><span class="name">..</span></a>
      <?php endif; ?>
      <?php foreach ($dirs as $d): ?>
        <div class="file dir">
          <input type="checkbox" form="bulk" name="paths[]" value="<?php echo e($d['rel']); ?>" aria-label="Select">
          <a class="thumb" href="<?php echo e(admin_url(array('page' => 'files', 'dir' => $d['rel']))); ?>"><?php echo admin_icon('folder'); ?></a>
          <a class="name" href="<?php echo e(admin_url(array('page' => 'files', 'dir' => $d['rel']))); ?>"><?php echo e($d['name']); ?></a>
          <?php admin_file_menu($d, $dirRel, false); ?>
        </div>
      <?php endforeach; ?>
      <?php foreach ($files as $f): $url = cms_files_public_url($f['rel']); ?>
        <div class="file">
          <input type="checkbox" form="bulk" name="paths[]" value="<?php echo e($f['rel']); ?>" aria-label="Select">
          <a class="thumb" href="<?php echo e($url); ?>" target="_blank" rel="noopener">
            <?php if (in_array($f['ext'], array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'ico'), true)): ?>
              <img src="<?php echo e($url); ?>" alt="" loading="lazy">
            <?php else: ?><?php echo admin_icon('file'); ?><em><?php echo e($f['ext']); ?></em><?php endif; ?>
          </a>
          <span class="name" title="<?php echo e($f['name']); ?>"><?php echo e($f['name']); ?></span>
          <span class="muted small"><?php echo cms_human_size($f['size']); ?></span>
          <?php admin_file_menu($f, $dirRel, $url); ?>
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
    admin_layout_start('Dashboard', 'dashboard');
    ?>
    <header class="top"><h1>Hello, <?php echo e(strtok(cms_config('site_name'), ' ')); ?> 👋</h1>
      <div class="actions">
        <a class="btn primary" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => 'news'))); ?>"><?php echo admin_icon('plus'); ?> News</a>
        <a class="btn primary" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => 'posts'))); ?>"><?php echo admin_icon('plus'); ?> Blog post</a>
        <a class="btn primary" href="<?php echo e(admin_url(array('page' => 'edit', 'type' => 'projects'))); ?>"><?php echo admin_icon('plus'); ?> Project</a>
      </div>
    </header>
    <div class="stats">
      <a class="stat" href="<?php echo e(admin_url(array('page' => 'items', 'type' => 'posts'))); ?>"><b><?php echo count($posts); ?></b><span>blog posts</span></a>
      <a class="stat" href="<?php echo e(admin_url(array('page' => 'items', 'type' => 'news'))); ?>"><b><?php echo count($news); ?></b><span>news items</span></a>
      <a class="stat" href="<?php echo e(admin_url(array('page' => 'items', 'type' => 'projects'))); ?>"><b><?php echo count($projects); ?></b><span>projects</span></a>
      <div class="stat"><b><?php echo $drafts; ?></b><span>drafts</span></div>
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

function admin_file_menu($f, $dirRel, $url)
{
    ?>
    <div class="file-actions">
      <?php if ($url): ?>
        <?php $isImg = isset($f['ext']) && in_array($f['ext'], array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'), true); ?>
        <button type="button" class="btn tiny" data-copy-text="<?php echo e(($isImg ? '!' : '') . '[' . pathinfo($f['name'], PATHINFO_FILENAME) . '](' . cms_config('files_url') . $f['rel'] . ')'); ?>" title="Copy Markdown to paste into a post or project">Copy Markdown</button>
        <button type="button" class="btn tiny" data-copy="<?php echo e($url); ?>" title="Copy full link">Copy link</button>
      <?php endif; ?>
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
