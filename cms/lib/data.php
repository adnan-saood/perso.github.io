<?php
// Whole-document JSON data edited from the admin (CV, repositories).
// Stored in cms-data/<name>.json; every save keeps the previous version in history/.

if (!defined('CMS_BOOT')) { http_response_code(404); exit; }

function cms_doc_file($name)
{
    return cms_data_path(preg_replace('/[^a-z0-9_-]/', '', $name) . '.json');
}

function cms_doc($name, $default = array())
{
    return cms_read_json(cms_doc_file($name), $default);
}

function cms_doc_save($name, array $data)
{
    $file = cms_doc_file($name);
    if (is_file($file)) cms_backup($file, 'doc/' . $name . '.json');
    return cms_write_json($file, $data);
}

// ---------------------------------------------------------------------------
// CV (JSON Resume format, https://jsonresume.org/schema)
// ---------------------------------------------------------------------------

// Editable CV sections: key => label + fields. Field types: text, textarea, list (one item per line), date.
function cms_cv_schema()
{
    return array(
        'work' => array('label' => 'Experience', 'title' => 'position', 'fields' => array(
            'position' => array('Role', 'text'), 'name' => array('Organisation', 'text'), 'location' => array('Location', 'text'),
            'startDate' => array('Start (YYYY-MM)', 'text'), 'endDate' => array('End (YYYY-MM or "Present")', 'text'),
            'url' => array('Link', 'text'), 'summary' => array('Summary', 'textarea'), 'highlights' => array('Highlights (one per line)', 'list'))),
        'education' => array('label' => 'Education', 'title' => 'studyType', 'fields' => array(
            'studyType' => array('Degree', 'text'), 'area' => array('Field', 'text'), 'institution' => array('Institution', 'text'),
            'location' => array('Location', 'text'), 'startDate' => array('Start', 'text'), 'endDate' => array('End', 'text'),
            'score' => array('Grade', 'text'), 'url' => array('Link', 'text'), 'courses' => array('Courses (one per line)', 'list'),
            'highlights' => array('Highlights (one per line)', 'list'))),
        'awards' => array('label' => 'Awards & honours', 'title' => 'title', 'fields' => array(
            'title' => array('Award', 'text'), 'awarder' => array('Awarded by', 'text'), 'date' => array('Year / date', 'text'),
            'url' => array('Link', 'text'), 'summary' => array('Summary', 'textarea'))),
        'skills' => array('label' => 'Skills', 'title' => 'name', 'fields' => array(
            'name' => array('Skill group', 'text'), 'level' => array('Level', 'text'), 'icon' => array('Icon (Font Awesome class, optional)', 'text'),
            'keywords' => array('Keywords (one per line)', 'list'))),
        'projects' => array('label' => 'Selected projects', 'title' => 'name', 'fields' => array(
            'name' => array('Project', 'text'), 'startDate' => array('Start', 'text'), 'endDate' => array('End', 'text'),
            'url' => array('Link', 'text'), 'summary' => array('Summary', 'textarea'), 'highlights' => array('Highlights (one per line)', 'list'))),
        'languages' => array('label' => 'Languages', 'title' => 'language', 'fields' => array(
            'language' => array('Language', 'text'), 'fluency' => array('Level', 'text'))),
        'interests' => array('label' => 'Interests', 'title' => 'name', 'fields' => array(
            'name' => array('Interest', 'text'), 'keywords' => array('Keywords (one per line)', 'list'))),
    );
}

// CV fields that can have a French version (field_fr). Dates, links and grades don't.
function cms_cv_translatable($field)
{
    return !in_array($field, array('startDate', 'endDate', 'date', 'url', 'score', 'icon'), true);
}

function cms_cv_basics_fields()
{
    return array('name' => 'Name', 'label' => 'Headline', 'email' => 'Email', 'phone' => 'Phone', 'url' => 'Website', 'summary' => 'Summary');
}

