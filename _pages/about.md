---
layout: about
title: About me
permalink: /
subtitle: Doctoral Candidate in Robotics at <a href='https://www.ip-paris.fr/'>Institut Polytechnique de Paris</a>

profile:
  align: right
  image: prof_pic.png
  image_circular: false # crops the image to make it circular
  more_info: >
    <p>R.2.19</p>
    <p>828 Bd. des Maréchaux</p>
    <p>91762 Palaiseau Cedex</p>
    <p><a href="mailto:adnan.saood@ip-paris.fr">adnan.saood@ip-paris.fr</a></p>

selected_papers: true # includes a list of papers marked as "selected={true}"
social: true # includes social icons at the bottom of the page

announcements:
  enabled: true # includes a list of news items
  scrollable: true # adds a vertical scroll bar if there are more than 3 news items
  limit: 5 # leave blank to include all the news in the `_news` folder

latest_posts:
  enabled: true
  scrollable: true # adds a vertical scroll bar if there are more than 3 new posts items
  limit: 3 # leave blank to include all the blog posts
---

<section class="home-intro" aria-labelledby="home-intro-title">
  <div class="home-field-wrap">
    <canvas id="tactile-field" class="home-field" aria-label="Interactive tactile sensing field"></canvas>
    <span class="home-field-label">Move through the field</span>
    <span class="home-field-meta">touch / signal / response</span>
  </div>
  <div class="home-identity">
    <p class="home-kicker">Mechatronics engineer / medical robotics / PhD researcher</p>
    <h1 id="home-intro-title">Touch for Humanoid Robots</h1>
    <p class="home-role">Doctoral researcher in robotics at Institut Polytechnique de Paris - ENSTA.</p>
    <div class="home-profile">
      <img src="{{ '/assets/img/prof_pic.png' | relative_url }}" alt="Portrait of Adnan Saood">
      <span>Adnan Saood<br>ENSTA Paris<br><br>R.2.19<br>828 Bd. des Marechaux<br>91762 Palaiseau Cedex</span>
    </div>
  </div>
  <div class="home-intro-copy">
    <p class="home-lede">I am a mechatronics engineer with a master's degree in medical robotics, now pursuing a PhD on socio-affective touch, tactile sensing, and real-time estimation for more natural human-robot interaction.</p>
    <div class="home-signals" aria-label="Research focus">
      <span><strong>01</strong> Tactile perception</span>
      <span><strong>02</strong> Medical robotics</span>
      <span><strong>03</strong> Real-time systems</span>
    </div>
  </div>
  <a class="home-project-link" href="{{ '/projects/' | relative_url }}">Explore the work <span aria-hidden="true">&#8594;</span></a>
</section>
