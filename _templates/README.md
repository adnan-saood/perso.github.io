# Blog Post Templates

This folder contains template posts demonstrating various features and formatting options available in al-folio. Use these as reference when writing new blog posts for your site.

## Quick Reference Guide

### 1. Image Galleries

**Files:** `2015-05-15-images.md`

**Features:**
- Responsive multi-column image grids using Bootstrap
- Zoomable images with `zoomable=true` parameter
- Image captions
- Lazy loading for performance

**Usage:**
```liquid
<div class="row mt-3">
    <div class="col-sm mt-3 mt-md-0">
        {% include figure.liquid loading="lazy" path="assets/img/blog/post01.jpg" 
            title="Image caption" class="img-fluid rounded z-depth-1" zoomable=true %}
    </div>
    <div class="col-sm mt-3 mt-md-0">
        {% include figure.liquid loading="lazy" path="assets/img/blog/post02.jpg" 
            title="Another caption" class="img-fluid rounded z-depth-1" zoomable=true %}
    </div>
</div>
<div class="caption">
    Overall caption for the image group
</div>
```

---

### 2. Advanced Photo Galleries

**Files:** `2024-12-04-photo-gallery.md`

**Features:**
- Multiple lightbox libraries: Lightbox2, PhotoSwipe, Spotlight, VenoBox
- Full-screen gallery viewing
- Grouped galleries with different libraries

**Best for:** Showcasing multiple images from events, lab work, or research demonstrations

---

### 3. Video Embedding

**Files:** `2023-04-24-videos.md`

**Features:**
- Local video files (MP4, WebM, etc.)
- YouTube embed
- Vimeo embed
- Responsive video containers
- Autoplay and controls options

**Usage:**
```liquid
<!-- Local video -->
<div class="row mt-3">
    <div class="col-sm mt-3 mt-md-0">
        {% include video.liquid path="assets/video/my-video.mp4" 
            class="img-fluid rounded z-depth-1" controls=true autoplay=true %}
    </div>
</div>

<!-- YouTube -->
{% include video.liquid path="https://www.youtube.com/embed/VIDEO_ID" %}

<!-- Vimeo -->
{% include video.liquid path="https://player.vimeo.com/video/VIDEO_ID" %}
```

---

### 4. Data Visualization

**Files:** 
- `2024-01-26-echarts.md` — Interactive charts
- `2024-01-26-vega-lite.md` — Declarative visualization grammar

**Features:**
- ECharts for complex, interactive charts
- Vega-Lite for statistical graphics
- Responsive, embeddable visualizations
- JSON/configuration-based definitions

**Best for:** Publishing data-driven research results, benchmark comparisons

---

### 5. Diagrams and Flowcharts

**Files:** `2021-07-04-diagrams.md`

**Features:**
- Mermaid diagrams (flowcharts, sequence diagrams, class diagrams, etc.)
- Zoomable diagrams
- Text-based definition (no image editing needed)

**Usage:**
```liquid
Front matter:
mermaid:
  enabled: true
  zoomable: true

In content:
```mermaid
flowchart LR
    A[Start] --> B[Process] --> C[End]
```
```

---

### 6. Tables

**Files:** `2023-03-21-tables.md`

**Features:**
- Bootstrap table styling (automatic with `pretty_table: true`)
- Responsive tables
- Text alignment (left, center, right)
- Sortable tables

**Usage:**
```yaml
---
layout: post
pretty_table: true
---
```

Then use standard Markdown tables:
```markdown
| Header 1 | Header 2 | Header 3 |
| :------- | :------: | -------: |
| Left     | Center   | Right    |
```

---

### 7. Code Features

**Files:** 
- `2015-07-15-code.md` — Code highlighting and line numbers
- `2024-01-27-code-diff.md` — Diff/patch highlighting
- `2024-04-15-pseudocode.md` — Algorithm pseudocode

**Features:**
- Syntax highlighting for 100+ languages
- Line numbers and line highlighting
- Diff format support
- Copy-to-clipboard buttons

---

### 8. Math and Equations

**Files:** `2015-10-20-math.md`

**Features:**
- LaTeX/KaTeX inline and block equations
- Display math: `$$...$$`
- Inline math: `$...$`
- Full equation numbering and referencing

**Usage:**
```markdown
Inline: This is $E = mc^2$ in your text.

Block:
$$
\frac{\partial u}{\partial t} = \nu \nabla^2 u - u \cdot \nabla u - \nabla p
$$
```

---

### 9. Comments and Interactions

**Files:** 
- `2015-10-20-disqus-comments.md` — Disqus comments (legacy)
- `2022-12-10-giscus-comments.md` — Giscus comments (GitHub-based)

**Features:**
- Disqus comment system (older, more options)
- Giscus GitHub discussions (modern, privacy-friendly)

**Usage:**
```yaml
---
layout: post
giscus_comments: true  # Enable GitHub Giscus comments
---
```

---

### 10. Post Citations

**Files:** `2024-04-28-post-citation.md`

**Features:**
- Posts can be cited with BibTeX
- Automatic citation metadata generation
- Link to post's citation info

**Usage:**
```yaml
---
layout: post
citation: true
---
```

---

### 11. Formatting and Special Features

**Files:**
- `2015-03-15-formatting-and-links.md` — Links, emphasis, lists, quotes
- `2023-03-20-table-of-contents.md` — Auto-generated table of contents
- `2023-05-12-custom-blockquotes.md` — Styled blockquotes
- `2024-04-29-typograms.md` — ASCII diagram rendering
- `2023-12-12-tikzjax.md` — TikZ diagrams (LaTeX graphics)

---

## Common Front Matter Options

```yaml
---
layout: post
title: "Post Title"
date: 2026-MM-DD
description: "Short description for preview"

# Styling and features
tags: tag1 tag2 tag3
categories: category-name
thumbnail: assets/img/thumbnail.jpg

# Interactivity
giscus_comments: true          # Enable comments
related_posts: true            # Show similar posts
citation: true                 # Make post citable
pretty_table: true             # Style tables with Bootstrap

# Media features
mermaid:
  enabled: true
  zoomable: true
chart:
  echarts: true               # Enable ECharts
  vega: true                  # Enable Vega-Lite

images:
  lightbox2: true
  photoswipe: true
  spotlight: true
  venobox: true
---
```

---

## Tips for Reusing Templates

1. **Copy and adapt:** Take any template file, change the date to today's date, update the title and content.

2. **Mix and match:** Combine features from different templates (e.g., use image gallery + diagrams + pretty tables in one post).

3. **Keep it clean:** Remove unused front matter options to keep posts lightweight.

4. **Test locally:** Use `docker compose up jekyll` to preview posts locally before publishing.

5. **Git track:** These templates won't appear in Jekyll output (no `_templates` collection), so they're safe to experiment with.

---

## Example Workflow

1. Copy `2015-05-15-images.md` to `_posts/2026-MM-DD-my-new-post.md`
2. Update the front matter (title, date, description, tags)
3. Replace the placeholder images with your own: update `path="assets/img/..."` references
4. Write your content
5. Add videos, diagrams, or other features from other templates as needed
6. Test with `docker compose up jekyll` and visit http://localhost:8080
7. Commit and push when ready

---

## For More Information

- [al-folio Documentation](https://github.com/alshedivat/al-folio)
- [Jekyll Liquid Tags](https://jekyllrb.com/docs/liquid/)
- [Bootstrap Grid System](https://getbootstrap.com/docs/5.1/layout/grid/)
- Each template file contains extensive inline documentation

---

*Last updated: 2026-09-15*
