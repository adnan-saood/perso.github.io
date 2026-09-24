<?php
// Starting content (cms-seed/ in the repository) imported into cms-data automatically.
//
// deploy.sh uploads cms-seed/ to cms-data/_seed on every deploy (the local preview reads
// it straight from the repository). On the next request:
//   - a seed item or document you don't have yet is copied in;
//   - when a seed file changed since the last import (e.g. French translations were
//     added), fields your copy doesn't have at all are added. Nothing you wrote is
//     ever overwritten, and items you deleted are not brought back.
// cms-data/state/seeded.json remembers what was imported.

function cms_seed_dir()
{
    if (getenv('CMS_SEED_DIR')) return rtrim(getenv('CMS_SEED_DIR'), '/');
    return cms_data_path('_seed');
}

// Cheap fingerprint of the seed folder: file names and modification times.
function cms_seed_files()
{
    $dir = cms_seed_dir();
    $out = array();
    foreach (array_keys(cms_types()) as $type) {
        foreach ((array) glob($dir . '/' . $type . '/*.md') as $f) $out[$type . '/' . basename($f)] = $f;
    }
    foreach ((array) glob($dir . '/*.json') as $f) $out[basename($f)] = $f;
    return $out;
}

function cms_seed_sync()
{
    $dir = cms_seed_dir();
    if (!is_dir($dir)) return;
    $files = cms_seed_files();
    $sig = '';
    foreach ($files as $rel => $f) $sig .= $rel . ':' . @filemtime($f) . ';';
    $sig = md5($sig);
    $stateFile = cms_data_path('state/seeded.json');
    $state = cms_read_json($stateFile, array());
    if (isset($state['sig']) && $state['sig'] === $sig) return;

    $known = isset($state['files']) ? $state['files'] : array();
    foreach ($files as $rel => $f) {
        $raw = @file_get_contents($f);
        if ($raw === false) continue;
        $hash = md5($raw);
        $target = cms_data_path($rel);
        if (!isset($known[$rel])) {
            // New seed file: import it unless you already have one with that name.
            if (!is_file($target)) {
                if (!is_dir(dirname($target))) @mkdir(dirname($target), 0775, true);
                cms_write_file($target, $raw);
            } else {
                cms_seed_fill($rel, $raw, $target);
            }
        } elseif ($known[$rel] !== $hash && is_file($target)) {
            cms_seed_fill($rel, $raw, $target);
        }
        $known[$rel] = $hash;
    }
    if (!is_dir(dirname($stateFile))) @mkdir(dirname($stateFile), 0775, true);
    cms_write_json($stateFile, array('sig' => $sig, 'files' => $known, 'at' => date('c')));
}

// Adds the seed's fields that your copy is missing; never changes a field you have.
function cms_seed_fill($rel, $seedRaw, $target)
{
    $mine = file_get_contents($target);
    if (substr($rel, -5) === '.json') {
        $cur = json_decode($mine, true);
        $seed = json_decode($seedRaw, true);
        if (!is_array($cur) || !is_array($seed)) return;
        $merged = cms_seed_merge($cur, $seed);
        if ($merged !== $cur) cms_write_json($target, $merged);
        return;
    }
    list($meta, $body) = cms_parse_document($mine);
    list($seedMeta) = cms_parse_document($seedRaw);
    $added = false;
    foreach ($seedMeta as $k => $v) {
        if (!array_key_exists($k, $meta) && $v !== '' && $v !== null && $v !== array()) {
            $meta[$k] = $v;
            $added = true;
        }
    }
    if ($added) cms_write_file($target, cms_build_document($meta, $body));
}

// Recursive "fill the gaps" merge for JSON documents: empty strings and empty lists in
// your copy are filled from the seed; anything with content is kept as it is.
function cms_seed_merge($cur, $seed)
{
    if (!is_array($seed)) return ($cur === '' || $cur === null || $cur === array()) ? $seed : $cur;
    if (!is_array($cur)) return ($cur === '' || $cur === null) ? $seed : $cur;
    if ($cur === array()) return $seed;
    $isList = array_keys($seed) === range(0, count($seed) - 1);
    if ($isList) {
        // Same entries as the seed (e.g. CV jobs): fill their gaps, such as new French fields.
        // A list you added to or removed from stays exactly yours.
        if (count($cur) !== count($seed) || array_keys($cur) !== array_keys($seed)) return $cur;
        foreach ($seed as $i => $v) {
            if (is_array($v) && is_array($cur[$i])) $cur[$i] = cms_seed_merge($cur[$i], $v);
        }
        return $cur;
    }
    foreach ($seed as $k => $v) {
        $cur[$k] = array_key_exists($k, $cur) ? cms_seed_merge($cur[$k], $v) : $v;
    }
    return $cur;
}
