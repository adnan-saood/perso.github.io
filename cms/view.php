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
        'title' => cms_t('Not found'),
        'html' => '<div class="post"><h1 class="post-title">' . e(cms_t('Not found')) . '</h1><p>' . e(cms_t('That page does not exist (anymore).')) . '</p>'
            . '<p><a href="' . e(cms_url('blog/')) . '">' . e(cms_t('Back to the blog')) . '</a></p></div>',
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
    echo '<header class="cms-page-header"><p class="kicker reveal">' . e(cms_t('Blog')) . '</p>';
    echo '<h1 class="post-title split">' . ($filter !== '' ? e(cms_t('Posts')) . ': ' . e($filter) : cms_t('Notes from the lab.')) . '</h1>';
    echo '<p class="post-description reveal">' . e(cms_t('Research notes, conference recaps and the stories behind the papers.')) . '</p>';
    if ($filter !== '') echo '<p><a href="' . e(cms_url('blog/')) . '">&larr; ' . e(cms_t('All posts')) . '</a></p>';
    echo '</header>';

    // Featured posts as cards, only on the unfiltered first page.
    if ($filter === '' && $page === 1) {
        $featured = array_filter($posts, function ($p) { return $p['featured']; });
        if ($featured) {
            echo '<div class="cms-featured">';
            foreach ($featured as $p) {
                echo '<a class="cms-card reveal" href="' . e(cms_post_url($p['slug'])) . '">'
                    . ($p['thumbnail'] !== '' ? '<img src="' . e(cms_asset_url($p['thumbnail'])) . '" alt="" loading="lazy">' : '')
                    . '<span class="cms-card-body"><span class="cms-pin"><i class="fa-solid fa-thumbtack fa-xs"></i> ' . e(cms_t('Featured')) . '</span>'
                    . '<span class="cms-card-title">' . e($p['title']) . '</span>'
                    . '<span class="cms-card-text">' . e(cms_excerpt($p, 140)) . '</span></span></a>';
            }
            echo '</div>';
        }
    }

    if (!$slice) echo '<p>' . e(cms_t('No posts yet.')) . '</p>';
    echo '<ul class="post-list">';
    foreach ($slice as $p) {
        echo '<li class="reveal">';
        if ($p['thumbnail'] !== '') echo '<div class="row"><div class="col-sm-9">';
        echo '<h3><a class="post-title" href="' . e(cms_post_url($p['slug'])) . '">' . e($p['title']) . '</a></h3>';
        echo '<p>' . e(cms_excerpt($p)) . '</p>';
        echo '<p class="post-meta">' . cms_reading_time($p['body']) . ' ' . e(cms_t('min read')) . ' &nbsp; &middot; &nbsp; ' . cms_date('F j, Y', $p['date']) . '</p>';
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

    return array('title' => $filter !== '' ? cms_t('Blog') . ': ' . $filter : cms_t('Blog'), 'html' => ob_get_clean());
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
    echo '<a class="cms-back reveal" href="' . e(cms_url('blog/')) . '"><i class="ti ti-arrow-left"></i> ' . e(cms_t('All posts')) . '</a>';
    echo '<header class="post-header"><h1 class="post-title split">' . e($post['title']) . '</h1>';
    echo '<p class="post-meta">' . cms_date('F j, Y', $post['date']) . ' &nbsp;&middot;&nbsp; ' . cms_reading_time($post['body']) . ' ' . e(cms_t('min read')) . '</p>';
    echo '<p class="post-tags">' . cms_tag_links($post) . '</p></header>';
    echo '<article class="post-content"><div id="markdown-content">' . cms_markdown($post['body']) . '</div></article>';

    if ($prev || $next) {
        echo '<nav class="cms-prev-next">';
        echo $prev ? '<a class="reveal" href="' . e(cms_post_url($prev['slug'])) . '"><small>&larr; ' . e(cms_t('Previous')) . '</small><span>' . e($prev['title']) . '</span></a>' : '<span></span>';
        echo $next ? '<a class="reveal cms-next" href="' . e(cms_post_url($next['slug'])) . '"><small>' . e(cms_t('Next')) . ' &rarr;</small><span>' . e($next['title']) . '</span></a>' : '<span></span>';
        echo '</nav>';
    }
    echo '</div>';

    return array('title' => $post['title'], 'description' => cms_excerpt($post, 160), 'html' => ob_get_clean(),
                 'image' => $post['og_image'] !== '' ? $post['og_image'] : $post['thumbnail'], 'article' => true);
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
        $out .= '<tr class="reveal"><th scope="row" style="width: 20%">' . cms_date('M j, Y', $n['date']) . '</th><td>' . $content . '</td></tr>';
    }
    return $out;
}

function cms_view_news()
{
    if (isset($_GET['n'])) return cms_view_news_item((string) $_GET['n']);
    $items = cms_list_items('news');
    $html = '<div class="post"><header class="cms-page-header"><p class="kicker reveal">' . e(cms_t('News')) . '</p><h1 class="post-title split">' . e(cms_t('What’s new.')) . '</h1>'
        . '<p class="post-description reveal">' . e(cms_t('Papers, awards, talks and other updates.')) . '</p></header></div>';
    $html .= '<div class="news">';
    $html .= $items
        ? '<div class="table-responsive"><table class="table table-sm table-borderless">' . cms_news_rows($items) . '</table></div>'
        : '<p>' . e(cms_t('No news so far.')) . '</p>';
    $html .= '</div>';
    return array('title' => cms_t('News'), 'html' => $html);
}

