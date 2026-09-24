---
layout: page
title: Site Rebuild Status & TODOs
description: Implementation checklist and decision points for the AGENT.md rebuild plan.
permalink: /rebuild-status/
nav: false
published: false
---

# Personal Website Rebuild Implementation Status

**Plan Source:** `agents/AGENT.md`  
**Status Date:** September 15, 2026  
**Overall Progress:** ~60% complete (content research phase finished; content creation / publication phase in progress)

---

## ✅ Completed Tasks

### §3.1 Homepage / About (`_pages/about.md`)
- [x] Bio rewrite with accurate research narrative
- [x] Office address corrected (R.2.19, 828 Bd des Maréchaux, 91120 Palaiseau)
- [x] Home page text cleaned of placeholder instructions
- [x] Profile photo placeholder gracefully handled
- [x] 5 real news announcements created:
  - Therasonic patent licensing (2026)
  - Beyond Words 2 co-organization (2026)
  - Fédération Demeny Vaucanson prize (2026)
  - Best Awards at ENSTA Lab Day (2026)
  - ICRA 2026 handshake paper (2026)

### §3.2 Blog Framework
- [x] Blog pipeline stub post created (`_posts/2026-01-15-blog-pipeline-stub.md`)
- [x] Framework for accepting LinkedIn exports documented
- [ ] Actual blog posts written (awaiting INPUT from Adnan; see TODO section below)

### §3.3 Repositories Page (`_data/repositories.yml`)
- [x] Curated list of 12 public repositories configured
- [x] Auto-pull of forks disabled in favor of hand-curated portfolio

### §3.4 CV Page (`_data/cv.json`)
- [x] Updated with new awards (Fédération Demeny Vaucanson, Best Poster/Presentation 2026)
- [x] Experience section: Therasonic licensing note added
- [x] Skills: PyTorch, TensorFlow, JAX added under Deep Learning category
- [x] Doctoral Fellowship (2023) visible in Honors section

### §3.5 Projects Page (`_projects/*.md`)
- [x] 1_project.md: Socio-Affective Tactile Array (kept, refined)
- [x] 2_project.md: Generative Factorized Tactile Affordance (new)
- [x] 3_project.md: Blood-Brain Barrier Opening / Therasonic (new)
- [x] 4_project.md: Human-Robot Handshake Study (new)
- [x] 5_project.md: Soft Robotic Haptic Interface (new)
- [x] covid_dl_published.md: COVID-19 Segmentation (new)
- [x] swarm_dynamics.md: SWARM Motion Planning (new)
- [ ] Engineering projects (paxini_ros2, franka_handshake_ros2, Meka, ndisys_ros2): Currently referenced in project descriptions; could be elevated to full project cards

### §3.6 Teaching Page (`_teaching/*.md`)
- [x] Robotics Course: placeholder with TODO marker
- [x] Control Theory Course: placeholder with TODO marker
- [x] IN104 C Programming: placeholder with TODO marker
- [ ] Real course descriptions (awaiting INPUT from Adnan; see TODO section below)

### §3.7 Publications Page
- [x] Added 2026 handshake paper (inproceedings)
- [x] Added 2025 handshape paper (inproceedings)
- [x] Added 2025 humor paper (inproceedings)
- [x] Patent EP4445859A1: Annotated with Therasonic licensing note
- [x] Page is now auto-generated from updated `_bibliography/papers.bib`

### §3.8 People Page
- [x] Deleted placeholder people page content
- [x] Removed "people" from navigation dropdown
- [x] Alternative: author links on Publications page can serve as collaborator credit

### §3.9 Bookshelf/Books Page
- [x] Books submenu option removed from dropdown
- [x] No empty demo book page shipped
- [x] Can revisit if Adnan wants a reading list (see Decision Point D4 below)

### Infrastructure & Dependencies
- [x] Fixed missing `feedjira`, `httharty`, `nokogiri` plugin dependencies
- [x] Regenerated `Gemfile.lock` with all external-posts dependencies
- [x] Docker image rebuilt and tested: Jekyll build completes successfully
- [x] Confirmed site serves on `http://localhost:8080/` with auto-reload

### Cleanup
- [x] Removed theme demo posts with obsolete plugin requirements:
  - 2020-09-28-twitter.md (jekyll-twitter-plugin)
  - 2023-07-04-jupyter-notebook.md (jekyll-jupyter-notebook)
  - 2023-07-12-post-bibliography.md (lorem ipsum demo)
  - 2024-01-27-advanced-images.md (stock demo)
  - 2024-05-01-tabs.md (stock demo)
  - 2025-03-26-plotly.md (stock demo)
- [x] Removed Einstein placeholder pages:
  - _pages/about_einstein.md
  - _pages/profiles.md (duplicate/test)
