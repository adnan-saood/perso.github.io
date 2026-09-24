"""One-time migration: homepage texts -> cms-seed/home.json, talks -> cms-seed/talks/*.md.

Usage:  python tools/migrate_home_talks.py
"""
import json
import pathlib
import re

import yaml

ROOT = pathlib.Path(__file__).resolve().parent.parent
SEED = ROOT / "cms-seed"


def migrate_home():
    text = (ROOT / "_pages" / "about.md").read_text(encoding="utf-8")
    fm = yaml.safe_load(re.match(r"^---\n(.*?)\n---", text.replace("\r\n", "\n"), re.S).group(1))
    title = re.sub(r'<span class="text-gradient">(.*?)</span>', r"*\1*", fm["hero"]["title"])
    en = {
        "hero": {"kicker": fm["hero"]["kicker"], "title": title, "lede": fm["hero"]["lede"]},
        "about": {"image": fm["about"]["image"].lstrip("/"), "lead": fm["about"]["lead"], "body": fm["about"]["body"]},
        "facts": fm["facts"],
        "keywords": fm["keywords"],
        "research_title": fm["research_title"],
        "pillars": fm["pillars"],
        "robots_title": "Robots I've designed, in 3D.",
        "work_title": "Things I've built, measured and shipped.",
        "stats": [{"value": str(s["value"]), "label": s["label"]} for s in fm["stats"]],
        "papers_title": "Selected papers.",
        "talks_title": "Talks, workshops & media.",
    }
    doc = {"en": en, "fr": {}}
    (SEED / "home.json").write_text(json.dumps(doc, indent=2, ensure_ascii=False) + "\n", encoding="utf-8", newline="\n")
    print("home.json written")


TALKS = [
    ("roscon-fr-2025-hid-ros2", {"title": "hid_ros2: a universal HID hardware interface for ros2_control", "date": "2025-10-30",
     "kind": "talk", "event": "ROSConFr 2025", "code": "https://github.com/adnan-saood/hid_ros2", "post": "blog/?p=hid-ros2-roscon-fr",
     "featured": True}, "Debut of **hid_ros2**, a ros2_control hardware interface that connects any USB-HID device "
     "(Teensy, ESP32, STM32, custom sensors) to ROS 2 without writing custom C++ plugins."),
    ("icra-2026-handshake", {"title": "Contributing Factors in Human-Robot Handshake: Compliance, Hand Grip, and Synchrony",
     "date": "2026-05-01", "kind": "paper", "event": "IEEE ICRA 2026", "location": "Vienna, Austria",
     "post": "blog/?p=icra-2026-handshake", "featured": True},
     "Presentation of our study on the tactile and kinematic factors that make human-robot handshakes feel natural."),
    ("ensta-lab-day-2026", {"title": "Tactile sensing and human-robot interaction", "date": "2026-05-15", "kind": "poster",
     "event": "ENSTA U2IS Annual Lab Day", "award": "Best Poster & Best Presentation Awards", "featured": True},
     "Poster and talk on my PhD research, awarded both **Best Poster** and **Best Presentation**."),
    ("icsr-2024-tactile-interface", {"title": "Human Hand Shape and Grasping Behavior Estimation using a Humanoid Hand with a Tactile Interface",
     "date": "2024-09-15", "kind": "paper", "event": "ICSR 2024", "location": "Naples, Italy"},
     "Presentation of our tactile interface for humanoid hands at the 17th International Conference on Social Robotics."),
    ("beyond-words-icsr-2024", {"title": "Beyond Words: The Role of Touch in Social Robots", "date": "2024-09-14",
     "kind": "workshop", "event": "Workshop at ICSR 2024", "location": "Naples, Italy"},
     "Organised and hosted the workshop, with invited speakers Prof. Katherine J. Kuchenbecker (MPI-IS), "
     "Dr. Morten Roed Frederiksen (ITU Copenhagen) and Dr. Zhegong Shangguan."),
    ("beyond-words-2-icsr-2026", {"title": "Beyond Words 2: The Role of Touch in Social Robotics", "date": "2026-07-15",
     "kind": "workshop", "event": "Workshop at ICSR 2026", "post": "blog/?p=beyond-words-2-recap"},
     "Co-organised the second edition of the workshop, bringing together researchers on haptics, embodied interaction "
     "and socio-affective robotics."),
]


def migrate_talks():
    out = SEED / "talks"
    out.mkdir(parents=True, exist_ok=True)
    for slug, meta, body in TALKS:
        lines = ["---"]
        for k, v in meta.items():
            lines.append(f"{k}: {'true' if v is True else json.dumps(v, ensure_ascii=False)}")
        lines.append("---")
        (out / f"{slug}.md").write_text("\n".join(lines) + "\n\n" + body + "\n", encoding="utf-8", newline="\n")
    print(f"{len(TALKS)} talks written")


if __name__ == "__main__":
    migrate_home()
    migrate_talks()
