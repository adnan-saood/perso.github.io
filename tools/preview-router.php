<?php
// Router for PHP's built-in server: serves _site/ like Apache on perso.ensta.fr would,
// executing .php files (blog, news, admin, cms/api.php). Used by tools/preview.sh.

$site = realpath(__DIR__ . '/../_site');
$base = rtrim((string) getenv('CMS_BASE_URL'), '/');   // "" or "/~saood"
$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($base !== '' && strpos($uri, $base . '/') !== 0) {
    header('Location: ' . $base . '/');
    return true;
}
$rel = substr($uri, strlen($base));
$path = $site . $rel;
// Uploads live outside _site in the preview (Jekyll rebuilds would delete them).
$filesDir = getenv('CMS_FILES_DIR');
if ($filesDir && strpos($rel, '/files/') === 0) {
    $site = realpath($filesDir);
    $path = $site . substr($rel, strlen('/files'));
}
if (is_dir($path)) {
    if (substr($uri, -1) !== '/') { header('Location: ' . $uri . '/'); return true; }
    // Same order as Apache's DirectoryIndex.
    foreach (array('index.html', 'index.php') as $idx) {
        if (is_file($path . $idx)) { $path .= $idx; break; }
    }
}
$real = realpath($path);
if ($real === false || strpos($real, $site) !== 0 || !is_file($real)) {
    http_response_code(404);
    $page = $site . '/404.html';
    if (is_file($page)) readfile($page); else echo 'Not found';
    return true;
}

if (substr($real, -4) === '.php') {
    $_SERVER['SCRIPT_NAME'] = $base . substr($real, strlen($site));
    $_SERVER['SCRIPT_FILENAME'] = $real;
    chdir(dirname($real));
    require $real;
    return true;
}

$types = array(
    'html' => 'text/html; charset=utf-8', 'css' => 'text/css', 'js' => 'text/javascript', 'json' => 'application/json',
    'xml' => 'application/xml', 'svg' => 'image/svg+xml', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
    'gif' => 'image/gif', 'webp' => 'image/webp', 'avif' => 'image/avif', 'ico' => 'image/x-icon', 'pdf' => 'application/pdf',
    'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf', 'txt' => 'text/plain; charset=utf-8',
    'bib' => 'text/plain; charset=utf-8', 'mp4' => 'video/mp4', 'webm' => 'video/webm',
);
$ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
header('Content-Type: ' . (isset($types[$ext]) ? $types[$ext] : 'application/octet-stream'));
header('Content-Length: ' . filesize($real));
readfile($real);
return true;
