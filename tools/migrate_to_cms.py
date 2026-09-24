"""One-time migration of Jekyll _posts/ and _news/ into the PHP CMS format.

Writes:
  cms-seed/posts/<slug>.md   and   cms-seed/news/<slug>.md
      Markdown with front matter; Liquid figure includes become plain HTML.
  _pages/legacy/*.html
      Tiny redirect pages so old /blog/<year>/<slug>/ and /news/<name>/ URLs keep working.

Usage:  python tools/migrate_to_cms.py
Then upload cms-seed/ to the server as cms-data/ (deploy.sh init does this).
"""
import pathlib
import re
import shlex
import unicodedata

ROOT = pathlib.Path(__file__).resolve().parent.parent
BASE = "/~saood/"
SEED = ROOT / "cms-seed"
LEGACY = ROOT / "_pages" / "legacy"

FIGURE = re.compile(r"{%-?\s*include\s+figure\.liquid\s+(.*?)-?%}", re.S)


def slugify(s):
    s = unicodedata.normalize("NFKD", s).encode("ascii", "ignore").decode()
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")[:80]


def split_front_matter(text):
    m = re.match(r"^---\n(.*?)\n---\n?(.*)$", text.replace("\r\n", "\n"), re.S)
    return (m.group(1), m.group(2)) if m else ("", text)


def figure_html(match):
    args = dict(kv.split("=", 1) for kv in shlex.split(match.group(1)) if "=" in kv)
    src = args.get("url") or args.get("path", "").lstrip("/")  # base-free; the CMS adds the base
    title = args.get("title", "")
    cls = args.get("class", "img-fluid rounded z-depth-1")
    caption = args.get("caption", "")
    html = f'<figure><img src="{src}" class="{cls}" alt="{title}" title="{title}" loading="lazy">'
    if caption:
        html += f'<figcaption class="caption">{caption}</figcaption>'
    return html + "</figure>"


def convert_body(body):
    body = FIGURE.sub(figure_html, body)
    body = body.replace("{{ site.baseurl }}/", BASE).replace("{{site.baseurl}}/", BASE)
    leftovers = re.findall(r"{%.*?%}|{{.*?}}", body)
    return body, leftovers


def legacy_page(old_url, new_url):
    return (
        "---\n"
        f"permalink: {old_url}\n"
        "layout: null\n"
        "sitemap: false\n"
        "---\n"
        '<!doctype html><html lang="en"><head><meta charset="utf-8">'
        f'<meta http-equiv="refresh" content="0; url={new_url}">'
        f'<link rel="canonical" href="{new_url}"><meta name="robots" content="noindex">'
        f'<title>Moved</title></head><body><a href="{new_url}">This page moved here.</a></body></html>\n'
    )


def main():
    report = []
    LEGACY.mkdir(parents=True, exist_ok=True)

    for src in sorted((ROOT / "_posts").glob("*.md")):
        fm, body = split_front_matter(src.read_text(encoding="utf-8"))
        m = re.match(r"(\d{4})-\d{2}-\d{2}-(.+)$", src.stem)
        year, slug = (m.group(1), slugify(m.group(2))) if m else ("", slugify(src.stem))
        body, left = convert_body(body)
        out = SEED / "posts" / f"{slug}.md"
        out.parent.mkdir(parents=True, exist_ok=True)
        out.write_text(f"---\n{fm}\n---\n\n{body.lstrip()}", encoding="utf-8", newline="\n")
        if year:
            (LEGACY / f"post-{slug}.html").write_text(
                legacy_page(f"/blog/{year}/{slug}/", f"{BASE}blog/?p={slug}"), encoding="utf-8", newline="\n")
        report.append(f"post  {src.name} -> posts/{slug}.md" + (f"  (unconverted Liquid: {left})" if left else ""))

    for src in sorted((ROOT / "_news").glob("*.md")):
        fm, body = split_front_matter(src.read_text(encoding="utf-8"))
        slug = slugify(src.stem.replace("announcement_", ""))
        body, left = convert_body(body)
        out = SEED / "news" / f"{slug}.md"
        out.parent.mkdir(parents=True, exist_ok=True)
        out.write_text(f"---\n{fm}\n---\n\n{body.lstrip()}", encoding="utf-8", newline="\n")
        (LEGACY / f"news-{slug}.html").write_text(
            legacy_page(f"/news/{src.stem}/", f"{BASE}news/?n={slug}"), encoding="utf-8", newline="\n")
        report.append(f"news  {src.name} -> news/{slug}.md" + (f"  (unconverted Liquid: {left})" if left else ""))

    print("\n".join(report))
    print(f"\n{len(report)} items written to {SEED}")


if __name__ == "__main__":
    main()
