<?php
// Public endpoints used by static pages:
//   GET  api.php?fragment=news&limit=5   -> HTML rows for the homepage news table
//   GET  api.php?fragment=posts&limit=3  -> HTML rows for "latest posts"
//   POST api.php?action=contact          -> contact form submission (JSON reply)

require_once __DIR__ . '/view.php';

header('X-Content-Type-Options: nosniff');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$fragment = isset($_GET['fragment']) ? $_GET['fragment'] : '';

if ($action === 'contact') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
    list($ok, $err) = cms_receive_message($_POST);
    $wantsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        if (!$ok) http_response_code(422);
        echo json_encode(array('ok' => $ok, 'error' => $err));
    } else {
        // No-JS fallback: bounce back to the contact page with a status flag.
        header('Location: ' . cms_url('contact/') . '?sent=' . ($ok ? '1' : '0'), true, 303);
    }
    exit;
}

$limit = isset($_GET['limit']) ? max(1, min(50, (int) $_GET['limit'])) : 5;
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: public, max-age=60');

if ($fragment === 'news') {
    $items = array_slice(cms_list_items('news'), 0, $limit);
    echo $items ? cms_news_rows($items) : '<tr><td>No news so far...</td></tr>';
} elseif ($fragment === 'posts') {
    $items = array_slice(cms_list_items('posts'), 0, $limit);
    foreach ($items as $p) {
        echo '<tr><th scope="row" style="width: 20%">' . date('M j, Y', $p['date']) . '</th>'
            . '<td><a class="news-title" href="' . e(cms_post_url($p['slug'])) . '">' . e($p['title']) . '</a></td></tr>';
    }
    if (!$items) echo '<tr><td>No posts so far...</td></tr>';
} else {
    http_response_code(404);
}

