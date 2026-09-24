"""One-time migration of Jekyll _projects/ into the PHP CMS (cms-seed/projects/).

Also writes redirect pages so the old /projects/<name>/ URLs keep working.
Usage:  python tools/migrate_projects.py
"""
import pathlib
import re

from migrate_to_cms import FIGURE, convert_body, legacy_page, split_front_matter, BASE

ROOT = pathlib.Path(__file__).resolve().parent.parent
OUT = ROOT / "cms-seed" / "projects"
LEGACY = ROOT / "_pages" / "legacy"

# old file stem -> (new slug, category, importance, featured on homepage)
PLAN = {
    "1_project": ("tactile-array", "Tactile sensing", 1, True),
    "2_project": ("tactile-affordance", "Tactile sensing", 2, True),
    "3_project": ("bbb-opening", "Medical robotics & imaging", 3, True),
    "4_project": ("handshake", "Human–robot interaction", 4, True),
    "5_project": ("haptic-interface", "Human–robot interaction", 5, False),
    "ndisys_ros2": ("ndisys-ros2", "Robotics software", 6, False),
    "swarm_dynamics": ("swarm-dynamics", "Swarm robotics", 7, False),
    "swarm_robot_firmware": ("swarm-firmware", "Swarm robotics", 8, False),
    "3d_gradient_path_planner": ("3d-path-planner", "Robotics software", 9, False),
    "covid_dl_published": ("covid-segmentation", "Medical robotics & imaging", 10, False),
}

KEEP = ("title", "description", "img", "github")


def main():
    OUT.mkdir(parents=True, exist_ok=True)
    LEGACY.mkdir(parents=True, exist_ok=True)
    for src in sorted((ROOT / "_projects").glob("*.md")):
        slug, category, importance, featured = PLAN[src.stem]
        fm, body = split_front_matter(src.read_text(encoding="utf-8"))
        meta = {}
        for line in fm.splitlines():
            m = re.match(r"^(\w+):\s*(.*)$", line)
            if m and m.group(1) in KEEP:
                meta[m.group(1)] = m.group(2).strip()
        img = meta.get("img", "")

        # The project page shows the cover image in its header; drop a leading
        # figure that repeats it (and its wrapping row/col divs, if any).
        first = FIGURE.search(body)
        if first and img and img in first.group(1) and first.start() < 400:
            block = re.search(r"(<div class=\"row[^\"]*\">\s*)?(<div class=\"col[^\"]*\">\s*)?" + re.escape(first.group(0))
                              + r"(\s*</div>)?(\s*</div>)?", body)
            if block and block.group(0).count("<div") == block.group(0).count("</div>"):
                body = body.replace(block.group(0), "", 1)
            else:
                body = body.replace(first.group(0), "", 1)
        body, left = convert_body(body)

        lines = ["---"]
        for k in ("title", "description", "img", "github"):
            if meta.get(k):
                lines.append(f"{k}: {meta[k]}")
        lines += [f"category: \"{category}\"", f"importance: {importance}"]
        if featured:
            lines.append("featured: true")
        lines.append("---")
        (OUT / f"{slug}.md").write_text("\n".join(lines) + "\n\n" + body.strip() + "\n", encoding="utf-8", newline="\n")
        (LEGACY / f"project-{slug}.html").write_text(
            legacy_page(f"/projects/{src.stem}/", f"{BASE}projects/?p={slug}"), encoding="utf-8", newline="\n")
        print(f"{src.name} -> projects/{slug}.md" + (f"  (unconverted Liquid: {left})" if left else ""))


if __name__ == "__main__":
    main()
