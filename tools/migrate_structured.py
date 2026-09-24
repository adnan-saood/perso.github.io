"""One-time migration of Jekyll-built data into the PHP CMS.

  _bibliography/papers.bib      -> cms-seed/publications/<key>.md
  assets/json/resume.json       -> cms-seed/cv.json            (publications come from the CMS)
  _data/repositories.yml        -> cms-seed/repositories.json

Usage:  python tools/migrate_structured.py
"""
import json
import pathlib
import re

import yaml

ROOT = pathlib.Path(__file__).resolve().parent.parent
SEED = ROOT / "cms-seed"

# --------------------------------------------------------------------------- BibTeX
LATEX = {r"{\'i}": "í", r"{\'a}": "á", r"{\'e}": "é", r"{\'o}": "ó", r"{\"u}": "ü", r"{\"o}": "ö", "~": " ", "--": "–"}
CUSTOM = {"abbr", "selected", "preview", "bibtex_show", "abstract", "altmetric", "dimensions", "google_scholar_id",
          "keywords", "pdf", "code", "video", "slides", "poster", "html", "award", "website", "supp"}


def delatex(s):
    for k, v in LATEX.items():
        s = s.replace(k, v)
    return re.sub(r"[{}]", "", s).strip()


def parse_bib(text):
    entries = []
    i = 0
    while True:
        m = re.compile(r"@(\w+)\s*\{\s*([^,\s]+)\s*,").search(text, i)
        if not m:
            break
        etype, key = m.group(1).lower(), m.group(2)
        j, depth = m.end(), 1
        while depth and j < len(text):
            depth += {"{": 1, "}": -1}.get(text[j], 0)
            j += 1
        body = text[m.end():j - 1]
        i = j
        if etype in ("string", "comment", "preamble"):
            continue
        fields, k = {}, 0
        while k < len(body):
            fm = re.compile(r"\s*(\w+)\s*=\s*").match(body, k)
            if not fm:
                break
            name, k = fm.group(1).lower(), fm.end()
            if body[k] == "{":
                d, s = 0, k
                while True:
                    d += {"{": 1, "}": -1}.get(body[k], 0)
                    k += 1
                    if d == 0:
                        break
                value = body[s + 1:k - 1]
            elif body[k] == '"':
                e = body.index('"', k + 1)
                value, k = body[k + 1:e], e + 1
            else:
                e = re.compile(r"[,\n]").search(body, k)
                e = e.start() if e else len(body)
                value, k = body[k:e].strip(), e
            fields[name] = value
            k = body.find(",", k) + 1 if body.find(",", k) != -1 else len(body)
        entries.append((etype, key, fields, text[m.start():j]))
    return entries


def people(authors):
    out = []
    for a in re.split(r"\s+and\s+", delatex(authors)):
        a = a.strip()
        if "," in a:
            last, first = [x.strip() for x in a.split(",", 1)]
            a = f"{first} {last}"
        a = " ".join(w.capitalize() if len(w) > 1 and w.isupper() else w for w in a.split())
        out.append(a)
    return out


def title_case_if_shouting(s):
    letters = [c for c in s if c.isalpha()]
    if letters and sum(c.isupper() for c in letters) / len(letters) > 0.8:
        small = {"and", "of", "the", "with", "a", "an", "along", "for", "on", "in", "to"}
        words = s.lower().split()
        return " ".join(w if (i and w in small) else w.capitalize() for i, w in enumerate(words))
    return s


def clean_bibtex(raw):
    lines = [l for l in raw.splitlines() if not re.match(r"\s*(" + "|".join(CUSTOM) + r")\s*=", l)]
    return "\n".join(lines).replace(",\n}", "\n}")


def yaml_str(v):
    return json.dumps(v, ensure_ascii=False)


