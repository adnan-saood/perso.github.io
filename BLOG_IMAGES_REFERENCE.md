---
layout: page
title: Blog Post Images Reference
permalink: /blog-images-reference/
nav: false
published: false
---

# Blog Post Images: Inventory & Mapping

**Date:** September 15, 2026  
**Source:** LinkedIn posts JSON with embedded image metadata  
**Storage Location:** `/assets/img/blog/` (persistent, versioned)  
**Total Images:** 26 JPEGs  
**Total Size:** ~2.5 MB  

---

## Images by Blog Post

### Published Posts (6)

#### 1. "From PhD Thesis to Licensed Technology: Therasonic" (2026-07-15)
- **post02_1.jpg** (29 KB) — Therasonic/SATT Conectus branding
- **post02_2.jpg** (44 KB) — Patent and licensing announcement
- **Usage:** Added after "From Research Lab to Commercial Reality" section
- **LinkedIn Post:** #2 (July 2026, 875 impressions)

#### 2. "ICRA 2026 Recap: Human-Robot Handshake Study" (2026-06-10)
- **post03_1.jpg** (66 KB) — ICRA 2026 Vienna venue/crowd shot
- **post03_2.jpg** (208 KB) — Conference presentation detail
- **post06.jpg** (101 KB) — Handshake demonstration
- **Usage:** Added after "Key Findings" section as 2-column grid + 1 separate image
- **LinkedIn Post:** #3 (June 2026, 83 reactions) + #6 (June 2026, 44 reactions)

#### 3. "hid_ros2: 500Hz+ Hardware Bridge for ROS2" (2025-12-20)
- **post09.jpg** (30 KB) — hid_ros2 talk at ROSCon FR (AI-generated, but authentic!)
- **Usage:** Added with caption after "The Talk at ROSCon FR 2025" section
- **LinkedIn Post:** #9 (December 2025, 5,050 impressions — **HIGHEST ENGAGEMENT**)

#### 4. "Beyond Words 2: ICSR 2026 Touch Workshop Recap" (2026-07-25)
- **post01.jpg** (92 KB) — Workshop group photo/attendees
- **Usage:** Added after "Co-Organizers" section
- **LinkedIn Post:** #1 (July 2026, 549 impressions, 10 reactions)

#### 5. "A Touch of Feeling in Robotics (ENSTA Feature)" (2026-01-10)
- **post08_1.jpg** (26 KB) — Tactile sensing research photo
- **post08_2.jpg** (96 KB) — Haptic interface close-up
- **post08_3.jpg** (49 KB) — Research results/demonstration
- **Usage:** Added as 3-column grid after "The Hidden Sense in Robotics"
- **LinkedIn Post:** #8 (January 2026, 2,353 impressions, 8 comments)

#### 6. "Starting the PhD at ENSTA U2IS" (2024-09-15)
- **post17.jpg** (110 KB) — First day of PhD photo
- **Usage:** Added after "A Moment of Clarity" section
- **LinkedIn Post:** #17 (September 2024, 90 reactions, 31 comments — **HIGHEST ENGAGEMENT FOR MILESTONE POST**)

---

## Images in Backlog Posts (Not Yet Published)

These images correspond to blog posts still in the backlog, awaiting expansion and publication:

### Tier 1 Backlog (High Priority)

#### "Introducing hid_ros2" (Launch Announcement) — Backlog #1
- **post11.jpg** (66 KB) — hid_ros2 launch announcement/feature image
- **LinkedIn Post:** #11 (October 2025, 2,048 impressions, 43 reactions)
- **Notes:** Good marketing/feature shot for the launch post

#### "How My COVID-19 CT Segmentation Paper Became My Most-Cited Work" — Backlog #2
- **post22.jpg** (10 KB) — COVID-19 paper/publication thumbnail
- **LinkedIn Post:** #22 (June 2021, 41 reactions, most-cited paper origin story)

#### "PETRA: Winning the Geriatronics Summer School Challenge" — Backlog #3
- **post15.jpg** (81 KB) — Team photo from Geriatronics Summer School
- **LinkedIn Post:** #15 (June 2025, 2,382 impressions, 68 reactions, 5 comments)
- **Notes:** Strong engagement; good team/achievement photo

