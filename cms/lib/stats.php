<?php
// Privacy-friendly visitor analytics, stored on your own server.
// - No cookies, nothing stored in the visitor's browser, no IP addresses kept.
// - A visitor is a hash of (daily random salt + IP + browser). The salt changes every
//   day and old salts are deleted, so hashes can't be linked across days or reversed.
// - One file per day: cms-data/stats/YYYY-MM-DD.ndjson (one JSON event per line).

if (!defined('CMS_BOOT')) { http_response_code(404); exit; }

const CMS_STATS_KEEP_DAYS = 400;

function cms_stats_dir()
{
    return cms_data_path('stats');
}

function cms_stats_is_bot($ua)
{
    return $ua === '' || preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|headless|lighthouse|pingdom|monitor|curl|wget|python|java\//i', $ua) === 1;
}

function cms_stats_salt($day)
{
    $dir = cms_stats_dir();
    $file = $dir . '/.salt-' . $day;
    if (!is_file($file)) {
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents($file, bin2hex(random_bytes(16)), LOCK_EX);
        @chmod($file, 0600);
        foreach (glob($dir . '/.salt-*') ?: array() as $old) {
            if ($old !== $file) @unlink($old); // yesterday's visitors can no longer be re-identified
        }
    }
    return (string) @file_get_contents($file);
}

function cms_stats_browser($ua)
{
    $b = 'Other';
    foreach (array('Edg/' => 'Edge', 'OPR/' => 'Opera', 'SamsungBrowser' => 'Samsung Internet', 'Firefox/' => 'Firefox',
                   'Chrome/' => 'Chrome', 'Safari/' => 'Safari') as $needle => $name) {
        if (strpos($ua, $needle) !== false) { $b = $name; break; }
    }
    $o = 'Other';
    foreach (array('iPhone' => 'iOS', 'iPad' => 'iOS', 'Android' => 'Android', 'Windows' => 'Windows',
                   'Mac OS X' => 'macOS', 'CrOS' => 'ChromeOS', 'Linux' => 'Linux') as $needle => $name) {
        if (strpos($ua, $needle) !== false) { $o = $name; break; }
    }
    return array($b, $o);
}

// Keep the path plus the query parameters that identify content (?p=, ?n=, ...).
function cms_stats_clean_path($url)
{
    $parts = parse_url((string) $url);
    $path = isset($parts['path']) ? $parts['path'] : '/';
    $keep = array();
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $q);
        foreach (array('p', 'n', 'tag', 'category', 'year') as $k) {
            if (isset($q[$k]) && is_string($q[$k])) $keep[$k] = substr($q[$k], 0, 100);
        }
    }
    $path = substr(preg_replace('#[^A-Za-z0-9/_.~%-]#', '', $path), 0, 200);
    return $path . ($keep ? '?' . http_build_query($keep) : '');
}

// Records one event sent by the browser beacon. Returns true if stored.
function cms_stats_record(array $in)
{
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 400) : '';
    if (cms_stats_is_bot($ua)) return false;
    $event = isset($in['e']) && $in['e'] === 'leave' ? 'leave' : 'view';
    $day = date('Y-m-d');
    $row = array(
        't' => time(),
        'e' => $event,
        'p' => cms_stats_clean_path(isset($in['u']) ? $in['u'] : '/'),
        'v' => substr(hash('sha256', cms_stats_salt($day) . cms_client_ip() . $ua), 0, 16),
    );
    if ($event === 'leave') {
        $row['s'] = max(0, min(3600, (int) (isset($in['s']) ? $in['s'] : 0)));
    } else {
        $ref = '';
        $page = parse_url(isset($in['u']) ? (string) $in['u'] : '');
        if (!empty($page['query'])) {
            parse_str($page['query'], $q);
            if (!empty($q['utm_source']) && is_string($q['utm_source'])) $ref = substr(strtolower($q['utm_source']), 0, 60);
        }
        if ($ref === '' && !empty($in['r'])) {
            $host = strtolower((string) parse_url((string) $in['r'], PHP_URL_HOST));
            $own = strtolower(isset($_SERVER['HTTP_HOST']) ? preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']) : '');
            if ($host !== '' && $host !== $own) $ref = preg_replace('/^www\./', '', substr($host, 0, 80));
        }
        list($browser, $os) = cms_stats_browser($ua);
        $w = isset($in['w']) ? (int) $in['w'] : 0;
        $row += array(
            'r' => $ref,
            'd' => $w && $w < 700 ? 'Mobile' : ($w && $w < 1100 ? 'Tablet' : 'Desktop'),
            'b' => $browser,
            'o' => $os,
            'l' => isset($in['l']) ? strtolower(substr(preg_replace('/[^A-Za-z-]/', '', (string) $in['l']), 0, 2)) : '',
            'z' => isset($in['z']) ? substr(preg_replace('#[^A-Za-z_/+-]#', '', (string) $in['z']), 0, 40) : '',
        );
    }
    $dir = cms_stats_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) return false;
    $ok = @file_put_contents($dir . '/' . $day . '.ndjson', json_encode($row, JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND | LOCK_EX) !== false;
    if (mt_rand(1, 200) === 1) cms_stats_purge();
    return $ok;
}

