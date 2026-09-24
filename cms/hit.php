<?php
// Analytics beacon: pages send {u: url, r: referrer, w: width, l: language, z: timezone}
// on load and {e: "leave", u, s: seconds} when the visitor leaves. See lib/stats.php.
require_once __DIR__ . '/boot.php';

header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$raw = file_get_contents('php://input', false, null, 0, 4096);
$in = json_decode((string) $raw, true);
if (is_array($in)) cms_stats_record($in);
http_response_code(204);
