---
layout: post
title: "From PhD Thesis to Licensed Technology: How Our Focused-Ultrasound Robot Became Therasonic"
date: 2026-07-15
description: The journey from research at ICube to real-world deployment—patent EP4445859A1 licensed to startup Therasonic
tags: tech-transfer robotics patent licensing innovation
categories: research impact
related_posts: true
giscus_comments: true
---

## A Research Dream Realized

When I started my Master's research at ICube Laboratory (University of Strasbourg) in 2022, I was tackling a fundamental challenge in neuromedicine: **how do you safely deliver drugs across the blood-brain barrier?** The BBB is a critical protective membrane, but it also blocks ~99% of large-molecule therapeutics from reaching brain tumors, Parkinson's disease, Alzheimer's, and other neurological conditions.

The answer our team developed: **a robot-assisted focused ultrasound system** capable of temporarily, safely, and reversibly opening the BBB at precise target locations. It sounds like science fiction, but the physics is solid, and the clinical need is urgent.

Fast-forward to July 2026, and I'm thrilled to announce that our work has been **patented (EP4445859A1) and exclusively licensed to TheraSonic**, a CEA Neurospin spin-off company. This isn't just an academic milestone—it's the beginning of a real therapeutic system that will reach patients.

---

## The Research Behind the Technology

The technical approach combined three key innovations:

**1. Robotic Trajectory Planning**  
We developed optimization-based algorithms to compute smooth, collision-free paths for robot end-effectors that position ultrasound transducers at precise angles. This ensures optimal acoustic coupling while minimizing off-target exposure and thermal side effects.

**2. Real-Time Control & Safety**  
Building on ROS, we created closed-loop control pipelines that:
- Adjust ultrasound power and frequency in real time
- Monitor B-mode and Doppler ultrasound imaging for feedback
- Automatically shut down upon detecting anomalies

**3. Preclinical Validation**  
Rigorous testing in ex-vivo and in-vivo models confirmed BBB opening and successful nanoparticle extravasation, paving the way for clinical translation.

---

## From Research Lab to Commercial Reality

The path from thesis to license involved:

- **2022–2023:** Research design, experimental validation, technical writing
- **2023:** Patent application filing (coordinated with SATT Conectus and CEA)
- **2024:** Patent awarded (EP4445859A1)
- **2026:** Exclusive technology license to TheraSonic

This kind of tech transfer requires more than just good science—it requires partnerships, regulatory foresight, and a startup team crazy enough to believe in the vision. TheraSonic's co-founders and the CEA team exemplify this.

<div class="row mt-3">
    <div class="col-sm mt-3 mt-md-0">
        {% include figure.liquid loading="lazy" path="assets/img/blog/post02_1.jpg" title="Therasonic tech transfer" class="img-fluid rounded z-depth-1" %}
    </div>
    <div class="col-sm mt-3 mt-md-0">
        {% include figure.liquid loading="lazy" path="assets/img/blog/post02_2.jpg" title="Patent and licensing" class="img-fluid rounded z-depth-1" %}
    </div>
</div>

---

## What Happens Next?

As an **Engineering Consultant at TheraSonic**, I remain actively involved. The company is now:
- Finalizing regulatory compliance for the first prototype
- Planning toward clinical trials (~2028 timeline)
- Targeting market launch around 2030
- Raising capital via crowdequity to accelerate development

The focused ultrasound itself is not new (decades of research), but the **combination with precision robotics and machine learning-based trajectory planning** opens a new clinical frontier.

---

## Gratitude & Acknowledgments

This achievement is the fruit of 15 years of collaborative research across CEA/NeuroSpin, BioMaps, and ICube. Special thanks to:
- **Prof. Adriana Tapus** (my PhD advisor, continuing to support)
- **Dr. Benoit Larrat & Dr. Jonathan Vappou** (CEA pioneers in focused ultrasound)
- **Prof. Florent Nageotte** (ICube robotics group)
- The CEA Investissement, ANR, Bpifrance, and regional partners who funded the groundwork
- And most importantly, **SATT Conectus** for shepherding the tech transfer with excellence

---

## What This Means

For a researcher, this is validation that your work matters beyond the conference room. For patients with Parkinson's, Huntington's, ALS, Alzheimer's, and brain tumors, it's hope—hope that in a few years, a small robot might unlock a path to treatment that was previously impossible.

If you're interested in the technical details, check out our peer-reviewed publications on controlled acoustic field modeling (CRAS 2023) and ultrasound safety/efficacy (ISTU 2023) linked on the Publications page. Or visit [therasonic.fr](https://www.therasonic.fr/) to learn more about the company.

---

**Related:**
- See the [Therasonic BBB project]({{ site.baseurl }}/projects/#robotic-assistance-for-blood-brain-barrier-opening-via-focused-ultrasound) for full technical details
- Publications: CRAS 2023, ISTU 2023 on the Publications page