function cms_view_news_item($slug)
{
    $n = cms_load_item('news', $slug);
    if (!$n || $n['draft'] || $n['date'] > time()) return null;
    $html = '<div class="post"><a class="cms-back reveal" href="' . e(cms_url('news/')) . '"><i class="ti ti-arrow-left"></i> ' . e(cms_t('All news')) . '</a>'
        . '<header class="post-header"><h1 class="post-title split">' . e($n['title'] ?: cms_t('News')) . '</h1>'
        . '<p class="post-meta">' . cms_date('F j, Y', $n['date']) . '</p></header>'
        . '<article class="post-content">' . cms_markdown($n['body']) . '</article>'
        . '</div>';
    return array('title' => $n['title'] ?: cms_t('News'), 'description' => cms_excerpt($n, 160), 'html' => $html, 'image' => $n['og_image'], 'article' => true);
}

// ---------------------------------------------------------------------------
// Projects
// ---------------------------------------------------------------------------

// Card markup shared by the projects page and the homepage fragment.
function cms_project_cards(array $items, $featureFirst = false)
{
    $out = '';
    foreach ($items as $i => $p) {
        $cls = 'project-card' . ($featureFirst && $i === 0 ? ' is-feature' : '');
        $out .= '<a class="' . $cls . '" href="' . e(cms_project_url($p['slug'])) . '" data-category="' . e($p['category']) . '">'
            . '<span class="project-card__media">'
            . ($p['category'] !== '' ? '<span class="chip project-card__cat">' . e($p['category']) . '</span>' : '')
            . ($p['model'] !== '' ? '<span class="chip project-card__3d"><i class="ti ti-3d-cube-sphere"></i> 3D</span>' : '')
            . ($p['img'] !== '' ? '<img src="' . e(cms_asset_url($p['img'])) . '" alt="" loading="lazy">' : '')
            . '</span>'
            . '<span class="project-card__body">'
            . '<span class="project-card__title">' . e($p['title']) . '</span>'
            . '<span class="project-card__text">' . e(cms_excerpt($p, 170)) . '</span>'
            . '<span class="project-card__foot"><span>'
            . ($p['github'] !== '' ? '<i class="ti ti-brand-github"></i> open source' : 'read more')
            . '</span><span class="go" aria-hidden="true"><i class="ti ti-arrow-right"></i></span></span>'
            . '</span></a>';
    }
    return $out;
}

function cms_view_projects()
{
    if (isset($_GET['p'])) return cms_view_project((string) $_GET['p']);
    $items = cms_list_items('projects');
    $cats = array();
    foreach ($items as $p) {
        if ($p['category'] !== '') $cats[$p['category']] = true;
    }

    $html = '<header class="cms-page-header"><p class="kicker reveal">' . e(cms_t('Projects')) . '</p>'
        . '<h1 class="post-title split">' . e(cms_t('Research and engineering, from silicone to software.')) . '</h1>'
        . '<p class="post-description reveal">' . e(cms_t('Tactile skins, haptic interfaces, medical robots, ROS 2 drivers and swarms. Pick a thread.')) . '</p></header>';
    if (count($cats) > 1) {
        $html .= '<div class="filters reveal" data-filters="#project-grid" role="group" aria-label="Filter projects">'
            . '<button type="button" class="is-active" data-filter="*">' . e(cms_t('All')) . '</button>';
        foreach (array_keys($cats) as $c) $html .= '<button type="button" data-filter="' . e($c) . '">' . e($c) . '</button>';
        $html .= '</div>';
    }
    $html .= $items
        ? '<div class="project-grid" id="project-grid">' . cms_project_cards($items, true) . '</div>'
        : '<p>' . e(cms_t('No projects yet.')) . '</p>';
    return array('title' => cms_t('Projects'), 'description' => cms_t('Research and engineering projects by') . ' ' . cms_config('site_name') . '.', 'html' => $html);
}

function cms_view_project($slug)
{
    $p = cms_load_item('projects', $slug);
    if (!$p || $p['draft']) return null;
    $all = cms_list_items('projects');
    $next = null;
    foreach ($all as $i => $it) {
        if ($it['slug'] === $slug) $next = isset($all[$i + 1]) ? $all[$i + 1] : $all[0];
    }

    $html = '<a class="cms-back reveal" href="' . e(cms_url('projects/')) . '"><i class="ti ti-arrow-left"></i> ' . e(cms_t('All projects')) . '</a>';
    $html .= '<header class="project-hero"><div>';
    if ($p['category'] !== '') $html .= '<p class="kicker reveal">' . e($p['category']) . '</p>';
    $html .= '<h1 class="post-title split">' . e($p['title']) . '</h1>';
    if ($p['description'] !== '') $html .= '<p class="post-description reveal">' . e($p['description']) . '</p>';
    $links = '';
    if ($p['github'] !== '') {
        $links .= '<a class="btn btn--primary magnetic" href="' . e($p['github']) . '" rel="noopener"><i class="ti ti-brand-github"></i> Code on GitHub</a>';
    }
    if ($p['url'] !== '') {
        $links .= '<a class="btn btn--ghost magnetic" href="' . e($p['url']) . '" rel="noopener">Project link <i class="ti ti-arrow-up-right"></i></a>';
    }
    if ($links !== '') $html .= '<div class="project-hero__links reveal">' . $links . '</div>';
    $html .= '</div>';
    if ($p['model'] !== '') {
        $html .= '<div class="project-hero__media project-hero__model reveal">' . cms_model_viewer($p)
            . '<p class="robots__hint"><i class="ti ti-hand-move"></i> ' . e(cms_t('Drag to rotate · scroll to zoom')) . '</p></div>';
    } elseif ($p['img'] !== '') {
        $html .= '<div class="project-hero__media tilt reveal"><img src="' . e(cms_asset_url($p['img'])) . '" alt=""></div>';
    }
    $html .= '</header>';
    $html .= '<article class="cms-project-body post-content">' . cms_markdown($p['body']) . '</article>';
    if ($next && $next['slug'] !== $slug) {
        $html .= '<nav class="cms-prev-next"><span></span><a class="cms-next" href="' . e(cms_project_url($next['slug'])) . '">'
            . '<small>' . e(cms_t('Next project')) . ' &rarr;</small><span>' . e($next['title']) . '</span></a></nav>';
    }
    return array('title' => $p['title'], 'description' => cms_excerpt($p, 160), 'html' => $html,
                 'image' => $p['og_image'] !== '' ? $p['og_image'] : $p['img']);
}

