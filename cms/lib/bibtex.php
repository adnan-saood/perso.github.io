<?php
// Minimal BibTeX reader for "Import from BibTeX" in the admin.

if (!defined('CMS_BOOT')) { http_response_code(404); exit; }

// Returns array('type' => ..., 'key' => ..., 'fields' => array(...)) or null.
function cms_bibtex_parse($text)
{
    $text = (string) $text;
    if (!preg_match('/@(\w+)\s*\{\s*([^,\s]+)\s*,/', $text, $m, PREG_OFFSET_CAPTURE)) return null;
    $type = strtolower($m[1][0]);
    $key = $m[2][0];
    $i = $m[0][1] + strlen($m[0][0]);
    $n = strlen($text);
    $fields = array();
    while ($i < $n) {
        if (!preg_match('/\G[\s,]*(\w+)\s*=\s*/', $text, $fm, 0, $i)) break;
        $name = strtolower($fm[1]);
        $i += strlen($fm[0]);
        if ($i >= $n) break;
        if ($text[$i] === '{') {
            $depth = 0;
            $start = $i;
            for (; $i < $n; $i++) {
                if ($text[$i] === '{') $depth++;
                elseif ($text[$i] === '}' && --$depth === 0) break;
            }
            $value = substr($text, $start + 1, $i - $start - 1);
            $i++;
        } elseif ($text[$i] === '"') {
            $end = strpos($text, '"', $i + 1);
            if ($end === false) break;
            $value = substr($text, $i + 1, $end - $i - 1);
            $i = $end + 1;
        } else {
            preg_match('/\G[^,}\n]*/', $text, $vm, 0, $i);
            $value = trim($vm[0]);
            $i += strlen($vm[0]);
        }
        $fields[$name] = $value;
    }
    return array('type' => $type, 'key' => $key, 'fields' => $fields);
}

function cms_bibtex_plain($s)
{
    $map = array("{\\'i}" => 'í', "{\\'a}" => 'á', "{\\'e}" => 'é', "{\\'o}" => 'ó', '{\\"u}' => 'ü', '{\\"o}' => 'ö', '~' => ' ', '--' => '–');
    return trim(preg_replace('/[{}]/', '', strtr((string) $s, $map)));
}

// Converts a parsed entry into publication front matter + abstract.
function cms_bibtex_to_publication($raw)
{
    $e = cms_bibtex_parse($raw);
    if (!$e) return null;
    $f = $e['fields'];
    $authors = array();
    foreach (preg_split('/\s+and\s+/', cms_bibtex_plain(isset($f['author']) ? $f['author'] : '')) as $a) {
        $a = trim($a);
        if ($a === '') continue;
        if (strpos($a, ',') !== false) {
            list($last, $first) = array_map('trim', explode(',', $a, 2));
            $a = $first . ' ' . $last;
        }
        $authors[] = $a;
    }
    $types = array('article' => 'journal', 'inproceedings' => 'conference', 'incollection' => 'conference',
                   'phdthesis' => 'thesis', 'mastersthesis' => 'thesis');
    $venue = cms_bibtex_plain(isset($f['journal']) ? $f['journal'] : (isset($f['booktitle']) ? $f['booktitle'] : (isset($f['publisher']) ? $f['publisher'] : '')));
    $kind = isset($types[$e['type']]) ? $types[$e['type']] : 'other';
    if (stripos($venue, 'workshop') !== false) $kind = 'workshop';
    return array(
        'slug' => cms_slugify($e['key']),
        'meta' => array(
            'title' => cms_bibtex_plain(isset($f['title']) ? $f['title'] : ''),
            'authors' => $authors,
            'venue' => $venue,
            'year' => isset($f['year']) ? trim($f['year']) : '',
            'pubtype' => $kind,
            'doi' => preg_replace('#^https?://(dx\.)?doi\.org/#', '', isset($f['doi']) ? trim($f['doi']) : ''),
            'url' => isset($f['url']) ? trim($f['url']) : '',
            'pdf' => isset($f['pdf']) ? trim($f['pdf']) : '',
            'bibtex' => trim($raw),
        ),
        'abstract' => cms_bibtex_plain(isset($f['abstract']) ? $f['abstract'] : ''),
    );
}
