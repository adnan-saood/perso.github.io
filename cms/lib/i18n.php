<?php
// English / French. The visitor's choice lives in the "lang" cookie (set by the
// header toggle). Content items can carry *_fr fields; interface strings are
// translated with cms_t() here and, on static Jekyll pages, by assets/js/cms.js
// using the same dictionary (served by cms/api.php?i18n=1).

if (!defined('CMS_BOOT')) { http_response_code(404); exit; }

function cms_lang()
{
    if (defined('CMS_ADMIN')) return 'en'; // the admin always edits the source language
    return isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'fr' ? 'fr' : 'en';
}

// English -> French for interface text. Keys are the exact English strings.
function cms_i18n_dict()
{
    return array(
        // navigation & footer
        'Projects' => 'Projets', 'Publications' => 'Publications', 'Talks' => 'Conférences', 'Blog' => 'Blog',
        'News' => 'Actualités', 'CV' => 'CV', 'Open source' => 'Open source', 'Contact' => 'Contact',
        'Get in touch' => 'Me contacter', "Let's talk" => 'Parlons', 'touch' => 'toucher',
        'Collaborations, student projects, talks, or robots that need a sense of touch.' => 'Collaborations, projets étudiants, conférences, ou robots qui ont besoin du sens du toucher.',
        'Contact page' => 'Page contact', 'Skip to content' => 'Aller au contenu',
        // homepage & sections
        'About' => 'À propos', 'Research' => 'Recherche', 'Selected work' => 'Travaux choisis', 'All projects' => 'Tous les projets',
        'Explore my work' => 'Découvrir mes travaux', 'All publications' => 'Toutes les publications', 'All news' => 'Toutes les actualités',
        'All posts' => 'Tous les articles', 'From the blog' => 'Sur le blog', 'Talks & media' => 'Conférences & médias',
        'All talks' => 'Toutes les conférences', 'Robots in 3D' => 'Robots en 3D', 'touch the skin' => 'touchez la peau',
        'Drag to rotate · scroll to zoom' => 'Glisser pour tourner · molette pour zoomer', 'View project' => 'Voir le projet',
        'Highlights' => 'Chiffres clés',
        // blog / news / projects
        'Notes from the lab.' => 'Carnet de labo.', 'Research notes, conference recaps and the stories behind the papers.' => 'Notes de recherche, comptes rendus de conférences et coulisses des articles.',
        "What’s new." => 'Quoi de neuf.', 'Papers, awards, talks and other updates.' => 'Articles, prix, conférences et autres nouvelles.',
        'Research and engineering, from silicone to software.' => 'Recherche et ingénierie, du silicone au logiciel.',
        'Tactile skins, haptic interfaces, medical robots, ROS 2 drivers and swarms. Pick a thread.' => 'Peaux tactiles, interfaces haptiques, robots médicaux, pilotes ROS 2 et essaims. Choisissez un fil.',
        'All' => 'Tout', 'min read' => 'min de lecture', 'Previous' => 'Précédent', 'Next' => 'Suivant', 'Next project' => 'Projet suivant',
        'Code on GitHub' => 'Code sur GitHub', 'Project link' => 'Lien du projet', 'read more' => 'lire la suite', 'open source' => 'open source',
        'No posts yet.' => 'Pas encore d’articles.', 'Featured' => 'À la une',
        // publications
        'Research output' => 'Production scientifique', 'Search title, author, venue…' => 'Chercher titre, auteur, conférence…',
        'Papers, patents and workshop contributions on tactile sensing, human–robot touch and medical robotics.' => 'Articles, brevets et contributions en ateliers sur la perception tactile, le toucher humain–robot et la robotique médicale.',
        'Abstract' => 'Résumé', 'Copy' => 'Copier', 'No publication matches your search.' => 'Aucune publication ne correspond à votre recherche.',
        'Journal' => 'Revue', 'Conference' => 'Conférence', 'Workshop' => 'Atelier', 'Patent' => 'Brevet', 'Thesis' => 'Thèse',
        'Preprint' => 'Prépublication', 'Talk' => 'Exposé', 'Other' => 'Autre',
        // CV
        'Curriculum vitae' => 'Curriculum vitae', 'Download CV (PDF)' => 'Télécharger le CV (PDF)', 'Experience' => 'Expérience',
        'Education' => 'Formation', 'Awards & honours' => 'Prix & distinctions', 'Skills' => 'Compétences', 'Selected projects' => 'Projets choisis',
        'Languages' => 'Langues', 'Interests' => 'Centres d’intérêt', 'All publications with abstracts and BibTeX' => 'Toutes les publications avec résumés et BibTeX',
        'Present' => 'Aujourd’hui',
        // talks
        'Talks, workshops & media.' => 'Conférences, ateliers & médias.', 'Slides' => 'Diapositives', 'Video' => 'Vidéo', 'Event page' => 'Page de l’événement',
        'Read the story' => 'Lire l’article', 'Play video' => 'Lire la vidéo', 'keynote' => 'keynote', 'talk' => 'exposé', 'paper' => 'article',
        'poster' => 'poster', 'workshop' => 'atelier', 'panel' => 'table ronde', 'press' => 'presse', 'podcast' => 'podcast', 'video' => 'vidéo',
        'Invited talks, conference presentations, workshops I organised and media coverage.' => 'Conférences invitées, présentations, ateliers organisés et couverture médiatique.',
        // open source
        'Code that makes robots feel.' => 'Du code qui donne des sensations aux robots.', 'Selected repositories.' => 'Dépôts choisis.',
        'Recent activity.' => 'Activité récente.', 'public repositories' => 'dépôts publics', 'languages in use' => 'langages utilisés',
        'stars across projects' => 'étoiles au total', 'since the last push' => 'depuis le dernier push', 'Follow on GitHub' => 'Suivre sur GitHub',
        'followers' => 'abonnés', 'on GitHub' => 'sur GitHub', 'Sort' => 'Trier', 'Curated' => 'Sélection', 'Recently updated' => 'Récemment mis à jour',
        'Most stars' => 'Plus d’étoiles', 'Name' => 'Nom', 'All repositories on GitHub' => 'Tous les dépôts sur GitHub',
        // contact page
        'Email' => 'E-mail', 'Office' => 'Bureau', 'Elsewhere' => 'Ailleurs', 'Write to me' => 'M’écrire', 'Copy address' => 'Copier l’adresse',
        'Copied!' => 'Copié !',
        'Interested in tactile interaction, social robotics, a collaboration or an internship? Drop me a line.' => 'L’interaction tactile, la robotique sociale, une collaboration ou un stage vous intéressent ? Écrivez-moi.',
        'I usually reply within a few days.' => 'Je réponds en général sous quelques jours.', 'Open in Maps' => 'Ouvrir dans Maps',
        'Interested in tactile interaction, social robotics, a collaboration or an internship? Drop me a line. I usually reply within a few days.' => 'Intéressé·e par l’interaction tactile, la robotique sociale, une collaboration ou un stage ? Écrivez-moi, je réponds en général sous quelques jours.',
        'Not found' => 'Page introuvable',
        'That page does not exist (anymore).' => 'Cette page n’existe pas (ou plus).', 'Back to the blog' => 'Retour au blog',
        'Posts' => 'Articles', 'No news so far.' => 'Pas encore d’actualités.', 'No projects yet.' => 'Pas encore de projets.',
        'No talks yet.' => 'Pas encore d’interventions.', 'Undated' => 'Sans date', 'Upcoming' => 'À venir',
        'Research and engineering projects by' => 'Projets de recherche et d’ingénierie de', 'Publications by' => 'Publications de',
        'Open-source repositories by' => 'Dépôts open source de', 'Talks, workshops and media by' => 'Conférences, ateliers et médias de',
        'PDF' => 'PDF', 'Code' => 'Code',
        'ROS 2 drivers, robot descriptions, embedded firmware and research tools, live from GitHub.' => 'Pilotes ROS 2, descriptions de robots, firmware embarqué et outils de recherche, en direct de GitHub.',
        // talk kinds & publication types (labels)
        'Keynote' => 'Keynote', 'Paper presentation' => 'Présentation d’article', 'Poster' => 'Poster', 'Panel' => 'Table ronde',
        'Press' => 'Presse', 'Podcast' => 'Podcast',
        // homepage: recruiter strip, Now panel, 3D
        'Now' => 'En ce moment', 'Currently working on' => 'En cours', 'Read more' => 'En savoir plus',
        'Next talk' => 'Prochaine intervention', 'Latest talk' => 'Dernière intervention', 'Latest commit' => 'Dernier commit',
        'On GitHub' => 'Sur GitHub', 'Demo robot' => 'Robot de démonstration', '3D demo' => 'Démo 3D', '3D model' => 'Modèle 3D',
        'View in your space' => 'Voir chez vous (RA)',
        'A placeholder robot built from cubes, to show how the 3D viewer works: drag it, zoom, or open it in augmented reality on a phone. My own robot designs will appear here.' => 'Un robot provisoire fait de cubes, pour montrer le fonctionnement de la visionneuse 3D : faites-le tourner, zoomez, ou ouvrez-le en réalité augmentée sur un téléphone. Mes propres robots apparaîtront ici.',
    );
}