// ---------------------------------------------------------------------------
// Publications
// ---------------------------------------------------------------------------

function cms_pub_types()
{
    // Labels follow the visitor's language (the admin always runs in English).
    return array_map('cms_t', array('journal' => 'Journal', 'conference' => 'Conference', 'workshop' => 'Workshop', 'patent' => 'Patent',
                 'thesis' => 'Thesis', 'preprint' => 'Preprint', 'talk' => 'Talk', 'other' => 'Other'));
}

// Authors with the site owner highlighted.
function cms_pub_authors(array $authors)
{
    $parts = explode(' ', cms_config('site_name'));
    $me = strtolower(end($parts));
    $out = array();
    foreach ($authors as $a) {
        $out[] = stripos($a, $me) !== false ? '<b class="me">' . e($a) . '</b>' : e($a);
    }
    return implode(', ', $out);
}

function cms_publication_row(array $p, $compact = false)
{
    $types = cms_pub_types();
    $link = $p['url'] !== '' ? $p['url'] : ($p['doi'] !== '' ? 'https://doi.org/' . $p['doi'] : '');
    $search = strtolower($p['title'] . ' ' . implode(' ', $p['authors']) . ' ' . $p['venue'] . ' ' . $p['badge'] . ' ' . $p['year']);
    $h = '<li class="pub reveal" id="' . e($p['slug']) . '" data-category="' . e($p['pubtype']) . '" data-search="' . e($search) . '">';
    $h .= '<div class="pub__media">';
    if ($p['image'] !== '') {
        $h .= '<img src="' . e(cms_asset_url($p['image'])) . '" alt="" loading="lazy">';
    } else {
        $label = $p['badge'] !== '' ? $p['badge'] : (isset($types[$p['pubtype']]) ? $types[$p['pubtype']] : '');
        $h .= '<span class="pub__tile"><span>' . e($label) . '</span><small>' . e($p['year']) . '</small></span>';
    }
    $h .= '</div><div class="pub__body"><div class="pub__meta">';
    if ($p['badge'] !== '') $h .= '<span class="chip chip--badge">' . e($p['badge']) . '</span>';
    $typeLabel = isset($types[$p['pubtype']]) ? $types[$p['pubtype']] : cms_t('Other');
    // No "Patent · Patent": skip the type when the badge already says it.
    if (strcasecmp($typeLabel, $p['badge']) !== 0) $h .= '<span class="chip">' . e($typeLabel) . '</span>';
    if ($p['award'] !== '') $h .= '<span class="chip chip--award"><i class="ti ti-trophy"></i> ' . e($p['award']) . '</span>';
    $h .= '</div>';
    $title = e($p['title']);
    $h .= '<h3 class="pub__title">' . ($link !== '' ? '<a href="' . e($link) . '" rel="noopener">' . $title . '</a>' : $title) . '</h3>';
    $h .= '<p class="pub__authors">' . cms_pub_authors($p['authors']) . '</p>';
    $venue = array();
    if ($p['venue'] !== '') $venue[] = '<em>' . e($p['venue']) . '</em>';
    if ($p['year'] !== '') $venue[] = e($p['year']);
    if ($p['note'] !== '' && !$compact) $venue[] = e($p['note']);
    if ($venue) $h .= '<p class="pub__venue">' . implode(' · ', $venue) . '</p>';
    if (!$compact) {
        $links = array();
        $kinds = array('pdf' => array('PDF', 'ti-file-type-pdf'), 'code' => array('Code', 'ti-brand-github'),
                       'video' => array('Video', 'ti-player-play'), 'slides' => array('Slides', 'ti-presentation'));
        foreach ($kinds as $k => $l) {
            if ($p[$k] !== '') $links[] = '<a class="pub__link" href="' . e(cms_asset_url($p[$k])) . '" rel="noopener"><i class="ti ' . $l[1] . '"></i> ' . e(cms_t($l[0])) . '</a>';
        }
        if ($p['doi'] !== '') $links[] = '<a class="pub__link" href="https://doi.org/' . e($p['doi']) . '" rel="noopener"><i class="ti ti-link"></i> DOI</a>';
        $extra = '';
        if (trim($p['body']) !== '') {
            $extra .= '<details class="pub__more"><summary><i class="ti ti-align-left"></i> ' . e(cms_t('Abstract')) . '</summary><div class="pub__abstract">' . cms_markdown($p['body']) . '</div></details>';
        }
        if ($p['bibtex'] !== '') {
            $extra .= '<details class="pub__more"><summary><i class="ti ti-quote"></i> BibTeX</summary><div class="pub__bib"><pre>' . e($p['bibtex']) . '</pre>'
                . '<button type="button" class="pub__copy" data-copy-bib>' . e(cms_t('Copy')) . '</button></div></details>';
        }
        if ($links || $extra) $h .= '<div class="pub__links">' . implode('', $links) . $extra . '</div>';
    }
    return $h . '</div></li>';
}

