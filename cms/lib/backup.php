<?php
// Backup & restore of everything the admin manages, as a plain .tar archive
// (pure PHP: the server has no zip extension). Layout inside the archive:
//   cms-data/...   posts, news, projects, publications, talks, CV, settings, stats
//   files/...      uploads

if (!defined('CMS_BOOT')) { http_response_code(404); exit; }

// Paths that are machine state, not content.
function cms_backup_skip($rel)
{
    return preg_match('#^cms-data/(sessions|state|backups|_seed)(/|$)|/\.salt-|/\.tmp_#', $rel) === 1;
}

// Yields array(archive path, absolute path) for every file to back up.
function cms_backup_files()
{
    $roots = array('cms-data' => cms_data_path(), 'files' => cms_config('files_dir'));
    $out = array();
    foreach ($roots as $prefix => $dir) {
        if (!is_dir($dir)) continue;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if (!$f->isFile()) continue;
            $rel = $prefix . '/' . str_replace('\\', '/', substr($f->getPathname(), strlen(rtrim($dir, '/')) + 1));
            if (!cms_backup_skip($rel)) $out[] = array($rel, $f->getPathname());
        }
    }
    return $out;
}

function cms_tar_header($name, $size, $mtime)
{
    $prefix = '';
    if (strlen($name) > 100) { // ustar: split long paths into prefix (155) + name (100)
        $cut = strrpos(substr($name, 0, 156), '/');
        if ($cut === false || strlen($name) - $cut - 1 > 100) return null;
        $prefix = substr($name, 0, $cut);
        $name = substr($name, $cut + 1);
    }
    $h = str_pad($name, 100, "\0") . str_pad('0000644', 8, "\0") . str_pad('0000000', 8, "\0") . str_pad('0000000', 8, "\0")
        . str_pad(decoct($size), 11, '0', STR_PAD_LEFT) . "\0" . str_pad(decoct($mtime), 11, '0', STR_PAD_LEFT) . "\0"
        . '        ' . '0' . str_repeat("\0", 100) . "ustar\0" . '00' . str_repeat("\0", 32) . str_repeat("\0", 32)
        . str_repeat("\0", 8) . str_repeat("\0", 8) . str_pad($prefix, 155, "\0") . str_repeat("\0", 12);
    $sum = 0;
    for ($i = 0; $i < 512; $i++) $sum += ord($h[$i]);
    return substr_replace($h, str_pad(decoct($sum), 6, '0', STR_PAD_LEFT) . "\0 ", 148, 8);
}

// Writes a tar archive to a stream (php://output for downloads, or a file).
function cms_backup_write($stream)
{
    $n = 0;
    foreach (cms_backup_files() as $f) {
        $size = filesize($f[1]);
        $header = cms_tar_header($f[0], $size, filemtime($f[1]));
        if ($header === null) continue;
        fwrite($stream, $header);
        $in = fopen($f[1], 'rb');
        stream_copy_to_stream($in, $stream);
        fclose($in);
        if ($size % 512) fwrite($stream, str_repeat("\0", 512 - $size % 512));
        $n++;
    }
    fwrite($stream, str_repeat("\0", 1024));
    return $n;
}

// Saves a snapshot inside cms-data/backups/ (used before every restore).
function cms_backup_snapshot($label)
{
    $dir = cms_data_path('backups');
    if (!is_dir($dir) && !@mkdir($dir, 0770, true)) return null;
    $file = $dir . '/' . $label . '-' . date('Ymd-His') . '.tar';
    $fh = fopen($file, 'wb');
    if (!$fh) return null;
    cms_backup_write($fh);
    fclose($fh);
    return $file;
}

// Restores a tar produced by cms_backup_write(). Existing files are overwritten;
// files that are not in the archive are left alone. Returns array(count, errors).
function cms_backup_restore($tarFile)
{
    $in = fopen($tarFile, 'rb');
    if (!$in) return array(0, array('Cannot read the uploaded archive.'));
    $roots = array('cms-data' => rtrim(cms_data_path(), '/'), 'files' => rtrim(cms_config('files_dir'), '/'));
    $count = 0;
    $errors = array();
    while (!feof($in)) {
        $h = fread($in, 512);
        if (strlen($h) < 512 || trim($h, "\0") === '') break;
        $name = rtrim(substr($h, 0, 100), "\0");
        $prefix = rtrim(substr($h, 345, 155), "\0");
        if ($prefix !== '') $name = $prefix . '/' . $name;
        $size = octdec(trim(substr($h, 124, 12), "\0 "));
        $type = $h[156];
        $data = $size > 0 ? stream_get_contents($in, $size) : '';
        if ($size % 512) fread($in, 512 - $size % 512);
        if ($type !== '0' && $type !== "\0") continue; // only regular files
        $parts = explode('/', $name, 2);
        $ok = count($parts) === 2 && isset($roots[$parts[0]]) && strpos('/' . $parts[1] . '/', '/../') === false
            && preg_match('#^[A-Za-z0-9._/ -]+$#', $parts[1]) && !cms_backup_skip($name);
        // Never let an archive drop executable code into the web-visible uploads folder.
        if ($ok && $parts[0] === 'files' && !in_array(strtolower(pathinfo($parts[1], PATHINFO_EXTENSION)), cms_allowed_extensions(), true)) $ok = false;
        if (!$ok) {
            $errors[] = 'Skipped ' . $name;
            continue;
        }
        $dest = $roots[$parts[0]] . '/' . $parts[1];
        if (!is_dir(dirname($dest))) @mkdir(dirname($dest), 0775, true);
        if (@file_put_contents($dest, $data) === false) $errors[] = 'Could not write ' . $name;
        else $count++;
    }
    fclose($in);
    return array($count, $errors);
}