#### "A Research Exchange at TUM's Institute for Cognitive Systems" — Backlog #4
- **post07.jpg** (110 KB) — TU Munich research lab / Erasmus+ mobility
- **LinkedIn Post:** #7 (February 2026, 3,651 impressions)
- **Notes:** Second-highest engagement; good research context photo

### Tier 2 Backlog (Medium Priority)

#### "Beyond Words 1: The Role of Touch in Social Robots (Naples 2025)" — Backlog #5
- **post13_1.jpg** (39 KB) — Workshop attendees/setup
- **post13_2.jpg** (47 KB) — Speaker/presentation moment
- **post13_3.jpg** (40 KB) — Workshop participant interaction
- **post13_4.jpg** (50 KB) — Group photo/closing remarks
- **LinkedIn Post:** #13 (September 2025, 707 impressions, 30 reactions)
- **Notes:** Rich multi-image set; good for detailed retrospective

#### "Call for Papers: Beyond Words (1st edition)" — Backlog #6 (OPTIONAL)
- **post14.jpg** (1.8 MB) — Large event banner/promotional image
- **LinkedIn Post:** #14 (July 2025, 1,412 impressions, 29 reactions)
- **Notes:** Large file; downscaled for web; archival value only

### Tier 3 Backlog (Lower Priority / Optional)

#### Images Without Primary Blog Posts (Contextual Only)
- **post04.jpg** (104 KB) — ICRA twins observation (personal anecdote, likely SKIP)
- **post05.jpg** (105 KB) — Beyond Words 2 CFP poster (archival, low priority)
- **post10.jpg** (38 KB) — Demeny-Vaucanson Prize talk announcement (could pair with award blog post)
- **post16.jpg** (155 KB) — IROS 2025 SAR Workshop CFP (Adnan not primary organizer, low priority)
- **post18.jpg** (19 KB) — Farewell to ICube (farewell/transition photo, contextual only)
- **post21.jpg** (54 KB) — Starting at ICube Research Engineer (career milestone, low priority)

---

## Image File Specifications

### Size Summary
- **Smallest:** post22.jpg (9.6 KB)
- **Largest:** post14.jpg (1.8 MB — event banner, downscaled)
- **Typical Range:** 26–110 KB per image
- **Total Footprint:** ~2.5 MB (256 images including metadata)

### Format & Encoding
- **Format:** JPEG
- **Quality:** Web-optimized via LinkedIn (already compressed)
- **Dimensions:** Varied; typically 800–1280px on long edge
- **Color:** RGB, 8-bit
- **Progressive:** Yes (web-friendly streaming)

