<?php
// Core of the flat-file CMS: config, paths, content store, markdown.
// Must stay compatible with PHP 7.2 (Ubuntu 18.04): no arrow functions,
// no typed properties, no str_contains/str_starts_with, no match.

if (!defined('CMS_BOOT')) { http_response_code(404); exit; }

define('CMS_LIB', __DIR__);
define('CMS_DIR', dirname(__DIR__));          // public_html/cms
define('CMS_WEB_ROOT', dirname(CMS_DIR));     // public_html

require_once CMS_LIB . '/Parsedown.php';

// ---------------------------------------------------------------------------
// Config
// ---------------------------------------------------------------------------

function cms_config($key = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = array(
            // URL path of the site root, with trailing slash.
            'base_url' => '/~saood/',
            // Where posts, news and settings live. Keep it outside
            // public_html so a redeploy can never overwrite it.
            'data_dir' => dirname(CMS_WEB_ROOT) . '/cms-data',
            // Web-visible folder managed by the file manager.
            'files_dir' => CMS_WEB_ROOT . '/files',
            'files_url' => 'files/',
            'site_name' => 'Adnan Saood',
            'posts_per_page' => 8,
            'timezone' => 'Europe/Paris',
        );
        $local = CMS_DIR . '/config.local.php';
        if (is_file($local)) {
            $over = include $local;
            if (is_array($over)) $cfg = array_merge($cfg, $over);
        }
        date_default_timezone_set($cfg['timezone']);
    }
    return $key === null ? $cfg : (isset($cfg[$key]) ? $cfg[$key] : null);
}

function cms_data_path($sub = '')
{
    $p = rtrim(cms_config('data_dir'), '/');
    return $sub === '' ? $p : $p . '/' . ltrim($sub, '/');
}

function cms_url($path = '')
{
    return cms_config('base_url') . ltrim($path, '/');
}

// Mutable settings written by the admin panel (password hash, session epoch).
function cms_settings($key = null)
{
    $s = cms_read_json(cms_data_path('settings.json'), array());
    if ($key === null) return $s;
    return isset($s[$key]) ? $s[$key] : null;
}

function cms_save_settings(array $changes)
{
    $s = cms_settings();
    foreach ($changes as $k => $v) $s[$k] = $v;
    return cms_write_json(cms_data_path('settings.json'), $s, 0600);
}

// ---------------------------------------------------------------------------
// Small helpers
// ---------------------------------------------------------------------------

function e($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function cms_starts_with($haystack, $needle)
{
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function cms_slugify($s)
{
    $s = (string) $s;
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($t !== false) $s = $t;
    }
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    $s = trim($s, '-');
    return substr($s, 0, 80);
}

function cms_valid_slug($s)
{
    return is_string($s) && preg_match('/^[a-z0-9][a-z0-9-]{0,99}$/', $s) === 1;
}

function cms_read_json($file, $default)
{
    if (!is_file($file)) return $default;
    $d = json_decode((string) file_get_contents($file), true);
    return is_array($d) ? $d : $default;
}

// Atomic write: temp file + rename, so a crash never leaves half a file.
// $mode: pass 0600 for secrets (settings.json holds the password hash).
function cms_write_file($file, $content, $mode = null)
{
    $dir = dirname($file);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) return false;
    $tmp = $dir . '/.tmp_' . bin2hex(random_bytes(6));
    if (@file_put_contents($tmp, $content, LOCK_EX) === false) return false;
    if ($mode !== null) @chmod($tmp, $mode);
    if (!@rename($tmp, $file)) { @unlink($tmp); return false; }
    return true;
}

function cms_write_json($file, $data, $mode = null)
{
    return cms_write_file($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $mode);
}