- [x] Removed fake book entry: _books/the_godfather.md
- [x] Placeholder scan confirms no "Write your biography", "project 1", "your office number", etc. in publishable files

---

## 🟡 TODO — INPUT NEEDED FROM ADNAN

### Blog Posts (§3.2 — High Priority)
**Status:** Framework ready; content waiting for Adnan

1. **Suggested Posts (from AGENT.md §3.2 backlog):**
   - "Beyond Words 2: What We Learned Organizing ICSR 2026's Touch Workshop"
   - "From PhD Thesis to Licensed Technology: How Our Focused-Ultrasound Robot Became Therasonic"
   - "ICRA 2026 Recap: Presenting 'Contributing Factors in Human-Robot Handshake'"
   - "A Touch of Feeling in Robotics" (republish of ENSTA feature)
   - "Generative Tactile Affordances: Teaching Robots to Understand Touch Through Action" (original explainer)
   - Optional: "Publication Roundup 2024–2026" or "Notes from ICRA"

2. **How to submit:**
   - Option A: Export posts from LinkedIn (Settings → Data → Posts/Articles), include text and any images/links
   - Option B: Paste/forward text directly, with publish dates and any external links
   - Option C: Provide outlines/talking points for the agent to expand

3. **Expected output:** Fully formatted Jekyll blog posts in `_posts/YYYY-MM-DD-slug.md` with front matter, cross-links to publications/projects, and images.

---

### Teaching Page Details (§3.6 — High Priority)
**Status:** Placeholder pages created with clear TODO markers

For each course (Robotics, Control Theory, IN104 C):
- [ ] Institution (assume ENSTA, but confirm if any are external)
- [ ] Semester(s) taught and year(s)
- [ ] Your role: TA / co-instructor / lead instructor
- [ ] 2–3 sentence course description (level, learning outcomes, topics covered)
- [ ] Optional: syllabus link, materials, or lab assignments (if public)

Current placeholders at:
- [_teaching/robotics.md](_teaching/robotics.md)
- [_teaching/Control.md](_teaching/Control.md)
- [_teaching/IN104.md](_teaching/IN104.md)

---

### Profile Photo (§3.1 — Medium Priority)
**Status:** Placeholder removed; position ready for a real photo

- [ ] Provide a professional headshot photo (RGB JPG/PNG, ~400×400px suggested)
- [ ] Place in `assets/img/prof_pic.png` (or change the filename in `_pages/about.md` line 8 if using a different name)
- [ ] Photo will appear in the right-column profile box on the homepage

**Fallback:** Currently, the image block gracefully omits the image if the file doesn't exist; site remains functional without it.

---

### Favicon (§5 Polish Pass — Lower Priority)
**Status:** Not yet implemented; th al-folio default is in use

- [ ] Design or provide a favicon (small icon for browser tab)
- [ ] Place in `assets/img/favicon.ico` or configure in `_config.yml`

---

### Decision Point D1: Domain & DNS (§4 — Medium Priority)
**Status:** Unresolved

- [ ] **Does `saood.fr` already redirect to `perso.ensta.fr/~saood/`?**
  - Run: `nslookup saood.fr` and `whois saood.fr` to check current domain status
  - If active, no action needed
  - If parked/unused, decide:
    - Option A: Point `saood.fr` → perso.ensta.fr (limited by ENSTA hosting constraints)
    - Option B: Migrate site to GitHub Pages + custom domain `saood.fr` (recommended for cleaner URLs and future flexibility)

- [ ] **Impact:** Every external link (CV, LinkedIn, GitHub bio) should point at the canonical URL

---

### Decision Point D2: Hosting & Stack (§4 — Medium Priority)
**Status:** Recommended but unconfirmed

- [ ] **Recommendation:** Keep Jekyll + al-folio (no migration needed)
- [ ] **If D1 resolves toward custom domain:** Evaluate GitHub Pages deployment (free, excellent custom-domain support) vs. ENSTA userdir hosting
  - **GitHub Pages route:** site served at `saood.fr`, fallback ENSTA mirror optional
  - **ENSTA userdir route:** keep current path, but `saood.fr` redirect is harder to achieve

---

### Decision Point D3: Deployment Automation (§4 — Medium Priority)
**Status:** Current method unknown; automation recommended

- [ ] **How does the current site deploy to perso.ensta.fr?**
  - Check for `Makefile`, `.github/workflows/`, `deploy.sh` in the repo
  - Or ask Adnan: manual `rsync`/`scp` of `_site/` to ENSTA? Automated CI/CD?

- [ ] **Recommendation:** Automate with GitHub Actions
  - Build Jekyll on `main` branch push
  - Deploy via `rsync`/`scp` using SSH credentials (stored as GitHub repo secret)
  - Enables blog publishing and content updates without manual deployment steps

---

### Decision Point D4: Page Removals & Finalization (§4 — Lower Priority)
**Status:** All flagged; decisions pending

