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
            'score' => array('Grade', 'text'), 'url' => array('Link', 'text'), 'highlights' => array('Highlights (one per line)', 'list'))),
        'awards' => array('label' => 'Awards & honours', 'title' => 'title', 'fields' => array(
            'title' => array('Award', 'text'), 'awarder' => array('Awarded by', 'text'), 'date' => array('Year / date', 'text'),
            'url' => array('Link', 'text'), 'summary' => array('Summary', 'textarea'))),
        'skills' => array('label' => 'Skills', 'title' => 'name', 'fields' => array(
            'name' => array('Skill group', 'text'), 'level' => array('Level', 'text'), 'keywords' => array('Keywords (one per line)', 'list'))),
        'projects' => array('label' => 'Selected projects', 'title' => 'name', 'fields' => array(
            'name' => array('Project', 'text'), 'startDate' => array('Start', 'text'), 'endDate' => array('End', 'text'),
            'url' => array('Link', 'text'), 'summary' => array('Summary', 'textarea'), 'highlights' => array('Highlights (one per line)', 'list'))),
        'languages' => array('label' => 'Languages', 'title' => 'language', 'fields' => array(
            'language' => array('Language', 'text'), 'fluency' => array('Level', 'text'))),
        'interests' => array('label' => 'Interests', 'title' => 'name', 'fields' => array(
            'name' => array('Interest', 'text'), 'keywords' => array('Keywords (one per line)', 'list'))),
    );
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
    if (preg_match('/^(\d{4})-(\d{2})/', $d, $m)) return date('M Y', mktime(0, 0, 0, (int) $m[2], 1, (int) $m[1]));
    return $d;
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