### Licensing
- **Source:** Public LinkedIn posts (user's own posts + reshares)
- **Rights:** All images are Adnan's own photos or reposted institutional content (ENSTA, SATT Conectus, etc.) with appropriate attribution
- **Usage:** Personal website only
- **Archives:** Safe to include in future portfolio/publication archives

---

## Image Integration in Jekyll

### Template Used
All images integrated via the standard al-folio `figure.liquid` include:

```liquid
{% include figure.liquid loading="lazy" path="assets/img/blog/postXX.jpg" 
  title="Image caption here" class="img-fluid rounded z-depth-1" %}
```

### Responsive Grid Layouts
- **Single column:** Full-width image
- **Two columns:** `.row` + two `.col-sm` divs (responsive stack on mobile)
- **Three columns:** `.row` + three `.col-sm` divs (responsive stack on mobile)

### Accessibility
- **Alt text:** Generated from `title` attribute
- **Lazy loading:** `loading="lazy"` for performance
- **Caption support:** Optional `<div class="caption">` below image grid

---

## Publishing Checklist for Backlog Posts

When publishing backlog posts, use this as reference for images:

- [ ] **Backlog #1 (hid_ros2 Launch):** Add post11.jpg as hero image
- [ ] **Backlog #2 (COVID-19 Paper):** Add post22.jpg as small thumbnail
- [ ] **Backlog #3 (PETRA):** Add post15.jpg with team photo caption
- [ ] **Backlog #4 (TUM Exchange):** Add post07.jpg as research context
- [ ] **Backlog #5 (Beyond Words 1):** Add post13_1/2/3/4 as 4-image gallery

---

## Notes for Future Maintenance

1. **Image URLs in JSON:** All images were scraped from LinkedIn's CDN. LinkedIn URLs expire; having local copies ensures long-term persistence.

2. **Backlog Post Images:** These images are ready to use whenever backlog posts are published. No additional work needed beyond adding Jekyll figure includes.

3. **Post14.jpg (Event Banner):** Very large file (1.8 MB). Only use if necessary; consider resizing if including in a published post.

4. **Engagement Tracking:** The engagement numbers (reactions, impressions, comments) in the image metadata don't need to be published but can inform blog post prioritization.

5. **Attribution:** All institutional images (ENSTA feature, SATT/Therasonic announcements, etc.) already credited in their respective blog posts. No additional attribution needed beyond the posts themselves.

---

## File Structure in Workspace

```
/home/adnan/perso/perso.github.io/
├── assets/img/blog/
│   ├── post01.jpg              (Beyond Words 2)
│   ├── post02_1.jpg, post02_2.jpg  (Therasonic)
│   ├── post03_1.jpg, post03_2.jpg  (ICRA handshake)
│   ├── post04.jpg              (ICRA twins — skipped)
│   ├── post05.jpg              (CFP — archival)
│   ├── post06.jpg              (Handshake demo)
│   ├── post07.jpg              (TUM Erasmus)
│   ├── post08_1.jpg, post08_2.jpg, post08_3.jpg  (ENSTA feature)
│   ├── post09.jpg              (hid_ros2 talk)
│   ├── post10.jpg              (Demeny-Vaucanson)
│   ├── post11.jpg              (hid_ros2 launch)
│   ├── post13_1.jpg, ..., post13_4.jpg  (Beyond Words 1)
│   ├── post14.jpg              (Beyond Words CFP)
│   ├── post15.jpg              (PETRA)
│   ├── post16.jpg              (IROS workshop)
│   ├── post17.jpg              (PhD starting)
│   ├── post18.jpg              (Farewell ICube)
│   ├── post21.jpg              (Research engineer)
│   └── post22.jpg              (COVID-19 paper)
│
├── _posts/
│   ├── 2026-07-15-therasonic-licensed.md          ✅ Images added
│   ├── 2026-06-10-icra-2026-handshake.md          ✅ Images added
│   ├── 2025-12-20-hid-ros2-roscon-fr.md           ✅ Images added
│   ├── 2026-07-25-beyond-words-2-recap.md         ✅ Images added
│   ├── 2026-01-10-ensta-feature-touch-robotics.md ✅ Images added
│   ├── 2024-09-15-starting-phd-ensta.md           ✅ Images added
│   ├── (9 backlog posts — images ready, awaiting expansion)
│   └── ...
│
└── agents/
    └── adnan_linkedin_posts.json  (source data, not persistent)
```

---

## Quick Reference: Blog Post Image Status

| Post | Title | Images | Status |
|------|-------|--------|--------|
| 1 | Therasonic | post02_1, 02_2 | ✅ Published + images |
| 2 | ICRA Handshake | post03_1, 03_2, post06 | ✅ Published + images |
| 3 | hid_ros2 Talk | post09 | ✅ Published + images |
| 4 | Beyond Words 2 | post01 | ✅ Published + images |
| 5 | ENSTA Feature | post08_1, 08_2, 08_3 | ✅ Published + images |
| 6 | Starting PhD | post17 | ✅ Published + images |
| B1 | hid_ros2 Launch | post11 | 🟡 Backlog, images ready |
| B2 | COVID-19 Paper | post22 | 🟡 Backlog, images ready |
| B3 | PETRA | post15 | 🟡 Backlog, images ready |
| B4 | TUM Exchange | post07 | 🟡 Backlog, images ready |
| B5 | Beyond Words 1 | post13_1–4 | 🟡 Backlog, images ready |

---

*This reference document ensures all blog images are archived, accessible, and ready for publication.*
