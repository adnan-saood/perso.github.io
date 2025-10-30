---
layout: page
title: Teaching
permalink: /teaching/
description: Courses I have taught or am currently teaching.
nav: true
nav_order: 6
display_categories: [work, fun]
horizontal: false
---

<!-- pages/teaching.md -->
<div class="projects">
{% if site.enable_course_categories and page.display_categories %}
  <!-- Display categorized courses -->
  {% for category in page.display_categories %}
  <a id="{{ category }}" href=".#{{ category }}">
    <h2 class="category">{{ category }}</h2>
  </a>
  {% assign categorized_teaching = site.teaching | where: "category", category %}
  {% assign sorted_teaching = categorized_teaching | sort: "importance" %}
  <!-- Generate cards for each teaching -->
  {% if page.horizontal %}
  <div class="container">
    <div class="row row-cols-1 row-cols-md-2">
    {% for teaching in sorted_teaching %}
      {% include projects_horizontal.liquid project=teaching %}
    {% endfor %}
    </div>
  </div>
  {% else %}
  <div class="row row-cols-1 row-cols-md-3">
    {% for teaching in sorted_teaching %}
      {% include teaching.liquid project=teaching %}
    {% endfor %}
  </div>
  {% endif %}
  {% endfor %}

{% else %}

<!-- Display teaching without categories -->

{% assign sorted_teachings = site.teaching | sort: "importance" %}

  <!-- Generate cards for each teaching -->

{% if page.horizontal %}

  <div class="container">
    <div class="row row-cols-1 row-cols-md-2">
    {% for teaching in sorted_teachings %}
      {% include projects_horizontal.liquid project=teaching %}
    {% endfor %}
    </div>
  </div>
  {% else %}
  <div class="row row-cols-1 row-cols-md-3">
    {% for teaching in sorted_teachings %}
      {% include teaching.liquid project=teaching %}
    {% endfor %}
  </div>
  {% endif %}
{% endif %}
</div>