function cms_view_publications()
{
    $items = cms_list_items('publications');
    $types = cms_pub_types();
    $present = array();
    foreach ($items as $p) $present[$p['pubtype']] = true;

    $html = '<header class="cms-page-header"><p class="kicker reveal">' . e(cms_t('Research output')) . '</p>'
        . '<h1 class="post-title split">Publications.</h1>'
        . '<p class="post-description reveal">' . e(cms_t('Papers, patents and workshop contributions on tactile sensing, human–robot touch and medical robotics.')) . '</p></header>';
    $html .= '<div class="pub-tools reveal"><label class="pub-search"><i class="ti ti-search"></i>'
        . '<input type="search" placeholder="' . e(cms_t('Search title, author, venue…')) . '" data-pub-search aria-label="Search publications"></label>';
    if (count($present) > 1) {
        $html .= '<div class="filters" data-filters="#pub-list" role="group" aria-label="Filter by type">'
            . '<button type="button" class="is-active" data-filter="*">' . e(cms_t('All')) . ' <span>' . count($items) . '</span></button>';
        foreach ($types as $k => $label) {
            if (!isset($present[$k])) continue;
            $n = 0;
            foreach ($items as $p) if ($p['pubtype'] === $k) $n++;
            $html .= '<button type="button" data-filter="' . e($k) . '">' . e($label) . ' <span>' . $n . '</span></button>';
        }
        $html .= '</div>';
    }
    $html .= '</div><div id="pub-list">';
    $year = null;
    foreach ($items as $p) {
        if ($p['year'] !== $year) {
            if ($year !== null) $html .= '</ol></section>';
            $year = $p['year'];
            $html .= '<section class="pub-year"><h2 class="pub-year__label">' . e($year !== '' ? $year : cms_t('Undated')) . '</h2><ol class="pubs">';
        }
        $html .= cms_publication_row($p);
    }
    if ($year !== null) $html .= '</ol></section>';
    $html .= '<p class="pub-empty" hidden>' . e(cms_t('No publication matches your search.')) . '</p></div>';
    return array('title' => cms_t('Publications'), 'description' => cms_t('Publications by') . ' ' . cms_config('site_name') . '.', 'html' => $html);
}

// Selected publications for the homepage, in the chosen order.
function cms_selected_publications($limit)
{
    $items = array_values(array_filter(cms_list_items('publications'), function ($p) { return $p['selected']; }));
    usort($items, function ($a, $b) { return $a['home_order'] - $b['home_order']; });
    return array_slice($items, 0, $limit);
}

// ---------------------------------------------------------------------------
// CV
// ---------------------------------------------------------------------------

function cms_cv_get($a, $k)
{
    if (cms_lang() === 'fr' && !empty($a[$k . '_fr'])) $k .= '_fr';
    if (!isset($a[$k])) return '';
    return is_array($a[$k]) ? $a[$k] : trim((string) $a[$k]);
}

function cms_cv_range($e)
{
    $s = cms_cv_date(cms_cv_get($e, 'startDate'));
    $t = cms_cv_date(cms_cv_get($e, 'endDate'));
    return ($s !== '' && $t !== '') ? $s . ' — ' . $t : $s . $t;
}

function cms_cv_entry($title, $sub, $dates, $url, $summary, array $bullets)
{
    $h = '<li class="cv-entry reveal"><div class="cv-entry__when">' . e($dates) . '</div><div class="cv-entry__what">';
    $h .= '<h3>' . e($title) . '</h3>';
    if ($sub !== '') {
        $href = preg_match('#^https?://#', $url) ? $url : 'https://' . $url;
        $h .= '<p class="cv-entry__org">' . ($url !== '' ? '<a href="' . e($href) . '" rel="noopener">' . e($sub) . '</a>' : e($sub)) . '</p>';
    }
    if ($summary !== '') $h .= '<p>' . e($summary) . '</p>';
    if ($bullets) {
        $h .= '<ul>';
        // Plain text, except Markdown links: [label](https://...).
        foreach ($bullets as $b) {
            $h .= '<li>' . preg_replace('#\[([^\]]+)\]\((https?://[^)\s]+)\)#', '<a href="$2" rel="noopener">$1</a>', e($b)) . '</li>';
        }
        $h .= '</ul>';
    }
    return $h . '</div></li>';
}