- [x] **People Page:** Removed (solo site, not a lab/group). ✅
- [ ] **Books Page:** Do you want a reading list / favorite books page?
  - If yes: populate with real reading list (see [al-folio books feature](https://al-folio.netlify.app/blog/2021/distill/))
  - If no: remains removed (current state)

- [ ] **Stale Publication Entry:** In the old CV, there was an "under review" journal paper on BBB-permeabilization. Was this superseded by the two 2023 CRAS/ISTU papers, or is it still active?
  - If superseded: confirm removal (already deleted from bibliography)
  - If active: provide updated BibTeX entry

---

### Analytics & Comments Configuration (§5 — Lower Priority)
**Status:** Not yet configured; placeholder decisions

- [ ] **Site analytics:**
  - Want Google Analytics, Plausible, or no tracking?
  - Configure in `_config.yml` if desired

- [ ] **Post-level comments:**
  - Current default: Giscus (GitHub-backed comment system) enabled on demo posts
  - Prefer Giscus, Disqus, or none?
  - Configure in each post's front matter as needed

---

## 🔴 Known Issues & Notes

### Site Serving Issues
- **Admin destination conflict:** Jekyll warns that `/srv/jekyll/_site/admin/index.html` is written to by both `admin.html` and `/admin/index.html`. Non-critical (both serve the same admin panel); can be resolved by deleting one source file if needed.

### Sass Deprecation Warnings
- al-folio theme uses deprecated Sass `@import` rules (Dart Sass 3.0+ will remove them). These are theme-level, not specific to this rebuild; no action needed unless updating al-folio upstream.

### Missing Project Images
- Projects now have `img:` fields, but actual image files may not exist in `assets/img/projects/`. 
  - Current: placeholder text displays; no broken image icons
  - To enhance: provide or create images for:
    - tactile_affordance.jpg
    - bbb_ultrasound.jpg
    - handshake.jpg
    - haptic_interface.jpg
    - covid_segmentation.jpg
    - swarm_robots.jpg

---

## 📋 Next Steps (Recommended Order)

1. **Immediate (today):**
   - Review this status page
   - Confirm teaching page details (§3.6 TODO) and provide to agent
   - Decide on blog posts backlog and provide any LinkedIn exports/talking points (§3.2 TODO)

2. **Short term (this week):**
   - Provide profile photo (§3.1 TODO)
   - Resolve decision points D1–D3 (domain, hosting, deployment)
   - Submit any open publication/award details for verification

3. **Medium term (next week):**
   - Draft and publish first 2–3 blog posts
   - Final review of all pages (typography, links, factual accuracy)
   - Test on multiple browsers/devices

4. **Polish (before public launch):**
   - Provide project images (or commission/create them)
   - Decide on comments/analytics config (D4)
   - Final accessibility & SEO audit
   - Deploy to live hosting (GitHub Pages or ENSTA, per D1/D2 decisions)

---

## Quick Links to Edited Files

### Pages
- [_pages/about.md](_pages/about.md) — homepage bio & profile
- [_teaching/robotics.md](_teaching/robotics.md)
- [_teaching/Control.md](_teaching/Control.md)
- [_teaching/IN104.md](_teaching/IN104.md)

### Projects
- [_projects/1_project.md](_projects/1_project.md) — Tactile Array
- [_projects/2_project.md](_projects/2_project.md) — Generative Affordance
- [_projects/3_project.md](_projects/3_project.md) — Therasonic BBB
- [_projects/4_project.md](_projects/4_project.md) — Handshake
- [_projects/5_project.md](_projects/5_project.md) — Haptic Interface
- [_projects/covid_dl_published.md](_projects/covid_dl_published.md) — COVID-19
- [_projects/swarm_dynamics.md](_projects/swarm_dynamics.md) — SWARM

### News & Blog
- [_news/](/_news/) — 5 new announcements
- [_posts/2026-01-15-blog-pipeline-stub.md](_posts/2026-01-15-blog-pipeline-stub.md) — blog framework

### Config
- [_data/repositories.yml](_data/repositories.yml) — curated repos
- [_data/cv.yml](_data/cv.yml) — updated CV data
- [_bibliography/papers.bib](_bibliography/papers.bib) — 3 new entries + patent note

### Infrastructure
- [Gemfile](Gemfile) — feedjira, httparty, nokogiri added
- [Gemfile.lock](Gemfile.lock) — locked versions
- [docker-compose.yml](docker-compose.yml) — verified working

---

## Questions?

Refer to the original brief: [agents/AGENT.md](agents/AGENT.md)

All TODO items in code are marked `TODO(adnan): ...` for easy grep/search:

```bash
grep -r "TODO(adnan)" _pages/ _projects/ _teaching/ _posts/ _news/ --include="*.md"
```