def migrate_publications():
    out = SEED / "publications"
    out.mkdir(parents=True, exist_ok=True)
    order = 0
    for etype, key, f, raw in parse_bib((ROOT / "_bibliography" / "papers.bib").read_text(encoding="utf-8")):
        venue = delatex(f.get("journal") or f.get("booktitle") or f.get("publisher") or "")
        badge = f.get("abbr", "")
        kind = {"article": "journal", "inproceedings": "conference", "incollection": "conference",
                "phdthesis": "thesis", "mastersthesis": "thesis"}.get(etype, "other")
        if "patent" in (badge + f.get("note", "")).lower():
            kind = "patent"
        elif "workshop" in venue.lower():
            kind = "workshop"
        award = ""
        if " - " in badge:  # e.g. "ICASSDA - Best Paper Award"
            badge, award = [x.strip() for x in badge.split(" - ", 1)]
        doi = f.get("doi", "")
        doi = re.sub(r"^https?://(dx\.)?doi\.org/", "", doi)
        meta = {
            "title": title_case_if_shouting(delatex(f.get("title", ""))),
            "authors": people(f.get("author", "")),
            "venue": venue,
            "year": f.get("year", ""),
            "pubtype": kind,
            "badge": badge,
            "award": award,
            "note": delatex(f.get("note", "")),
            "doi": doi,
            "url": f.get("url", "") or f.get("html", ""),
            "pdf": f.get("pdf", ""),
            "code": f.get("code", ""),
            "image": ("assets/img/publication_preview/" + f["preview"]) if f.get("preview") else "",
            "bibtex": clean_bibtex(raw),
        }
        if f.get("selected", "").lower() == "true":
            order += 1
            meta["selected"] = True
            meta["home_order"] = order
        lines = ["---"]
        for k, v in meta.items():
            if v in ("", [], None):
                continue
            if isinstance(v, bool):
                lines.append(f"{k}: {'true' if v else 'false'}")
            elif isinstance(v, int):
                lines.append(f"{k}: {v}")
            elif isinstance(v, list):
                lines.append(f"{k}: [" + ", ".join(yaml_str(x) for x in v) + "]")
            else:
                lines.append(f"{k}: {yaml_str(v)}")
        lines.append("---")
        slug = re.sub(r"[^a-z0-9]+", "-", key.lower()).strip("-")
        abstract = delatex(f.get("abstract", ""))
        (out / f"{slug}.md").write_text("\n".join(lines) + "\n\n" + abstract + "\n", encoding="utf-8", newline="\n")
        print(f"publication  {key} -> publications/{slug}.md" + ("  [homepage #%d]" % order if meta.get("selected") else ""))


def migrate_cv():
    cv = json.loads((ROOT / "assets" / "json" / "resume.json").read_text(encoding="utf-8"))
    cv.pop("publications", None)  # rendered from the Publications section instead
    (SEED / "cv.json").write_text(json.dumps(cv, indent=2, ensure_ascii=False) + "\n", encoding="utf-8", newline="\n")
    print("cv           resume.json -> cv.json (" + ", ".join(k for k, v in cv.items() if v) + ")")


def migrate_repositories():
    data = yaml.safe_load((ROOT / "_data" / "repositories.yml").read_text(encoding="utf-8"))
    repos = []
    for i, r in enumerate(data.get("github_repos", [])):
        r = r if isinstance(r, dict) else {"repo": r}
        repos.append({"repo": r["repo"], "tags": r.get("tags", []), "note": r.get("note", ""),
                      "size": "large" if i < 2 else "normal", "visible": True})
    doc = {"user": (data.get("github_users") or ["adnan-saood"])[0], "repos": repos}
    (SEED / "repositories.json").write_text(json.dumps(doc, indent=2, ensure_ascii=False) + "\n", encoding="utf-8", newline="\n")
    print(f"repositories {len(repos)} repos -> repositories.json")


if __name__ == "__main__":
    migrate_publications()
    migrate_cv()
    migrate_repositories()
