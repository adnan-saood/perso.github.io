<?php
if (!defined('CMS_BOOT')) define('CMS_BOOT', 1);

// Never print PHP notices into pages: they would break headers, redirects and JSON.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/lib/core.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/files.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/bibtex.php';
require_once __DIR__ . '/lib/stats.php';