function cms_stats_purge()
{
    $limit = date('Y-m-d', strtotime('-' . CMS_STATS_KEEP_DAYS . ' days'));
    foreach (glob(cms_stats_dir() . '/*.ndjson') ?: array() as $f) {
        if (basename($f, '.ndjson') < $limit) @unlink($f);
    }
}

// Aggregates the last $days days (today included) for the admin dashboard.
function cms_stats_summary($days)
{
    $days = max(1, min(365, (int) $days));
    $s = array(
        'days' => array(), 'visitors' => 0, 'views' => 0, 'bounces' => 0, 'time' => 0, 'timeCount' => 0, 'live' => 0,
        'pages' => array(), 'referrers' => array(), 'devices' => array(), 'browsers' => array(), 'os' => array(),
        'langs' => array(), 'zones' => array(),
    );
    $now = time();
    $live = array();
    for ($i = $days - 1; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-$i days"));
        $file = cms_stats_dir() . '/' . $day . '.ndjson';
        $views = 0;
        $perVisitor = array();
        $seenAttr = array();
        if (is_file($file)) {
            $fh = fopen($file, 'r');
            while (($line = fgets($fh)) !== false) {
                $r = json_decode($line, true);
                if (!$r) continue;
                if ($now - $r['t'] < 300) $live[$r['v']] = true;
                if ($r['e'] === 'leave') {
                    if (!empty($r['s']) && $r['s'] >= 1) {
                        $s['time'] += $r['s'];
                        $s['timeCount']++;
                        $p = &$s['pages'][$r['p']];
                        if (!$p) $p = array('views' => 0, 'visitors' => array(), 'time' => 0, 'timeCount' => 0);
                        $p['time'] += $r['s'];
                        $p['timeCount']++;
                        unset($p);
                    }
                    continue;
                }
                $views++;
                $perVisitor[$r['v']] = (isset($perVisitor[$r['v']]) ? $perVisitor[$r['v']] : 0) + 1;
                $p = &$s['pages'][$r['p']];
                if (!$p) $p = array('views' => 0, 'visitors' => array(), 'time' => 0, 'timeCount' => 0);
                $p['views']++;
                $p['visitors'][$day . $r['v']] = true;
                unset($p);
                // Attributes are counted once per visitor per day.
                if (isset($seenAttr[$r['v']])) continue;
                $seenAttr[$r['v']] = true;
                if (!empty($r['r'])) $s['referrers'][$r['r']] = (isset($s['referrers'][$r['r']]) ? $s['referrers'][$r['r']] : 0) + 1;
                foreach (array('d' => 'devices', 'b' => 'browsers', 'o' => 'os', 'l' => 'langs', 'z' => 'zones') as $k => $bucket) {
                    if (!empty($r[$k])) $s[$bucket][$r[$k]] = (isset($s[$bucket][$r[$k]]) ? $s[$bucket][$r[$k]] : 0) + 1;
                }
            }
            fclose($fh);
        }
        $visitors = count($perVisitor);
        foreach ($perVisitor as $n) if ($n === 1) $s['bounces']++;
        $s['days'][] = array('day' => $day, 'visitors' => $visitors, 'views' => $views);
        $s['visitors'] += $visitors;
        $s['views'] += $views;
    }
    $s['live'] = count($live);
    foreach ($s['pages'] as $path => $p) {
        $s['pages'][$path] = array('views' => $p['views'], 'visitors' => count($p['visitors']),
                                   'time' => $p['timeCount'] ? round($p['time'] / $p['timeCount']) : 0);
    }
    uasort($s['pages'], function ($a, $b) { return $b['views'] - $a['views']; });
    foreach (array('referrers', 'devices', 'browsers', 'os', 'langs', 'zones') as $k) arsort($s[$k]);
    return $s;
}
