---
title: Robotic Assistance for BBB Opening
description: Master's thesis work on robot-assisted transcranial focused ultrasound for drug delivery across the blood-brain barrier. Licensed to Therasonic in 2026.
img: assets/img/bbb_ultrasound.jpg
category: "Medical robotics & imaging"
importance: 3
featured: true
title_fr: "Assistance robotique pour l'ouverture de la BHE"
description_fr: "Travail de master sur les ultrasons focalisés transcrâniens assistés par robot pour délivrer des médicaments à travers la barrière hémato-encéphalique. Licencié à Therasonic en 2026."
category_fr: "Robotique médicale & imagerie"
body_fr: "## Présentation du projet\n\nCe travail de master au laboratoire ICube (Université de Strasbourg) s'attaque à un défi majeur de la neuromédecine : **faire passer des médicaments à travers la barrière hémato-encéphalique (BHE)**, une membrane protectrice naturelle qui empêche la plupart des traitements d'atteindre le cerveau.\n\nLa solution : un **système d'ultrasons focalisés assisté par robot** capable d'ouvrir précisément la BHE sur des zones ciblées, pour laisser entrer nanoparticules magnétiques et médicaments, de façon peu invasive, réversible et répétable.\n\n## Le problème clinique\n\nLa BHE est indispensable pour protéger le cerveau, mais elle bloque aussi environ 99 % des médicaments à grosses molécules destinés aux tumeurs cérébrales, aux maladies neurodégénératives et à d'autres pathologies. Les options cliniques actuelles sont limitées :\n- la chirurgie ouverte (très invasive) ;\n- la chimiothérapie systémique (toxique pour tout l'organisme, faible pénétration cérébrale) ;\n- aucune voie d'administration non invasive efficace pour la plupart des pathologies.\n\nLes ultrasons focalisés ouvrent une nouvelle voie : **une ouverture de la BHE temporaire, localisée et non invasive**.\n\n## Approche technique\n\n### Planification de trajectoires robotiques\n\nDéveloppement d'algorithmes d'optimisation pour calculer des trajectoires de l'effecteur, lisses et sans collision, qui :\n- positionnent les transducteurs ultrasonores selon des angles précis pour un couplage acoustique optimal ;\n- maintiennent un contact stable malgré la variabilité anatomique des patients ;\n- minimisent l'exposition hors cible et les effets thermiques indésirables.\n\n### Contrôle en temps réel\n\nChaînes de contrôle sous ROS pour :\n- l'ajustement en boucle fermée de la puissance et de la fréquence des ultrasons ;\n- le retour d'imagerie échographique en temps réel (mode B et Doppler) ;\n- la surveillance de sécurité avec arrêt automatique en cas d'anomalie.\n\n### Validation\n\nÉtudes précliniques ex vivo et in vivo, avec confirmation par imagerie de l'ouverture de la BHE et de l'extravasation des nanoparticules.\n\n## Impact concret : Therasonic\n\nEn 2026, le brevet EP4445859A1 (délivré en 2024) a été licencié en exclusivité à **Therasonic**, une start-up issue de NeuroSpin (CEA) qui porte cette technologie vers la pratique clinique. En tant qu'ingénieur consultant chez Therasonic, je continue de contribuer à transformer cette recherche en système thérapeutique.\n\n## Publications\n\nVoir la page Publications pour les articles issus de ce travail :\n- CRAS 2023 (modélisation d'un champ acoustique contrôlé)\n- ISTU 2023 (sécurité et efficacité des ultrasons)\n\n## Ressources\n\n- Brevet : [EP4445859A1](https://patents.google.com/?assignee=CEA&inventor=saood) (Google Patents)\n- Entreprise : [Therasonic](https://www.therasonic.fr/)"
---

## Project Overview



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
