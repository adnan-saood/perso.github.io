<?php
// Public endpoints used by static pages:
//   GET  api.php?fragment=news&limit=5   -> HTML rows for the homepage news table
//   GET  api.php?fragment=posts&limit=3  -> HTML rows for "latest posts"

require_once __DIR__ . '/view.php';

header('X-Content-Type-Options: nosniff');

$fragment = isset($_GET['fragment']) ? $_GET['fragment'] : '';

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

