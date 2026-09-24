<?php
// Public endpoints used by static pages:
//   GET  api.php?fragment=news&limit=5      -> HTML rows for the homepage news table
//   GET  api.php?fragment=posts&limit=3     -> HTML rows for "latest posts"
//   GET  api.php?fragment=projects&limit=4  -> project cards (featured first)
// Every fragment starts with <!--cms--> so cms.js can tell it apart from raw PHP
// source (which is what a server without PHP would send back).

require_once __DIR__ . '/view.php';

header('X-Content-Type-Options: nosniff');

$fragment = isset($_GET['fragment']) ? $_GET['fragment'] : '';

$limit = isset($_GET['limit']) ? max(1, min(50, (int) $_GET['limit'])) : 5;
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: public, max-age=60');

if ($fragment === 'news') {
    $items = array_slice(cms_list_items('news'), 0, $limit);
    echo '<!--cms-->', $items ? cms_news_rows($items) : '<tr><td>No news so far...</td></tr>';
} elseif ($fragment === 'posts') {
    $items = array_slice(cms_list_items('posts'), 0, $limit);
    echo '<!--cms-->';
    foreach ($items as $p) {
        echo '<tr><th scope="row">' . date('M j, Y', $p['date']) . '</th>'
            . '<td><a class="news-title" href="' . e(cms_post_url($p['slug'])) . '">' . e($p['title']) . '</a></td></tr>';
    }
    if (!$items) echo '<tr><td>No posts so far...</td></tr>';
} elseif ($fragment === 'projects') {
    $items = cms_list_items('projects');
    $featured = array_values(array_filter($items, function ($p) { return $p['featured']; }));
    $others = array_values(array_filter($items, function ($p) { return !$p['featured']; }));
    echo '<!--cms-->', cms_project_cards(array_slice(array_merge($featured, $others), 0, $limit), true);
} else {
    http_response_code(404);
}