function cms_view_cv()
{
    $cv = cms_doc('cv');
    $b = isset($cv['basics']) ? $cv['basics'] : array();
    $name = cms_cv_get($b, 'name');
    if ($name === '' || $name === strtoupper($name)) $name = ucwords(strtolower($name !== '' ? $name : cms_config('site_name')));

    $html = '<header class="cv-head"><p class="kicker reveal">' . e(cms_t('Curriculum vitae')) . '</p>';
    $html .= '<h1 class="post-title split">' . e($name) . '</h1>';
    if (cms_cv_get($b, 'label') !== '') $html .= '<p class="cv-head__label reveal">' . e(cms_cv_get($b, 'label')) . '</p>';
    if (cms_cv_get($b, 'summary') !== '') $html .= '<p class="post-description reveal">' . e(cms_cv_get($b, 'summary')) . '</p>';
    $html .= '<div class="cv-head__actions reveal">';
    if (cms_cv_get($b, 'pdf') !== '') {
        $html .= '<a class="btn btn--primary magnetic" href="' . e(cms_asset_url(cms_cv_get($b, 'pdf'))) . '" download><i class="ti ti-download"></i> ' . e(cms_t('Download CV (PDF)')) . '</a>';
    }
    if (cms_cv_get($b, 'email') !== '') {
        $html .= '<a class="btn btn--ghost magnetic" href="mailto:' . e(cms_cv_get($b, 'email')) . '"><i class="ti ti-mail"></i> ' . e(cms_cv_get($b, 'email')) . '</a>';
    }
    $html .= '</div></header>';

    $sections = array();
    foreach (cms_cv_schema() as $key => $def) {
        if (!empty($cv[$key])) $sections[$key] = $def['label'];
    }
    $pubs = cms_list_items('publications');
    if ($pubs) $sections['publications'] = 'Publications';
    $html .= '<nav class="cv-index reveal" aria-label="CV sections">';
    foreach ($sections as $k => $label) $html .= '<a href="#cv-' . $k . '">' . e(cms_t($label)) . '</a>';
    $html .= '</nav>';

    foreach ($sections as $key => $label) {
        $html .= '<section class="cv-section" id="cv-' . $key . '"><h2 class="cv-section__title reveal">' . e(cms_t($label)) . '</h2>';
        if ($key === 'work') {
            $html .= '<ol class="cv-timeline">';
            foreach ($cv['work'] as $e) {
                $sub = cms_cv_get($e, 'name') . (cms_cv_get($e, 'location') !== '' ? ' · ' . cms_cv_get($e, 'location') : '');
                $html .= cms_cv_entry(cms_cv_get($e, 'position'), $sub, cms_cv_range($e), cms_cv_get($e, 'url'), cms_cv_get($e, 'summary'), (array) cms_cv_get($e, 'highlights'));
            }
            $html .= '</ol>';
        } elseif ($key === 'education') {
            $html .= '<ol class="cv-timeline">';
            foreach ($cv['education'] as $e) {
                $title = trim(cms_cv_get($e, 'studyType') . (cms_cv_get($e, 'area') !== '' ? ', ' . cms_cv_get($e, 'area') : ''), ', ');
                $bul = (array) cms_cv_get($e, 'highlights');
                if (cms_cv_get($e, 'score') !== '') array_unshift($bul, 'Grade: ' . cms_cv_get($e, 'score'));
                $sub = cms_cv_get($e, 'institution') . (cms_cv_get($e, 'location') !== '' ? ' · ' . cms_cv_get($e, 'location') : '');
                $html .= cms_cv_entry($title, $sub, cms_cv_range($e), cms_cv_get($e, 'url'), '', $bul);
            }
            $html .= '</ol>';
        } elseif ($key === 'awards') {
            $html .= '<ol class="cv-timeline cv-timeline--tight">';
            foreach ($cv['awards'] as $e) {
                $html .= cms_cv_entry(cms_cv_get($e, 'title'), cms_cv_get($e, 'awarder'), cms_cv_date(cms_cv_get($e, 'date')), cms_cv_get($e, 'url'), cms_cv_get($e, 'summary'), array());
            }
            $html .= '</ol>';
        } elseif ($key === 'projects') {
            $html .= '<ol class="cv-timeline">';
            foreach ($cv['projects'] as $e) {
                $html .= cms_cv_entry(cms_cv_get($e, 'name'), '', cms_cv_range($e), cms_cv_get($e, 'url'), cms_cv_get($e, 'summary'), (array) cms_cv_get($e, 'highlights'));
            }
            $html .= '</ol>';
        } elseif ($key === 'skills' || $key === 'interests') {
            $html .= '<div class="cv-skills">';
            foreach ($cv[$key] as $e) {
                $level = cms_cv_get($e, 'level');
                $html .= '<div class="cv-skill reveal"><h3>' . e(cms_cv_get($e, 'name')) . ($level !== '' ? ' <small>' . e($level) . '</small>' : '') . '</h3><div>';
                foreach ((array) cms_cv_get($e, 'keywords') as $k) $html .= '<span class="chip">' . e($k) . '</span>';
                $html .= '</div></div>';
            }
            $html .= '</div>';
        } elseif ($key === 'languages') {
            $html .= '<div class="cv-skills">';
            foreach ($cv['languages'] as $e) {
                $html .= '<div class="cv-skill reveal"><h3>' . e(cms_cv_get($e, 'language')) . ' <small>' . e(cms_cv_get($e, 'fluency')) . '</small></h3></div>';
            }
            $html .= '</div>';
        } elseif ($key === 'publications') {
            $html .= '<ol class="pubs pubs--compact">';
            foreach ($pubs as $p) $html .= cms_publication_row($p, true);
            $html .= '</ol><p class="reveal"><a class="section__more" href="' . e(cms_url('publications/')) . '">All publications with abstracts and BibTeX <i class="ti ti-arrow-right"></i></a></p>';
        }
        $html .= '</section>';
    }
    return array('title' => 'CV', 'description' => 'Curriculum vitae of ' . cms_config('site_name') . '.', 'html' => $html);
}

// ---------------------------------------------------------------------------
// Repositories (live GitHub data is added by assets/js/repos.js)
// ---------------------------------------------------------------------------

