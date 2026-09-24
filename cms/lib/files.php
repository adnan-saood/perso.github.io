<?php
// File manager restricted to one web-visible folder (config files_dir).

if (!defined('CMS_BOOT')) { http_response_code(404); exit; }

// Only harmless types. HTML, SVG, JS and anything executable are refused
// because they would run on the same origin as the admin panel.
function cms_allowed_extensions()
{
    return array(
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'ico',
        'pdf', 'txt', 'md', 'bib', 'csv', 'json', 'zip', 'tar', 'gz',
        'mp4', 'webm', 'mov', 'mp3', 'wav', 'ogg',
        'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'odt', 'odp', 'ods',
        'woff', 'woff2', 'ttf',
    );
}

function cms_files_root()
{
    $root = cms_config('files_dir');
    if (!is_dir($root)) @mkdir($root, 0775, true);
    return realpath($root);
}

// Turn a user-supplied relative path into an absolute one inside the root, or null.
function cms_files_resolve($rel, $mustExist = true)
{
    $root = cms_files_root();
    if (!$root) return null;
    $rel = trim(str_replace('\\', '/', (string) $rel), '/');
    if ($rel === '') return $root;
    foreach (explode('/', $rel) as $part) {
        if ($part === '' || $part === '.' || $part === '..' || $part[0] === '.') return null;
    }
    $path = $root . '/' . $rel;
    if ($mustExist) {
        $real = realpath($path);
        if ($real === false || ($real !== $root && !cms_starts_with($real, $root . '/'))) return null;
        return $real;
    }
    $parent = realpath(dirname($path));
    if ($parent === false || ($parent !== $root && !cms_starts_with($parent, $root . '/'))) return null;
    return $parent . '/' . basename($path);
}

// Create (if needed) a nested folder inside the root, one segment at a time.
function cms_files_ensure_dir($rel)
{
    $path = '';
    foreach (explode('/', trim($rel, '/')) as $part) {
        $path = ltrim($path . '/' . $part, '/');
        $abs = cms_files_resolve($path, false);
        if (!$abs) return null;
        if (!is_dir($abs) && !@mkdir($abs, 0775)) return null;
    }
    return cms_files_resolve($rel);
}

function cms_files_rel($abs)
{
    $root = cms_files_root();
    return $abs === $root ? '' : substr($abs, strlen($root) + 1);
}

function cms_safe_filename($name, $isDir = false)
{
    $name = basename(str_replace('\\', '/', (string) $name));
    $ext = '';
    if (!$isDir && preg_match('/\.([A-Za-z0-9]{1,8})$/', $name, $m)) {
        $ext = strtolower($m[1]);
        $name = substr($name, 0, -strlen($m[0]));
    }
    $name = cms_slugify($name);
    if ($name === '') $name = $isDir ? 'folder' : 'file';
    if ($isDir) return $name;
    if (!in_array($ext, cms_allowed_extensions(), true)) return null;
    return $name . '.' . $ext;
}

function cms_files_list($absDir)
{
    $dirs = array();
    $files = array();
    foreach (scandir($absDir) as $n) {
        if ($n === '' || $n[0] === '.') continue;
        $p = $absDir . '/' . $n;
        $entry = array('name' => $n, 'rel' => cms_files_rel($p), 'mtime' => filemtime($p));
        if (is_dir($p)) {
            $dirs[] = $entry;
        } else {
            $entry['size'] = filesize($p);
            $entry['ext'] = strtolower(pathinfo($n, PATHINFO_EXTENSION));
            $files[] = $entry;
        }
    }
    $by = function ($a, $b) { return strnatcasecmp($a['name'], $b['name']); };
    usort($dirs, $by);
    usort($files, $by);
    return array($dirs, $files);
}

function cms_files_public_url($rel)
{
    $parts = array_map('rawurlencode', explode('/', $rel));
    return cms_url(cms_config('files_url') . implode('/', $parts));
}

// Saves one entry of $_FILES into $absDir. Returns array(relPath|null, error|null).
function cms_files_store_upload(array $f, $absDir)
{
    if (!isset($f['error']) || $f['error'] !== UPLOAD_ERR_OK) {
        $codes = array(UPLOAD_ERR_INI_SIZE => 'File is larger than the server allows (' . ini_get('upload_max_filesize') . ').',
                       UPLOAD_ERR_FORM_SIZE => 'File too large.', UPLOAD_ERR_PARTIAL => 'Upload was interrupted.',
                       UPLOAD_ERR_NO_FILE => 'No file received.');
        $code = isset($f['error']) ? $f['error'] : -1;
        return array(null, isset($codes[$code]) ? $codes[$code] : 'Upload failed (code ' . $code . ').');
    }
    if (!is_uploaded_file($f['tmp_name'])) return array(null, 'Invalid upload.');
    $name = cms_safe_filename($f['name']);
    if ($name === null) return array(null, '"' . $f['name'] . '": this file type is not allowed.');

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true) && @getimagesize($f['tmp_name']) === false) {
        return array(null, '"' . $f['name'] . '" is not a valid image.');
    }
    $target = $absDir . '/' . $name;
    $base = substr($name, 0, -strlen($ext) - 1);
    $i = 2;
    while (file_exists($target)) $target = $absDir . '/' . $base . '-' . $i++ . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $target)) return array(null, 'Could not save the file (is the folder writable?).');
    @chmod($target, 0644);
    return array(cms_files_rel($target), null);
}

// Normalise PHP's awkward multi-file $_FILES structure.
function cms_files_from_request($field)
{
    if (empty($_FILES[$field])) return array();
    $f = $_FILES[$field];
    if (!is_array($f['name'])) return array($f);
    $out = array();
    foreach ($f['name'] as $i => $n) {
        $out[] = array('name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i],
                       'error' => $f['error'][$i], 'size' => $f['size'][$i]);
    }
    return $out;
}

function cms_human_size($bytes)
{
    $u = array('B', 'KB', 'MB', 'GB');
    $i = 0;
    while ($bytes >= 1024 && $i < 3) { $bytes /= 1024; $i++; }
    return ($i ? number_format($bytes, 1) : $bytes) . ' ' . $u[$i];
}
