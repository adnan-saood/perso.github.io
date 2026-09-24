<?php
// RSS 2.0 feed of blog posts.
require_once __DIR__ . '/boot.php';

$host = (cms_is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
$posts = array_slice(cms_list_items('posts'), 0, 20);

header('Content-Type: application/rss+xml; charset=utf-8');
header('Cache-Control: public, max-age=300');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
  <title><?php echo e(cms_config('site_name')); ?> — Blog</title>
  <link><?php echo e($host . cms_url('blog/')); ?></link>
  <atom:link href="<?php echo e($host . cms_url('cms/feed.php')); ?>" rel="self" type="application/rss+xml"/>
  <description>Posts by <?php echo e(cms_config('site_name')); ?></description>
<?php foreach ($posts as $p): ?>
  <item>
    <title><?php echo e($p['title']); ?></title>
    <link><?php echo e($host . cms_post_url($p['slug'])); ?></link>
    <guid isPermaLink="true"><?php echo e($host . cms_post_url($p['slug'])); ?></guid>
    <pubDate><?php echo date(DATE_RSS, $p['date']); ?></pubDate>
    <description><?php echo e(cms_markdown($p['body'])); ?></description>
  </item>
<?php endforeach; ?>
</channel>
</rss>