// Cleans a CV posted from the admin editor against the schema.
function cms_cv_sanitize(array $in)
{
    $out = array('basics' => array());
    $b = isset($in['basics']) && is_array($in['basics']) ? $in['basics'] : array();
    foreach (cms_cv_basics_fields() as $k => $label) {
        $out['basics'][$k] = isset($b[$k]) ? trim((string) $b[$k]) : '';
    }
    $old = cms_doc('cv');
    foreach (array('location', 'profiles', 'image') as $keep) {
        if (isset($old['basics'][$keep])) $out['basics'][$keep] = $old['basics'][$keep];
    }
    $out['basics']['pdf'] = isset($b['pdf']) ? trim((string) $b['pdf']) : '';
    foreach (array('label_fr', 'summary_fr') as $k) {
        if (isset($b[$k]) && trim((string) $b[$k]) !== '') $out['basics'][$k] = trim((string) $b[$k]);
    }
    foreach (cms_cv_schema() as $section => $def) {
        $out[$section] = array();
        $rows = isset($in[$section]) && is_array($in[$section]) ? $in[$section] : array();
        foreach ($rows as $row) {
            if (!is_array($row)) continue;
            $clean = array();
            $empty = true;
            foreach ($def['fields'] as $f => $spec) {
                $v = isset($row[$f]) ? $row[$f] : '';
                if ($spec[1] === 'list') {
                    $v = is_array($v) ? $v : preg_split('/\r?\n/', (string) $v);
                    $v = array_values(array_filter(array_map('trim', $v), 'strlen'));
                    if ($v) $empty = false;
                } else {
                    $v = trim((string) $v);
                    if ($v !== '') $empty = false;
                }
                $clean[$f] = $v;
                // Optional French version of the field (shown to visitors who read in French).
                if (cms_cv_translatable($f) && isset($row[$f . '_fr'])) {
                    $fr = $row[$f . '_fr'];
                    $fr = $spec[1] === 'list'
                        ? array_values(array_filter(array_map('trim', is_array($fr) ? $fr : preg_split('/\r?\n/', (string) $fr)), 'strlen'))
                        : trim((string) $fr);
                    if ($fr !== '' && $fr !== array()) $clean[$f . '_fr'] = $fr;
                }
            }
            if (!$empty) $out[$section][] = $clean;
        }
    }
    return $out;
}

// "2024-04-01" -> "Apr 2024"; "Present"/"2021" pass through.
function cms_cv_date($d)
{
    $d = trim((string) $d);
    if (preg_match('/^(\d{4})-(\d{2})/', $d, $m)) return cms_date('M Y', mktime(0, 0, 0, (int) $m[2], 1, (int) $m[1]));
    return strcasecmp($d, 'Present') === 0 ? cms_t('Present') : $d;
}

// ---------------------------------------------------------------------------
// Repositories shown on /repositories/
// ---------------------------------------------------------------------------

