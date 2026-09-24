<?php
// Public, server-rendered views embedded in Jekyll-built pages.
// The Jekyll page (e.g. _pages/blog.php) calls cms_render_view() before any
// output, then prints $cms['html'] where the content goes.

require_once __DIR__ . '/boot.php';

function cms_render_view($view)
{
    $fn = 'cms_view_' . $view;
    $res = function_exists($fn) ? $fn() : null;
    if (!$res) $res = cms_view_not_found();
    if (!empty($res['status'])) http_response_code($res['status']);
    header('Cache-Control: public, max-age=60');
    return $res + array('title' => '', 'description' => '', 'html' => '');
}

function cms_view_not_found()
{
    return array(
        'status' => 404,
        'title' => 'Not found',
        'html' => '<div class="post"><h1 class="post-title">Not found</h1><p>That page does not exist (anymore).</p>'
            . '<p><a href="' . e(cms_url('blog/')) . '">Back to the blog</a></p></div>',
    );
}

function cms_tag_links($item)
{
    $parts = array('<a href="' . e(cms_url('blog/?year=' . date('Y', $item['date']))) . '"><i class="fa-solid fa-calendar fa-sm"></i> ' . date('Y', $item['date']) . '</a>');
    $tags = array();
    foreach ($item['tags'] as $t) {
        $tags[] = '<a href="' . e(cms_url('blog/?tag=' . rawurlencode($t))) . '"><i class="fa-solid fa-hashtag fa-sm"></i> ' . e($t) . '</a>';
    }
    if ($tags) $parts[] = implode(' &nbsp; ', $tags);
    $cats = array();
    foreach ($item['categories'] as $c) {
        $cats[] = '<a href="' . e(cms_url('blog/?category=' . rawurlencode($c))) . '"><i class="fa-solid fa-tag fa-sm"></i> ' . e($c) . '</a>';
    }
    if ($cats) $parts[] = implode(' &nbsp; ', $cats);
    return implode(' &nbsp; &middot; &nbsp; ', $parts);
}

// ---------------------------------------------------------------------------
// Blog
// ---------------------------------------------------------------------------

function cms_view_blog()
{
    if (isset($_GET['p'])) return cms_view_post((string) $_GET['p']);

    $posts = cms_list_items('posts');
    $filter = '';
    foreach (array('tag' => 'tags', 'category' => 'categories') as $param => $field) {
        if (isset($_GET[$param]) && is_string($_GET[$param]) && $_GET[$param] !== '') {
            $want = strtolower($_GET[$param]);
            $posts = array_values(array_filter($posts, function ($p) use ($field, $want) {
                return in_array($want, array_map('strtolower', $p[$field]), true);
            }));
            $filter = ($param === 'tag' ? '#' : '') . $_GET[$param];
        }
    }
    if (isset($_GET['year']) && preg_match('/^\d{4}$/', (string) $_GET['year'])) {
        $y = $_GET['year'];
        $posts = array_values(array_filter($posts, function ($p) use ($y) { return date('Y', $p['date']) === $y; }));
        $filter = $y;
    }

    $per = (int) cms_config('posts_per_page');
    $pages = max(1, (int) ceil(count($posts) / $per));
    $page = isset($_GET['page']) ? max(1, min($pages, (int) $_GET['page'])) : 1;
    $slice = array_slice($posts, ($page - 1) * $per, $per);

    ob_start();
    echo '<div class="post cms-blog">';
    echo '<header class="cms-page-header reveal"><h1 class="post-title">' . ($filter !== '' ? 'Posts: ' . e($filter) : 'Blog') . '</h1>';
    echo '<p class="post-description">Research notes, conference recaps and news from the lab.</p>';
    if ($filter !== '') echo '<p><a href="' . e(cms_url('blog/')) . '">&larr; All posts</a></p>';
    echo '</header>';

    // Featured posts as cards, only on the unfiltered first page.
    if ($filter === '' && $page === 1) {
        $featured = array_filter($posts, function ($p) { return $p['featured']; });
        if ($featured) {
            echo '<div class="cms-featured">';
            foreach ($featured as $p) {
                echo '<a class="cms-card reveal" href="' . e(cms_post_url($p['slug'])) . '">'
                    . ($p['thumbnail'] !== '' ? '<img src="' . e(cms_asset_url($p['thumbnail'])) . '" alt="" loading="lazy">' : '')
                    . '<span class="cms-card-body"><span class="cms-pin"><i class="fa-solid fa-thumbtack fa-xs"></i> Featured</span>'
                    . '<span class="cms-card-title">' . e($p['title']) . '</span>'
                    . '<span class="cms-card-text">' . e(cms_excerpt($p, 140)) . '</span></span></a>';
            }
            echo '</div>';
        }
    }

    if (!$slice) echo '<p>No posts yet.</p>';
    echo '<ul class="post-list">';
    foreach ($slice as $p) {
        echo '<li class="reveal">';
        if ($p['thumbnail'] !== '') echo '<div class="row"><div class="col-sm-9">';
        echo '<h3><a class="post-title" href="' . e(cms_post_url($p['slug'])) . '">' . e($p['title']) . '</a></h3>';
        echo '<p>' . e(cms_excerpt($p)) . '</p>';
        echo '<p class="post-meta">' . cms_reading_time($p['body']) . ' min read &nbsp; &middot; &nbsp; ' . date('F j, Y', $p['date']) . '</p>';
        echo '<p class="post-tags">' . cms_tag_links($p) . '</p>';
        if ($p['thumbnail'] !== '') {
            echo '</div><div class="col-sm-3"><img class="card-img" src="' . e(cms_asset_url($p['thumbnail'])) . '" style="object-fit:cover;height:90%" alt="" loading="lazy"></div></div>';
        }
        echo '</li>';
    }
    echo '</ul>';

    if ($pages > 1) {
        $q = $_GET;
        echo '<nav aria-label="Blog pages"><ul class="pagination pagination-lg justify-content-center">';
        for ($i = 1; $i <= $pages; $i++) {
            $q['page'] = $i;
            echo '<li class="page-item' . ($i === $page ? ' active' : '') . '"><a class="page-link" href="?' . e(http_build_query($q)) . '">' . $i . '</a></li>';
        }
        echo '</ul></nav>';
    }
    echo '</div>';

    return array('title' => $filter !== '' ? 'Blog: ' . $filter : 'Blog', 'html' => ob_get_clean());
}

