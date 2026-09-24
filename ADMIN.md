# Site admin (PHP CMS)

The site is still built with Jekyll (layout, publications, CV, projects), but
**projects, blog posts, news and files are managed live** at

    https://perso.ensta.fr/~saood/admin/

Changes there are online immediately. No rebuild, no upload.

| What | Where it lives on the server | Edited from |
|---|---|---|
| Projects, blog posts, news | `~/cms-data/projects/`, `posts/`, `news/` (Markdown) | Admin → Projects / Blog posts / News |
| Publications | `~/cms-data/publications/*.md` | Admin → Publications |
| CV (and the downloadable PDF) | `~/cms-data/cv.json` | Admin → CV |
| Repositories page | `~/cms-data/repositories.json` | Admin → Repositories |
| Visitor statistics | `~/cms-data/stats/` (one file per day, kept 400 days) | Admin → Analytics |
| Uploaded files & images | `~/public_html/files/` | Admin → Files, or drag images into the editor |
| Admin password | `~/cms-data/settings.json` (readable by PHP only) | Admin → Settings |
| Previous versions / deleted items | `~/cms-data/history/`, `~/cms-data/trash/` | Admin → Trash (restore) |
| Homepage wording (hero, about, research pillars, stats) | `_pages/about.md` front matter | edit + build + `./deploy.sh` |
| Everything else (CV, publications, look) | this repo | edit + `jekyll build` + `./deploy.sh` |

`cms-data` sits **outside** `public_html`, so a deploy can never overwrite what
you wrote in the admin panel.

## First-time setup

1. **Remove the old insecure admin now** (it had a hardcoded password and could write any file):
   ```bash
   ssh -J saood@relais.ensta.fr saood@salle.ensta.fr rm -f public_html/simple-admin.php
   ```
2. The server was checked on 2026-09-24: PHP 8.4 (FPM) running as `www-data`, no
   local mail. `tools/server-check.php` can re-run the check if the server changes.
3. **Build and deploy** the site as usual (`bundle exec jekyll build`, then `./deploy.sh`).
   The deploy also deletes stale `blog/index.html`, `news/index.html` and the old
   Netlify `admin/index.html`, which would otherwise hide the new PHP pages.
4. **Create the data folder** and import the existing posts/news (from `cms-seed/`):
   ```bash
   ./deploy.sh init
   ```
   It prints a one-time **setup token**.
5. Open `/admin/`, paste the token, choose a password (12+ characters). Done.

## Everyday use

- **Projects**: Dashboard → *+ Project*. Give it a category (becomes a filter button on
  the Projects page), a cover image (upload button), an order number (1 = first) and
  optional GitHub / other links. Tick *Show on homepage* to feature it in "Selected work".
- **Publications**: *Import from BibTeX* (paste one entry) or *+ New publication*. Pick a
  picture or animated GIF with *Library* / *Upload*, tick *Show on homepage*, and drag the
  list under *On the homepage* to set the order (press *Save order*). Your name is
  highlighted automatically; abstract and BibTeX get toggles on the public page.
- **CV**: Admin → CV. Edit the header and each section (add, remove, ↑↓ to reorder).
  *Upload new PDF* sets the file behind the “Download CV” button. The CV's publication
  list is built from Publications automatically.
- **Repositories**: add `owner/name` (your GitHub repos are suggested), drag to reorder,
  choose *Large / Normal / Compact*, set tags (filter buttons) and untick *Shown* to hide.
- **Analytics**: visitors, page views, time on page, top pages, sources (incl.
  `?utm_source=` links), devices, browsers, time zones. No cookies, no IPs stored; your
  own visits are excluded in any browser where you've opened the admin.
- **Homepage**: Admin → Homepage. *Page layout* (top of the page) lists the sections under
  the hero: drag them to reorder, untick *Shown* to hide one. Edit the hero title (wrap a word in `*stars*` to give it
  the gradient), intro, about text, the scrolling keywords strip, the research pillars,
  highlight numbers and every section title. The *Français* tab holds the French
  versions; any field left empty there falls back to English.
  The same page holds the **recruiter strip** (availability with a green dot, what you're
  looking for, CV · Email · Scholar buttons), the **proof badges** under the hero, the
  **Now panel** (your current work + your next talk, picked automatically, + your latest
  public GitHub commit) and the **Robots in 3D** switch that shows a demo robot made of
  cubes until one of your projects has a 3D model on the homepage.
- **Talks & media**: Dashboard → *+ Talk*. Pick the kind (talk, paper presentation,
  poster, workshop, media...), event, place, and paste a YouTube/Vimeo link: the video
  only loads when a visitor presses play. Slides/paper/code links become buttons;
  *Featured* shows the talk on the homepage.
