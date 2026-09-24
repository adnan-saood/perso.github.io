# Site admin (PHP CMS)

The site is still built with Jekyll (layout, publications, CV, projects), but
**projects, blog posts, news and files are managed live** at

    https://perso.ensta.fr/~saood/admin/

Changes there are online immediately. No rebuild, no upload.

| What | Where it lives on the server | Edited from |
|---|---|---|
| Projects, blog posts, news | `~/cms-data/projects/`, `posts/`, `news/` (Markdown) | Admin → Projects / Blog posts / News |
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

## URLs

- Blog: `/blog/`, a post: `/blog/?p=<slug>`, filters: `/blog/?tag=HRI`, `?year=2026`
- Projects: `/projects/`, a project: `/projects/?p=<slug>`
- News: `/news/`, a news page: `/news/?n=<slug>`
- RSS feed: `/cms/feed.php`
- Contact page (your email, office, profiles): `/contact/`
- Old Jekyll URLs (`/blog/2026/<slug>/`, `/projects/1_project/`, ...) redirect automatically (pages in `_pages/legacy/`).

## Backups

Everything you create lives in `~/cms-data` and `~/public_html/files`. To take a copy
(tar will warn that it cannot read `settings.json`; that is expected, it only holds the password hash):

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