function cms_view_post($slug)
{
    $post = cms_load_item('posts', $slug);
    if (!$post || $post['draft'] || $post['date'] > time()) return null;

    $all = cms_list_items('posts');
    $prev = $next = null;
    foreach ($all as $i => $p) {
        if ($p['slug'] !== $slug) continue;
        if (isset($all[$i + 1])) $prev = $all[$i + 1];
        if ($i > 0) $next = $all[$i - 1];
    }

    ob_start();
    echo '<div class="post cms-post">';
    echo '<header class="post-header reveal"><h1 class="post-title">' . e($post['title']) . '</h1>';
    echo '<p class="post-meta">' . date('F j, Y', $post['date']) . ' &nbsp;&middot;&nbsp; ' . cms_reading_time($post['body']) . ' min read</p>';
    echo '<p class="post-tags">' . cms_tag_links($post) . '</p></header>';
    echo '<article class="post-content"><div id="markdown-content">' . cms_markdown($post['body']) . '</div></article>';

    if ($prev || $next) {
        echo '<nav class="cms-prev-next">';
        echo $prev ? '<a class="reveal" href="' . e(cms_post_url($prev['slug'])) . '"><small>&larr; Previous</small><span>' . e($prev['title']) . '</span></a>' : '<span></span>';
        echo $next ? '<a class="reveal cms-next" href="' . e(cms_post_url($next['slug'])) . '"><small>Next &rarr;</small><span>' . e($next['title']) . '</span></a>' : '<span></span>';
        echo '</nav>';
    }
    echo '</div>';

    return array('title' => $post['title'], 'description' => cms_excerpt($post, 160), 'html' => ob_get_clean());
}

// ---------------------------------------------------------------------------
// News
// ---------------------------------------------------------------------------

function cms_news_rows(array $items)
{
    $out = '';
    foreach ($items as $n) {
        $content = $n['inline'] || $n['title'] === ''
            ? preg_replace('#^<p>(.*)</p>$#s', '$1', trim(cms_markdown($n['body'])))
            : '<a class="news-title" href="' . e(cms_news_url($n['slug'])) . '">' . e($n['title']) . '</a>';
        $out .= '<tr class="reveal"><th scope="row" style="width: 20%">' . date('M j, Y', $n['date']) . '</th><td>' . $content . '</td></tr>';
    }
    return $out;
}

function cms_view_news()
{
    if (isset($_GET['n'])) return cms_view_news_item((string) $_GET['n']);
    $items = cms_list_items('news');
    $html = '<div class="post"><header class="cms-page-header reveal"><h1 class="post-title">News</h1>'
        . '<p class="post-description">Papers, awards, talks and other updates.</p></header></div>';
    $html .= '<div class="news">';
    $html .= $items
        ? '<div class="table-responsive"><table class="table table-sm table-borderless">' . cms_news_rows($items) . '</table></div>'
        : '<p>No news so far...</p>';
    $html .= '</div>';
    return array('title' => 'News', 'html' => $html);
}

function cms_view_news_item($slug)
{
    $n = cms_load_item('news', $slug);
    if (!$n || $n['draft'] || $n['date'] > time()) return null;
    $html = '<div class="post"><header class="post-header reveal"><h1 class="post-title">' . e($n['title'] ?: 'News') . '</h1>'
        . '<p class="post-meta">' . date('F j, Y', $n['date']) . '</p></header>'
        . '<article class="post-content">' . cms_markdown($n['body']) . '</article>'
        . '<p><a href="' . e(cms_url('news/')) . '">&larr; All news</a></p></div>';
    return array('title' => $n['title'] ?: 'News', 'description' => cms_excerpt($n, 160), 'html' => $html);
}
