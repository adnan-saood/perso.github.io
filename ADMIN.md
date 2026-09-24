# Site admin (PHP CMS)

The site is still built with Jekyll (layout, publications, CV, projects), but
**blog posts, news, contact messages and files are managed live** at

    https://perso.ensta.fr/~saood/admin/

Changes there are online immediately. No rebuild, no upload.

| What | Where it lives on the server | Edited from |
|---|---|---|
| Blog posts, news | `~/cms-data/posts/*.md`, `~/cms-data/news/*.md` | Admin → Blog posts / News |
| Contact messages | `~/cms-data/messages/*.json` (+ optional email) | Admin → Messages |
| Uploaded files & images | `~/public_html/files/` | Admin → Files, or drag images into the editor |
| Password, email settings | `~/cms-data/settings.json` | Admin → Settings |
| Previous versions / deleted items | `~/cms-data/history/`, `~/cms-data/trash/` | Admin → Trash (restore) |
| Everything else (pages, CV, publications, look) | this repo | edit + `jekyll build` + `./deploy.sh` |

`cms-data` sits **outside** `public_html`, so a deploy can never overwrite what
you wrote in the admin panel.

## First-time setup

1. **Remove the old insecure admin now** (it had a hardcoded password and could write any file):
   ```bash
   ssh -J saood@relais.ensta.fr saood@salle.ensta.fr rm -f public_html/simple-admin.php
   ```
2. **Check the server** once: upload `tools/server-check.php`, open
   `https://perso.ensta.fr/~saood/server-check.php?key=07f837cacfc568e82d364778`,
   note the *PHP version* and *Runs as user* lines, then delete the file.
   PHP 7.2+ is required.
3. **Build and deploy** the site as usual (`bundle exec jekyll build`, then `./deploy.sh`).
   The deploy also deletes stale `blog/index.html`, `news/index.html` and the old
   Netlify `admin/index.html`, which would otherwise hide the new PHP pages.
4. **Create the data folder** and import the existing posts/news (from `cms-seed/`):
   ```bash
   PHP_USER=www-data ./deploy.sh init     # use the "Runs as user" value from step 2
   ```
   It prints a one-time **setup token**.
5. Open `/admin/`, paste the token, choose a password (12+ characters). Done.
6. In **Settings**, enter the address that should receive contact-form emails and
   press *Send test email*. If the university mail server refuses, messages are
   still collected in the admin inbox.

## Everyday use

- **New news item**: Dashboard → *+ News*. Tick *Short item* for one-liners shown
  directly in the list; untick it to give the item its own page with a headline.
- **New blog post**: Dashboard → *+ Blog post*. Drag and drop or paste images into the
  editor; they are uploaded to `files/uploads/<year>/`. *Pin as featured* shows the
  post as a card on top of the blog.
- **Drafts and scheduling**: tick *Draft* to hide a post. A future date keeps a post
  hidden until that day.
- **Files**: upload by dropping files on the page; create folders, rename, delete
  (goes to Trash). Links are `https://perso.ensta.fr/~saood/files/...`.
  HTML/SVG/JS/PHP uploads are refused on purpose, because they would run on your site.
- **Messages**: new messages show a badge. *Reply by email* opens your mail client.

## URLs

- Blog: `/blog/`, a post: `/blog/?p=<slug>`, filters: `/blog/?tag=HRI`, `?year=2026`
- News: `/news/`, a news page: `/news/?n=<slug>`
- RSS feed: `/cms/feed.php`
- Contact form: `/contact/`
- Old Jekyll URLs (`/blog/2026/<slug>/`) redirect automatically (pages in `_pages/legacy/`).

## Backups

Everything you create lives in `~/cms-data` and `~/public_html/files`. To take a copy:

```bash
ssh -J saood@relais.ensta.fr saood@salle.ensta.fr "tar czf - cms-data public_html/files" > site-backup-$(date +%F).tgz
```

## How it fits together

- `cms/` is the engine (plain PHP 7.2+, no database): `lib/core.php` (content store,
  Markdown via Parsedown), `lib/auth.php` (login, CSRF, throttling),
  `lib/messages.php`, `lib/files.php`, `view.php` (public pages), `api.php`
  (homepage fragments + contact endpoint), `feed.php`.
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
- `perso.ensta.fr` hosts many users on the same domain, and PHP typically runs as a
  shared user. Log out when done, and keep regular backups.