function cms_client_ip()
{
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

function cms_format_date($ts, $fmt = 'M j, Y')
{
    return date($fmt, $ts);
}

// ---------------------------------------------------------------------------
// Front matter (the YAML subset Jekyll posts actually use)
// ---------------------------------------------------------------------------

function cms_parse_scalar($v)
{
    $v = trim($v);
    if ($v === '') return '';
    $q = $v[0];
    if (($q === '"' || $q === "'") && substr($v, -1) === $q && strlen($v) >= 2) {
        $inner = substr($v, 1, -1);
        if ($q === '"') {
            $j = json_decode('"' . $inner . '"');
            return $j === null ? $inner : $j;
        }
        return str_replace("''", "'", $inner);
    }
    if ($v[0] === '[' && substr($v, -1) === ']') {
        $items = array();
        foreach (explode(',', substr($v, 1, -1)) as $it) {
            $it = cms_parse_scalar($it);
            if ($it !== '') $items[] = $it;
        }
        return $items;
    }
    $l = strtolower($v);
    if ($l === 'true') return true;
    if ($l === 'false') return false;
    if ($l === 'null' || $l === '~') return null;
    // strip trailing comments
    $v = preg_replace('/\s+#.*$/', '', $v);
    return $v;
}

function cms_parse_document($raw)
{
    $raw = str_replace("\r\n", "\n", (string) $raw);
    $meta = array();
    $body = $raw;
    if (preg_match('/^---\n(.*?)\n---\n?(.*)$/s', $raw, $m)) {
        $body = $m[2];
        $key = null;
        $block = null; // lines of a YAML block scalar (">" folded or "|" literal)
        $blockSep = ' ';
        foreach (explode("\n", $m[1]) as $line) {
            if ($block !== null) {
                if (trim($line) === '' || preg_match('/^\s+\S/', $line)) {
                    $block[] = trim($line);
                    continue;
                }
                $meta[$key] = trim(implode($blockSep, $block));
                $block = null;
            }
            if (trim($line) === '' || preg_match('/^\s*#/', $line)) continue;
            if ($key !== null && preg_match('/^\s+-\s*(.*)$/', $line, $mm)) {
                if (!is_array($meta[$key])) $meta[$key] = array();
                $meta[$key][] = cms_parse_scalar($mm[1]);
                continue;
            }
            if (preg_match('/^([A-Za-z0-9_-]+):\s*(.*)$/', $line, $mm)) {
                $key = $mm[1];
                if (preg_match('/^([>|])[-+]?$/', trim($mm[2]), $bm)) {
                    $block = array();
                    $blockSep = $bm[1] === '|' ? "\n" : ' ';
                    continue;
                }
                $meta[$key] = cms_parse_scalar($mm[2]);
            }
        }
        if ($block !== null) $meta[$key] = trim(implode($blockSep, $block));
    }
    return array($meta, ltrim($body, "\n"));
}

function cms_yaml_value($v)
{
    if (is_bool($v)) return $v ? 'true' : 'false';
    if ($v === null) return 'null';
    if (is_array($v)) {
        $out = array();
        foreach ($v as $it) $out[] = cms_yaml_value((string) $it);
        return '[' . implode(', ', $out) . ']';
    }
    $v = (string) $v;
    if (preg_match('/^[A-Za-z0-9][A-Za-z0-9 ._\/-]*$/', $v) && !in_array(strtolower($v), array('true', 'false', 'null', 'yes', 'no'))) {
        return $v;
    }
    return json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function cms_build_document(array $meta, $body)
{
    $out = "---\n";
    foreach ($meta as $k => $v) {
        if ($v === '' || $v === null || $v === array()) continue;
        $out .= $k . ': ' . cms_yaml_value($v) . "\n";
    }
    return $out . "---\n\n" . rtrim(str_replace("\r\n", "\n", $body)) . "\n";
}

// Normalise a list-ish field: al-folio uses "a b c" strings, we also accept arrays.
function cms_list($v)
{
    if (is_array($v)) $items = $v;
    elseif (is_string($v) && trim($v) !== '') $items = preg_split('/[\s,]+/', trim($v));
    else $items = array();
    $out = array();
    foreach ($items as $it) {
        $it = trim((string) $it);
        if ($it !== '' && !in_array($it, $out, true)) $out[] = $it;
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Content store: posts and news are Markdown files with front matter.
// ---------------------------------------------------------------------------

function cms_types()
{
    return array(
        'posts' => array('label' => 'Blog posts', 'singular' => 'post'),
        'news' => array('label' => 'News', 'singular' => 'news item'),
    );
}

function cms_item_file($type, $slug)
{
    return cms_data_path($type . '/' . $slug . '.md');
}

function cms_load_item($type, $slug)
{
    if (!isset(cms_types()[$type]) || !cms_valid_slug($slug)) return null;
    $file = cms_item_file($type, $slug);
    if (!is_file($file)) return null;
    list($meta, $body) = cms_parse_document(file_get_contents($file));
    return cms_normalise_item($type, $slug, $meta, $body, filemtime($file));
}

function cms_normalise_item($type, $slug, array $meta, $body, $mtime)
{
    $ts = isset($meta['date']) ? strtotime((string) $meta['date']) : false;
    $item = array(
        'type' => $type,
        'slug' => $slug,
        'title' => isset($meta['title']) ? (string) $meta['title'] : '',
        'date' => $ts ? $ts : $mtime,
        'description' => isset($meta['description']) ? (string) $meta['description'] : '',
        'tags' => cms_list(isset($meta['tags']) ? $meta['tags'] : array()),
        'categories' => cms_list(isset($meta['categories']) ? $meta['categories'] : array()),
        'featured' => !empty($meta['featured']),
        'draft' => !empty($meta['draft']),
        'inline' => !empty($meta['inline']),
        'thumbnail' => isset($meta['thumbnail']) ? (string) $meta['thumbnail'] : '',
        'body' => $body,
        'updated' => $mtime,
        'meta' => $meta,
    );
    return $item;
}

// All items of a type, newest first. Drafts excluded unless $withDrafts.
function cms_list_items($type, $withDrafts = false)
{
    $dir = cms_data_path($type);
    $items = array();
    if (!is_dir($dir)) return $items;
    foreach (glob($dir . '/*.md') as $file) {
        $slug = basename($file, '.md');
        if (!cms_valid_slug($slug)) continue;
        list($meta, $body) = cms_parse_document(file_get_contents($file));
        $it = cms_normalise_item($type, $slug, $meta, $body, filemtime($file));
        if ($it['draft'] && !$withDrafts) continue;
        // Scheduled posts: future dates stay hidden until their day.
        if (!$withDrafts && $it['date'] > time()) continue;
        $items[] = $it;
    }
    usort($items, 'cms_cmp_date_desc');
    return $items;
}

function cms_cmp_date_desc($a, $b)
{
    if ($a['date'] === $b['date']) return strcmp($a['slug'], $b['slug']);
    return $a['date'] < $b['date'] ? 1 : -1;
}

function cms_save_item($type, $slug, array $meta, $body)
{
    $file = cms_item_file($type, $slug);
    if (is_file($file)) cms_backup($file, $type . '/' . $slug);
    return cms_write_file($file, cms_build_document($meta, $body));
}

// Keep the previous version of every file we overwrite or delete.
function cms_backup($file, $label)
{
    $dest = cms_data_path('history/' . str_replace('/', '__', $label) . '__' . date('Ymd-His') . '_' . bin2hex(random_bytes(2)));
    $dir = dirname($dest);
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    @copy($file, $dest);
}

function cms_delete_item($type, $slug)
{
    $file = cms_item_file($type, $slug);
    if (!is_file($file)) return false;
    cms_backup($file, $type . '/' . $slug);
    return cms_move_to_trash($file, $type . '-' . $slug . '.md', array('kind' => 'item', 'path' => $type . '/' . $slug . '.md'));
}

// Moves a file or folder into the trash and remembers where it came from,
// so it can be restored from the admin panel.
// $origin: array('kind' => 'item'|'file', 'path' => relative path)
function cms_move_to_trash($path, $name, array $origin = array())
{
    $trash = cms_data_path('trash');
    if (!is_dir($trash) && !@mkdir($trash, 0775, true)) return false;
    $entry = date('Ymd-His') . '_' . bin2hex(random_bytes(3)) . '__' . $name;
    if (!@rename($path, $trash . '/' . $entry)) return false;
    $index = cms_read_json($trash . '/.index.json', array());
    $index[$entry] = $origin + array('kind' => 'unknown', 'path' => '', 'deleted' => time(), 'name' => $name);
    cms_write_json($trash . '/.index.json', $index);
    return true;
}

function cms_trash_list()
{
    $trash = cms_data_path('trash');
    $index = cms_read_json($trash . '/.index.json', array());
    $out = array();
    foreach (is_dir($trash) ? scandir($trash) : array() as $n) {
        if ($n[0] === '.') continue;
        $info = isset($index[$n]) ? $index[$n] : array('kind' => 'unknown', 'path' => '', 'name' => $n, 'deleted' => filemtime($trash . '/' . $n));
        $info['entry'] = $n;
        $info['is_dir'] = is_dir($trash . '/' . $n);
        $out[] = $info;
    }
    usort($out, function ($a, $b) { return $b['deleted'] - $a['deleted']; });
    return $out;
}

// Returns null on success or an error message.
function cms_trash_restore($entry)
{
    $trash = cms_data_path('trash');
    $index = cms_read_json($trash . '/.index.json', array());
    if (!isset($index[$entry]) || strpos($entry, '/') !== false || $entry[0] === '.' || !file_exists($trash . '/' . $entry)) {
        return 'This entry cannot be restored automatically.';
    }
    $o = $index[$entry];
    if ($o['kind'] === 'file') {
        $dest = cms_files_resolve($o['path'], false);
    } elseif ($o['kind'] === 'item') {
        $dest = cms_data_path($o['path']);
    } else {
        $dest = null;
    }
    if (!$dest) return 'The original folder no longer exists.';
    if (file_exists($dest)) return 'Something already exists at ' . $o['path'] . '.';
    if (!is_dir(dirname($dest))) @mkdir(dirname($dest), 0775, true);
    if (!@rename($trash . '/' . $entry, $dest)) return 'Could not move the file back.';
    unset($index[$entry]);
    cms_write_json($trash . '/.index.json', $index);
    return null;
}

function cms_rrmdir($path)
{
    if (is_dir($path) && !is_link($path)) {
        foreach (scandir($path) as $n) {
            if ($n !== '.' && $n !== '..') cms_rrmdir($path . '/' . $n);
        }
        return @rmdir($path);
    }
    return @unlink($path);
}

function cms_trash_empty()
{
    $trash = cms_data_path('trash');
    foreach (is_dir($trash) ? scandir($trash) : array() as $n) {
        if ($n !== '.' && $n !== '..') cms_rrmdir($trash . '/' . $n);
    }
}

// ---------------------------------------------------------------------------
// Rendering
// ---------------------------------------------------------------------------

function cms_markdown($md)
{
    static $pd = null;
    if ($pd === null) {
        $pd = new Parsedown();
        // Only the site owner writes content, so raw HTML (figures, iframes) is allowed.
        $pd->setSafeMode(false);
    }
    $html = $pd->text((string) $md);
    // Lazy-load images and give them the theme's styling.
    $html = preg_replace('/<img(?![^>]*\bloading=)/i', '<img loading="lazy"', $html);
    return cms_add_heading_ids($html);
}

function cms_add_heading_ids($html)
{
    $seen = array();
    return preg_replace_callback('/<h([2-4])>(.*?)<\/h\1>/s', function ($m) use (&$seen) {
        $id = cms_slugify(strip_tags($m[2]));
        if ($id === '') return $m[0];
        $base = $id; $n = 2;
        while (isset($seen[$id])) $id = $base . '-' . $n++;
        $seen[$id] = true;
        return '<h' . $m[1] . ' id="' . $id . '">' . $m[2] . '</h' . $m[1] . '>';
    }, $html);
}

function cms_reading_time($md)
{
    $words = str_word_count(strip_tags((string) $md));
    return max(1, (int) ceil($words / 200));
}

function cms_excerpt($item, $len = 180)
{
    if ($item['description'] !== '') return $item['description'];
    $text = trim(preg_replace('/\s+/', ' ', strip_tags(cms_markdown($item['body']))));
    if (function_exists('mb_strlen') ? mb_strlen($text) <= $len : strlen($text) <= $len) return $text;
    $cut = function_exists('mb_substr') ? mb_substr($text, 0, $len) : substr($text, 0, $len);
    return preg_replace('/\s+\S*$/', '', $cut) . '…';
}

function cms_post_url($slug)
{
    return cms_url('blog/?p=' . rawurlencode($slug));
}

function cms_news_url($slug)
{
    return cms_url('news/?n=' . rawurlencode($slug));
}

// Resolve site-relative asset paths in content (e.g. "assets/img/x.jpg").
function cms_asset_url($path)
{
    if ($path === '' || preg_match('#^(https?:)?//#', $path) || $path[0] === '/') return $path;
    return cms_url($path);
}