function cms_view_repositories()
{
    $doc = cms_doc('repositories', array('user' => '', 'repos' => array()));
    $user = !empty($doc['user']) ? $doc['user'] : 'adnan-saood';
    $repos = array_values(array_filter(isset($doc['repos']) ? $doc['repos'] : array(), function ($r) { return !empty($r['visible']); }));
    $tags = array();
    foreach ($repos as $r) foreach ($r['tags'] as $t) $tags[$t] = true;
    $u = e($user);

    $h = '<div class="oss" data-github-user="' . $u . '"><header class="oss-head"><div>'
        . '<p class="kicker reveal">' . e(cms_t('Open source')) . '</p><h1 class="post-title split">' . e(cms_t('Code that makes robots feel.')) . '</h1>'
        . '<p class="post-description reveal">' . e(cms_t('ROS 2 drivers, robot descriptions, embedded firmware and research tools, live from GitHub.')) . '</p></div>'
        . '<a class="oss-profile tilt reveal" href="https://github.com/' . $u . '" rel="noopener">'
        . '<img class="oss-profile__avatar" src="https://github.com/' . $u . '.png?size=160" alt="" width="72" height="72" loading="lazy">'
        . '<span class="oss-profile__name" data-gh="name">' . e(cms_config('site_name')) . '</span>'
        . '<span class="oss-profile__handle"><i class="ti ti-brand-github"></i> @' . $u . '</span>'
        . '<span class="oss-profile__bio" data-gh="bio"></span>'
        . '<span class="oss-profile__meta"><span><b data-gh="followers">–</b> followers</span><span><b data-gh="since">–</b> on GitHub</span></span>'
        . '<span class="oss-profile__cta">Follow on GitHub <i class="ti ti-arrow-up-right"></i></span></a></header>';
    $h .= '<section class="oss-stats" aria-label="GitHub statistics">'
        . '<div class="oss-stat reveal"><b data-gh="repos">' . count($repos) . '</b><span>public repositories</span></div>'
        . '<div class="oss-stat reveal"><b data-gh="languages">–</b><span>languages in use</span></div>'
        . '<div class="oss-stat reveal"><b data-gh="stars">–</b><span>stars across projects</span></div>'
        . '<div class="oss-stat reveal"><b><span class="oss-pulse" aria-hidden="true"></span><span data-gh="lastpush">–</span></b><span>' . e(cms_t('since the last push')) . '</span></div></section>';
    $h .= '<section class="oss-langs reveal" aria-label="Languages" hidden><div class="oss-langs__bar" data-gh="langbar"></div><ul class="oss-langs__legend" data-gh="langlegend"></ul></section>';
    $h .= '<section class="oss-featured"><div class="section__head"><div><p class="kicker">' . e(cms_t('Featured')) . '</p><h2 class="section__title split">' . e(cms_t('Selected repositories.')) . '</h2></div>'
        . '<label class="oss-sort">' . e(cms_t('Sort')) . ' <select data-oss-sort><option value="curated">' . e(cms_t('Curated')) . '</option><option value="updated">' . e(cms_t('Recently updated')) . '</option>'
        . '<option value="stars">' . e(cms_t('Most stars')) . '</option><option value="name">' . e(cms_t('Name')) . '</option></select></label></div>';
    if (count($tags) > 1) {
        $h .= '<div class="filters reveal" data-filters="#repo-grid" role="group" aria-label="Filter repositories"><button type="button" class="is-active" data-filter="*">' . e(cms_t('All')) . '</button>';
        foreach (array_keys($tags) as $t) $h .= '<button type="button" data-filter="' . e($t) . '">' . e($t) . '</button>';
        $h .= '</div>';
    }
    $h .= '<div class="repo-grid" id="repo-grid">';
    foreach ($repos as $i => $r) {
        $name = substr(strrchr('/' . $r['repo'], '/'), 1);
        $h .= '<a class="repo-card project-card repo-card--' . e($r['size']) . '" href="https://github.com/' . e($r['repo']) . '" rel="noopener"'
            . ' data-repo="' . e($r['repo']) . '" data-order="' . ($i + 1) . '" data-category="' . e(implode('|', $r['tags'])) . '">'
            . '<span class="repo-card__top"><i class="ti ti-book-2" aria-hidden="true"></i><span class="repo-card__name">' . e($name) . '</span><span class="repo-card__live" hidden>active</span></span>'
            . '<span class="repo-card__desc" data-note="' . e($r['note']) . '">' . e($r['note']) . '</span><span class="repo-card__tags">';
        foreach ($r['tags'] as $t) $h .= '<span class="chip">' . e($t) . '</span>';
        $h .= '</span><span class="repo-card__foot"><span class="repo-card__lang"><span class="dot"></span><span data-f="lang"></span></span>'
            . '<span data-f="stars" hidden><i class="ti ti-star"></i> <b></b></span><span data-f="forks" hidden><i class="ti ti-git-fork"></i> <b></b></span>'
            . '<span class="repo-card__updated" data-f="updated"></span></span></a>';
    }
    $h .= '</div></section>';
    $h .= '<section class="oss-activity reveal" hidden><div class="section__head"><div><p class="kicker">Live</p><h2 class="section__title">' . e(cms_t('Recent activity.')) . '</h2></div>'
        . '<a class="section__more" href="https://github.com/' . $u . '?tab=repositories" rel="noopener">' . e(cms_t('All repositories on GitHub')) . ' <i class="ti ti-arrow-up-right"></i></a></div>'
        . '<ol class="oss-timeline" data-gh="activity"></ol></section></div>';
    return array('title' => cms_t('Open source'), 'description' => cms_t('Open-source repositories by') . ' ' . cms_config('site_name') . '.', 'html' => $h);
}

// ---------------------------------------------------------------------------
// Homepage (texts edited in Admin -> Homepage, stored in cms-data/home.json)
// ---------------------------------------------------------------------------

// "*word*" in a title becomes the gradient accent.
function cms_home_title($s)
{
    return preg_replace('/\*([^*]+)\*/', '<span class="text-gradient">$1</span>', e($s));
}

function cms_view_home()
{
    $h = cms_home();
    return array('title' => '', 'description' => isset($h['hero']['lede']) ? $h['hero']['lede'] : '', 'home' => $h, 'html' => '');
}