- **3D robots**: export your model as **GLB** (Blender: File → Export → glTF 2.0, format
  *glTF Binary*; SolidWorks/Fusion: export STEP/OBJ, open in Blender, export GLB). Keep it
  under ~15 MB (Blender's *Decimate* modifier helps). In the project editor, upload it in
  *3D model*; the project page then shows an interactive viewer (drag to rotate, AR on
  phones), and *Show in 3D on homepage* adds it to the “Robots in 3D” showcase.
- **French / English**: the FR/EN button in the menu switches the site. Each post,
  news item, project and talk has a *Français* box (title, summary, text); anything left
  empty is shown in English. The CV has a *Français* box in its header and in every entry, and
  the homepage has its own *Français* tab. Paper titles stay in English on purpose.
- **Share images**: when you save a post, project, news item or talk, the admin draws a
  1200×630 preview card (title + cover) and uses it when the page is shared on LinkedIn,
  X, WhatsApp... Stored in `files/og/`.
- **New news item**: Dashboard → *+ News*. Tick *Short item* for one-liners shown
  directly in the list; untick it to give the item its own page with a headline.
- **New blog post**: Dashboard → *+ Blog post*. Drag and drop or paste images into the
  editor; they are uploaded to `files/uploads/<year>/`. *Pin as featured* shows the
  post as a card on top of the blog.
- **Drafts and scheduling**: tick *Draft* to hide a post. A future date keeps a post
  hidden until that day.
- **Pictures in posts and projects**: in the editor, click the folder icon in the toolbar
  (*Insert from media library*) to browse your Files folders, drop new pictures in, and
  click one to insert it. Cover-image fields have a *Library* button that does the same.
  On the Files page, *Copy Markdown* gives a snippet you can paste into any post.
  Both have two libraries: **Uploads** (what you add through the admin, in `files/`) and
  **Site images** (the pictures that ship with the site in `assets/img/`, read-only —
  change those in the repository and redeploy).
- **Files**: upload by dropping files on the page; create folders, rename, delete
  (goes to Trash). Links are `https://perso.ensta.fr/~saood/files/...`.
  HTML/SVG/JS/PHP uploads are refused on purpose, because they would run on your site.
- **Uploads**: the server allows 2 MB per request by default; `admin/.user.ini` raises
  it to 64 MB for the admin panel, and the editor also shrinks big photos in your
  browser before uploading. Settings → Server status shows the limit actually in effect.

## Previewing locally

```bash
docker compose up
```

Open http://localhost:8080. Jekyll rebuilds on every save, and a PHP 8.4 container
(same version as perso.ensta.fr) serves the result, so the blog, news and `/admin/`
work like on the server. Admin setup token for the preview: `preview-setup-token-local`;
preview edits go to `.preview-data/` (delete it to start over from `cms-seed/`).
Jekyll's own server, without PHP, is still on port 4000.

Without Docker: `bundle exec jekyll build`, then `bash tools/preview.sh` (needs `php`).

## Starting content (cms-seed/)

`cms-seed/` holds the site's starting content: posts, news, projects, publications, talks,
CV, repositories and homepage texts (with their French versions). The CMS imports it by
itself: anything you don't have yet is added, and when a seed file changes (e.g. new
translations) only the fields your copy is missing are filled in. Nothing you edited is
overwritten and deleted items don't come back (`cms-data/state/seeded.json` keeps track).
`deploy.sh` uploads it to `~/cms-data/_seed` on every deploy; the local preview reads it
straight from the repository.

## URLs

- Blog: `/blog/`, a post: `/blog/?p=<slug>`, filters: `/blog/?tag=HRI`, `?year=2026`
- Projects: `/projects/`, a project: `/projects/?p=<slug>`
- News: `/news/`, a news page: `/news/?n=<slug>`
- Talks: `/talks/`, one talk: `/talks/#<slug>`
- RSS feed: `/cms/feed.php`
- Contact page (your email, office, profiles): `/contact/`
- Old Jekyll URLs (`/blog/2026/<slug>/`, `/projects/1_project/`, ...) redirect automatically (pages in `_pages/legacy/`).

## Backups

Settings → *Backup & restore*: **Download backup** gives one `.tar` file with all your
content, settings and uploads. **Restore** uploads such a file and puts everything back
(the state just before the restore is kept in `~/cms-data/backups/`, so a wrong restore can
be undone). Take one before big changes and now and then.

Everything you create lives in `~/cms-data` and `~/public_html/files`. To take a copy
from the command line instead (tar will warn that it cannot read `settings.json`; that is
expected, it only holds the password hash):

```bash
ssh -J saood@relais.ensta.fr saood@salle.ensta.fr "tar czf - cms-data public_html/files" > site-backup-$(date +%F).tgz
```

## How it fits together

- `cms/` is the engine (plain PHP 7.2+, no database): `lib/core.php` (content store,
  Markdown via Parsedown), `lib/auth.php` (login, CSRF, throttling),
  `lib/files.php`, `view.php` (public pages), `api.php` (homepage fragments),
  `feed.php`. `lib/Parsedown.php` has a small PHP 8.4 compatibility patch (see its header).
- `admin/` is the panel (`index.php`, `admin.css`, `admin.js`, EasyMDE editor in `admin/lib/`).
- `_pages/blog.php` and `_pages/news.php` are Jekyll pages that output PHP: Jekyll
  renders the normal theme around them and PHP fills in the content at request time
  (see the `cms_view` hook at the top of `_layouts/default.liquid`).
- The homepage news box and "latest posts" are filled by `assets/js/cms.js`.
- `cms/config.local.php` (optional, not committed) can override paths:
  ```php
  <?php return array('data_dir' => '/somewhere/else/cms-data');
  ```

## Security notes

- Login is rate-limited (5 failures per 15 minutes per IP), sessions expire after
  2 h idle, and changing the password logs out all other sessions.
- PHP runs as the shared `www-data` user, so `deploy.sh init` grants it access to
  `cms-data` and `files/` with ACLs (or, if the filesystem has none, makes them
  world-writable and says so). `settings.json` is always written as 0600.
- `perso.ensta.fr` hosts many users on the same domain. Log out when done, and keep
  regular backups.