function cms_repos_sanitize(array $in)
{
    $out = array('user' => preg_replace('/[^A-Za-z0-9-]/', '', isset($in['user']) ? $in['user'] : ''), 'repos' => array());
    $seen = array();
    foreach (isset($in['repos']) && is_array($in['repos']) ? $in['repos'] : array() as $r) {
        if (!is_array($r) || empty($r['repo'])) continue;
        $name = trim((string) $r['repo']);
        if (!preg_match('#^[A-Za-z0-9-]+/[A-Za-z0-9._-]+$#', $name) || isset($seen[strtolower($name)])) continue;
        $seen[strtolower($name)] = true;
        $tags = is_array(isset($r['tags']) ? $r['tags'] : null) ? $r['tags'] : preg_split('/\s*,\s*/', (string) (isset($r['tags']) ? $r['tags'] : ''));
        $out['repos'][] = array(
            'repo' => $name,
            'tags' => array_values(array_filter(array_map('trim', $tags), 'strlen')),
            'note' => trim((string) (isset($r['note']) ? $r['note'] : '')),
            'size' => in_array(isset($r['size']) ? $r['size'] : '', array('large', 'normal', 'compact'), true) ? $r['size'] : 'normal',
            'visible' => !empty($r['visible']),
        );
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Homepage texts (cms-data/home.json: {"en": {...}, "fr": {...}})
// ---------------------------------------------------------------------------

// Field kinds: text, textarea, paragraphs (blank-line separated), list (one per line),
// image, rows (repeatable groups of text fields).
function cms_home_schema()
{
    return array(
        'Hero' => array(
            'hero.kicker' => array('Small line above the title', 'text'),
            'hero.title' => array('Big title (wrap a word in *stars* for the gradient)', 'text'),
            'hero.lede' => array('Intro sentence', 'textarea'),
        ),
        'Recruiter strip (under the intro)' => array(
            'hero.status' => array('Availability, shown with a green dot (e.g. “Available from October 2027”); empty = hidden', 'text'),
            'hero.seeking' => array('What you are looking for (e.g. “Postdoc or R&D roles in tactile HRI”)', 'text'),
            'hero.links' => array('Show CV · Email · Scholar buttons', 'check'),
            'proof' => array('Proof badges (patents, awards…)', 'rows', array('label' => 'Text', 'icon' => 'Icon (tabler name, e.g. ti-trophy)', 'link' => 'Link (optional, e.g. news/?n=my-award)')),
        ),
        'Now panel' => array(
            'now.show' => array('Show the “Now” panel (current work, next talk, latest GitHub commit)', 'check'),
            'now.title' => array('Title', 'text'),
            'now.text' => array('What you are working on right now', 'textarea'),
            'now.link' => array('Link for it (optional)', 'text'),
        ),
        'About' => array(
            'about.image' => array('Portrait', 'image'),
            'about.lead' => array('Lead sentence', 'textarea'),
            'about.body' => array('Paragraphs (leave an empty line between paragraphs)', 'paragraphs'),
            'facts' => array('Fact boxes', 'rows', array('label' => 'Label', 'value' => 'Text')),
        ),
        'Keywords strip' => array(
            'keywords' => array('Keywords (one per line)', 'list'),
        ),
        'Research' => array(
            'research_title' => array('Section title', 'text'),
            'pillars' => array('Research threads', 'rows', array('title' => 'Title', 'icon' => 'Icon (tabler name, e.g. ti-hand-finger)', 'text' => 'Text')),
        ),
        'Robots in 3D' => array(
            'robots_title' => array('Section title', 'text'),
            'robots_demo' => array('Show a demo model while no project has a 3D model on the homepage', 'check'),
        ),
        'Other section titles' => array(
            'work_title' => array('“Selected work” title', 'text'),
            'papers_title' => array('“Selected papers” title', 'text'),
            'talks_title' => array('“Talks & media” title', 'text'),
        ),
        'Highlights' => array(
            'stats' => array('Numbers (write “repos” as the value to show your live GitHub repository count)', 'rows', array('value' => 'Number', 'label' => 'Label')),
        ),
    );
}

function cms_path_get(array $a, $path)
{
    foreach (explode('.', $path) as $k) {
        if (!is_array($a) || !isset($a[$k])) return null;
        $a = $a[$k];
    }
    return $a;
}

function cms_path_set(array &$a, $path, $value)
{
    $ref = &$a;
    foreach (explode('.', $path) as $k) {
        if (!isset($ref[$k]) || !is_array($ref[$k])) $ref[$k] = array();
        $ref = &$ref[$k];
    }
    $ref = $value;
}

// Homepage content in the visitor's language (French falls back to English per field).
function cms_home($lang = null)
{
    $doc = cms_doc('home');
    $en = isset($doc['en']) ? $doc['en'] : array();
    $lang = $lang ?: cms_lang();
    if ($lang !== 'fr' || empty($doc['fr'])) return $en;
    $out = $en;
    foreach (cms_home_schema() as $fields) {
        foreach ($fields as $path => $spec) {
            if ($spec[1] === 'check') continue; // switches are set once, on the English tab
            $v = cms_path_get($doc['fr'], $path);
            if ($v !== null && $v !== '' && $v !== array()) cms_path_set($out, $path, $v);
        }
    }
    return $out;
}

// Cleans one language of the homepage form.
function cms_home_sanitize(array $in)
{
    $out = array();
    foreach (cms_home_schema() as $fields) {
        foreach ($fields as $path => $spec) {
            $key = str_replace('.', '__', $path);
            $v = isset($in[$key]) ? $in[$key] : '';
            switch ($spec[1]) {
                case 'list':
                    $v = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $v)), 'strlen'));
                    break;
                case 'paragraphs':
                    $v = array_values(array_filter(array_map('trim', preg_split('/\r?\n\s*\r?\n/', trim((string) $v))), 'strlen'));
                    break;
                case 'check':
                    $v = $v === '1';
                    break;
                case 'rows':
                    $rows = array();
                    foreach (is_array($v) ? $v : array() as $row) {
                        $clean = array();
                        foreach (array_keys($spec[2]) as $f) $clean[$f] = trim((string) (isset($row[$f]) ? $row[$f] : ''));
                        if (implode('', $clean) !== '') $rows[] = $clean;
                    }
                    $v = $rows;
                    break;
                default:
                    $v = trim((string) $v);
            }
            cms_path_set($out, $path, $v);
        }
    }
    return $out;
}