// "Now" panel: the soonest upcoming talk, or the most recent one.
function cms_home_next_talk()
{
    $talks = cms_list_items('talks');
    $today = strtotime('today');
    $upcoming = array_values(array_filter($talks, function ($t) use ($today) { return $t['date'] >= $today; }));
    if ($upcoming) return array(end($upcoming), true); // list is newest first
    return $talks ? array($talks[0], false) : array(null, false);
}

// Where the hero's "CV" button goes: the PDF when one is set, else the CV page.
function cms_home_cv_link()
{
    $cv = cms_doc('cv');
    $pdf = isset($cv['basics']) ? cms_cv_get($cv['basics'], 'pdf') : '';
    return $pdf !== '' ? cms_asset_url($pdf) : cms_url('cv/');
}

// ---------------------------------------------------------------------------
// Talks & media
// ---------------------------------------------------------------------------

function cms_talk_kinds()
{
    return array_map('cms_t', array('keynote' => 'Keynote', 'talk' => 'Talk', 'paper' => 'Paper presentation', 'poster' => 'Poster',
                 'workshop' => 'Workshop', 'panel' => 'Panel', 'press' => 'Press', 'podcast' => 'Podcast', 'video' => 'Video'));
}

// YouTube / Vimeo link -> privacy-friendly embed URL, or '' if not embeddable.
function cms_video_embed($url)
{
    if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})#', $url, $m)) {
        return array('https://www.youtube-nocookie.com/embed/' . $m[1] . '?autoplay=1&rel=0', 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg');
    }
    if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $url, $m)) {
        return array('https://player.vimeo.com/video/' . $m[1] . '?autoplay=1&dnt=1', '');
    }
    return array('', '');
}

function cms_talk_card(array $t, $compact = false)
{
    $kinds = cms_talk_kinds();
    $upcoming = $t['date'] > time();
    $h = '<article class="talk reveal" id="' . e($t['slug']) . '" data-category="' . e($t['kind']) . '">';
    // Media: click-to-load video, else picture, else a gradient tile.
    list($embed, $thumb) = $t['video'] !== '' ? cms_video_embed($t['video']) : array('', '');
    $img = $t['image'] !== '' ? cms_asset_url($t['image']) : $thumb;
    if ($embed !== '') {
        $h .= '<button type="button" class="talk__media talk__video" data-embed="' . e($embed) . '" aria-label="' . e(cms_t('Play video')) . ': ' . e($t['title']) . '">'
            . ($img !== '' ? '<img src="' . e($img) . '" alt="" loading="lazy">' : '') . '<span class="talk__play"><i class="ti ti-player-play-filled"></i></span></button>';
    } elseif (preg_match('/\.(mp4|webm)$/i', $t['video'])) {
        $h .= '<div class="talk__media"><video src="' . e(cms_asset_url($t['video'])) . '" controls preload="metadata"' . ($img !== '' ? ' poster="' . e($img) . '"' : '') . '></video></div>';
    } elseif ($img !== '') {
        $h .= '<div class="talk__media"><img src="' . e($img) . '" alt="" loading="lazy"></div>';
    } else {
        $h .= '<div class="talk__media talk__tile"><span>' . e($t['event'] !== '' ? $t['event'] : (isset($kinds[$t['kind']]) ? $kinds[$t['kind']] : cms_t('Talk'))) . '</span></div>';
    }
    $h .= '<div class="talk__body"><div class="pub__meta">';
    if ($upcoming) $h .= '<span class="chip chip--live">' . e(cms_t('Upcoming')) . '</span>';
    $h .= '<span class="chip">' . e(isset($kinds[$t['kind']]) ? $kinds[$t['kind']] : cms_t('Talk')) . '</span>';
    if ($t['award'] !== '') $h .= '<span class="chip chip--award"><i class="ti ti-trophy"></i> ' . e($t['award']) . '</span>';
    $h .= '</div><h3 class="talk__title">' . e($t['title']) . '</h3>';
    $where = array_filter(array($t['event'], $t['location'], cms_date('M j, Y', $t['date'])));
    $h .= '<p class="talk__where">' . e(implode(' · ', $where)) . '</p>';
    if (!$compact && trim($t['body']) !== '') $h .= '<div class="talk__text">' . cms_markdown($t['body']) . '</div>';
    $links = array();
    if ($t['slides'] !== '') $links[] = '<a class="pub__link" href="' . e(cms_asset_url($t['slides'])) . '" rel="noopener"><i class="ti ti-presentation"></i> ' . e(cms_t('Slides')) . '</a>';
    if ($t['video'] !== '' && $embed === '') $links[] = '<a class="pub__link" href="' . e(cms_asset_url($t['video'])) . '" rel="noopener"><i class="ti ti-player-play"></i> ' . e(cms_t('Video')) . '</a>';
    if ($t['code'] !== '') $links[] = '<a class="pub__link" href="' . e($t['code']) . '" rel="noopener"><i class="ti ti-brand-github"></i> Code</a>';
    if ($t['url'] !== '') $links[] = '<a class="pub__link" href="' . e($t['url']) . '" rel="noopener"><i class="ti ti-external-link"></i> ' . e(cms_t('Event page')) . '</a>';
    if ($t['post'] !== '') $links[] = '<a class="pub__link" href="' . e(cms_asset_url($t['post'])) . '"><i class="ti ti-article"></i> ' . e(cms_t('Read the story')) . '</a>';
    if ($links) $h .= '<div class="pub__links">' . implode('', $links) . '</div>';
    return $h . '</div></article>';
}

