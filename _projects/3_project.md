---
layout: page
title: Robotic Assistance for BBB Opening
description: Master's thesis work on robot-assisted transcranial focused ultrasound for drug delivery across the blood-brain barrier. Licensed to Therasonic in 2026.
img: assets/img/bbb_ultrasound.jpg
importance: 1
category: Research
related_publications: true
---

## Project Overview

{% include figure.liquid loading="eager" path="assets/img/bbb_ultrasound.jpg" title="Robot-assisted focused ultrasound for BBB opening" class="img-fluid rounded z-depth-1" %}

This Master's thesis research at ICube Laboratory (University of Strasbourg) addresses a critical challenge in neuromedicine: **delivering drugs across the blood-brain barrier (BBB)** — a natural protective membrane that blocks most therapeutics from reaching the brain.

The solution: a **robot-assisted focused ultrasound system** that can precisely open the BBB at target sites, allowing magnetic nanoparticles and drugs to enter — all in a minimally invasive, reversible, and repeatable manner.

## The Clinical Problem

The BBB is essential for protecting the brain, but it also prevents ~99% of large-molecule drugs from reaching brain tumors, neurodegenerative diseases, and other conditions. Current clinical options are limited:
- Open surgery (highly invasive)
- Systemic chemotherapy (systemically toxic, poor brain penetration)
- No effective non-invasive delivery for most conditions

Focused ultrasound offers a new paradigm: **temporary, localized, non-invasive BBB disruption**.

## Technical Approach

### Robotic Trajectory Planning

Developed optimization-based algorithms to compute smooth, collision-free robot end-effector trajectories that:
- Position ultrasound transducers at precise angles for optimal acoustic coupling
- Maintain stable contact despite patient anatomy variability
- Minimize off-target exposure and thermal side effects

### Real-Time Control

Built ROS-based control pipelines for:
- Closed-loop ultrasound power and frequency adjustment
- Real-time ultrasound imaging feedback (B-mode and Doppler)
- Safety monitoring and automatic shutoff upon anomaly detection

### Validation

Preclinical studies in ex-vivo and in-vivo models, with imaging confirmation of BBB opening and successful nanoparticle extravasation.

## Real-World Impact: Therasonic

In 2026, the patent EP4445859A1 (awarded in 2024) was exclusively licensed to **Therasonic**, a CEA Neurospin spin-off company focused on bringing this technology to clinical practice. As an Engineering Consultant at Therasonic, I continue to contribute to translating this research into a therapeutic system.

## Publications

See the Publications page for the peer-reviewed papers from this work:
- CRAS 2023 (controlled acoustic field modeling)
- ISTU 2023 (ultrasound safety and efficacy)

## Related Resources

- Patent: [EP4445859A1](https://patents.google.com/?assignee=CEA&inventor=saood) (Google Patents)
- Blog: "From PhD Thesis to Licensed Technology: How Our Focused-Ultrasound Robot Became Therasonic" (TODO: to be written by Adnan)
- Company: [Therasonic](https://www.therasonic.fr/) (French site; English coming soon)