function cms_t($s)
{
    if (cms_lang() !== 'fr') return $s;
    $d = cms_i18n_dict();
    return isset($d[$s]) ? $d[$s] : $s;
}

// date() with French month/day names when the visitor reads in French.
function cms_date($fmt, $ts)
{
    $out = date($fmt, $ts);
    if (cms_lang() !== 'fr') return $out;
    static $map = array(
        'January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril', 'May' => 'mai', 'June' => 'juin',
        'July' => 'juillet', 'August' => 'août', 'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre',
        'Jan' => 'janv.', 'Feb' => 'févr.', 'Mar' => 'mars', 'Apr' => 'avr.', 'Jun' => 'juin', 'Jul' => 'juil.', 'Aug' => 'août',
        'Sep' => 'sept.', 'Oct' => 'oct.', 'Nov' => 'nov.', 'Dec' => 'déc.',
    );
    return preg_replace_callback('/\b[A-Z][a-z]+\b/', function ($m) use ($map) { return isset($map[$m[0]]) ? $map[$m[0]] : $m[0]; }, $out);
}

// Items may carry French versions of their text: title_fr, description_fr, body_fr.
function cms_localize(array $item)
{
    if (cms_lang() !== 'fr' || empty($item['meta'])) return $item;
    foreach (array('title', 'description', 'event', 'location', 'award', 'category') as $k) {
        if (!empty($item['meta'][$k . '_fr'])) $item[$k] = (string) $item['meta'][$k . '_fr'];
    }
    if (!empty($item['meta']['body_fr'])) $item['body'] = (string) $item['meta']['body_fr'];
    return $item;
}