function cms_view_talks()
{
    $items = cms_list_items('talks');
    $kinds = cms_talk_kinds();
    $present = array();
    foreach ($items as $t) $present[$t['kind']] = true;
    $html = '<header class="cms-page-header"><p class="kicker reveal">' . e(cms_t('Talks & media')) . '</p>'
        . '<h1 class="post-title split">' . e(cms_t('Talks, workshops & media.')) . '</h1>'
        . '<p class="post-description reveal">' . e(cms_t('Invited talks, conference presentations, workshops I organised and media coverage.')) . '</p></header>';
    if (count($present) > 1) {
        $html .= '<div class="filters reveal" data-filters="#talk-list" role="group"><button type="button" class="is-active" data-filter="*">' . e(cms_t('All')) . '</button>';
        foreach ($kinds as $k => $label) {
            if (isset($present[$k])) $html .= '<button type="button" data-filter="' . e($k) . '">' . e($label) . '</button>';
        }
        $html .= '</div>';
    }
    $html .= '<div class="talk-list" id="talk-list">';
    foreach ($items as $t) $html .= cms_talk_card($t);
    $html .= $items ? '' : '<p>' . e(cms_t('No talks yet.')) . '</p>';
    $html .= '</div>';
    return array('title' => cms_t('Talks & media'), 'description' => cms_t('Talks, workshops and media by') . ' ' . cms_config('site_name') . '.', 'html' => $html);
}

// ---------------------------------------------------------------------------
// 3D models (projects with a .glb file), rendered by <model-viewer>
// ---------------------------------------------------------------------------

function cms_model_viewer(array $p, $class = '')
{
    $poster = $p['img'] !== '' ? ' poster="' . e(cms_asset_url($p['img'])) . '"' : '';
    return '<model-viewer class="model ' . e($class) . '" src="' . e(cms_asset_url($p['model'])) . '"' . $poster
        . ' alt="' . e(cms_t('3D model') . ': ' . $p['title']) . '" camera-controls touch-action="pan-y" auto-rotate auto-rotate-delay="1500"'
        . ' rotation-per-second="18deg" interaction-prompt="auto" shadow-intensity="1" shadow-softness="0.9" exposure="1.05"'
        . ' environment-image="neutral" loading="lazy" reveal="auto" ar ar-modes="webxr scene-viewer quick-look">'
        . '<div class="model__progress" slot="progress-bar"><span></span></div>'
        . '<button class="model__ar" slot="ar-button" type="button"><i class="ti ti-augmented-reality"></i> ' . e(cms_t('View in your space')) . '</button>'
        . '</model-viewer>';
}

// Homepage showcase: one large viewer + a thumbnail strip to switch robots.
// With $demo, a small robot made of cubes stands in until a project has a model.
function cms_models_showcase($limit, $demo = false)
{
    $models = array_values(array_filter(cms_list_items('projects'), function ($p) { return $p['model'] !== '' && $p['model_home']; }));
    $models = array_slice($models, 0, $limit);
    if (!$models && $demo) {
        $first = array('title' => cms_t('Demo robot'), 'model' => 'assets/models/demo-robot.glb', 'img' => '', 'category' => cms_t('3D demo'));
        return '<div class="robots robots--demo" data-robots><div class="robots__stage">' . cms_model_viewer($first, 'robots__viewer')
            . '<p class="robots__hint"><i class="ti ti-hand-move"></i> ' . e(cms_t('Drag to rotate · scroll to zoom')) . '</p></div>'
            . '<div class="robots__info"><p class="kicker">' . e($first['category']) . '</p><h3>' . e($first['title']) . '</h3>'
            . '<p>' . e(cms_t('A placeholder robot built from cubes, to show how the 3D viewer works: drag it, zoom, or open it in augmented reality on a phone. My own robot designs will appear here.')) . '</p></div></div>';
    }
    if (!$models) return '';
    $first = $models[0];
    $h = '<div class="robots" data-robots>';
    $h .= '<div class="robots__stage">' . cms_model_viewer($first, 'robots__viewer')
        . '<p class="robots__hint"><i class="ti ti-hand-move"></i> ' . e(cms_t('Drag to rotate · scroll to zoom')) . '</p></div>';
    $h .= '<div class="robots__info"><p class="kicker" data-robot-cat>' . e($first['category']) . '</p><h3 data-robot-title>' . e($first['title']) . '</h3>'
        . '<p data-robot-text>' . e(cms_excerpt($first, 220)) . '</p>'
        . '<a class="btn btn--ghost magnetic" data-robot-link href="' . e(cms_project_url($first['slug'])) . '">' . e(cms_t('View project')) . ' <i class="ti ti-arrow-right"></i></a>';
    if (count($models) > 1) {
        $h .= '<div class="robots__thumbs" role="tablist">';
        foreach ($models as $i => $m) {
            $h .= '<button type="button" role="tab" aria-selected="' . ($i === 0 ? 'true' : 'false') . '" class="robots__thumb' . ($i === 0 ? ' is-active' : '') . '"'
                . ' data-src="' . e(cms_asset_url($m['model'])) . '" data-poster="' . e($m['img'] !== '' ? cms_asset_url($m['img']) : '') . '"'
                . ' data-title="' . e($m['title']) . '" data-cat="' . e($m['category']) . '" data-text="' . e(cms_excerpt($m, 220)) . '"'
                . ' data-link="' . e(cms_project_url($m['slug'])) . '">'
                . ($m['img'] !== '' ? '<img src="' . e(cms_asset_url($m['img'])) . '" alt="" loading="lazy">' : '<i class="ti ti-3d-cube-sphere"></i>')
                . '<span>' . e($m['title']) . '</span></button>';
        }
        $h .= '</div>';
    }
    return $h . '</div></div>';
}
