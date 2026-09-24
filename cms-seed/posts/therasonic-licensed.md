---
layout: post
title: "From PhD Thesis to Licensed Technology: How Our Focused-Ultrasound Robot Became Therasonic"
date: 2026-07-15
description: The journey from research at ICube to real-world deployment—patent EP4445859A1 licensed to startup Therasonic
tags: tech-transfer robotics patent licensing innovation
categories: research impact
related_posts: true
giscus_comments: true
title_fr: "De la thèse à la technologie licenciée : comment notre robot à ultrasons focalisés est devenu Therasonic"
description_fr: "De la recherche à ICube au déploiement concret : le brevet EP4445859A1 licencié à la start-up Therasonic"
body_fr: "## Un rêve de chercheur devenu réalité\n\nQuand j'ai commencé mon master au laboratoire ICube (Université de Strasbourg) en 2022, je m'attaquais à un défi fondamental de la neuromédecine : **comment faire passer des médicaments à travers la barrière hémato-encéphalique, en toute sécurité ?** La BHE est une membrane protectrice essentielle, mais elle empêche aussi environ 99 % des médicaments à grosses molécules d'atteindre les tumeurs cérébrales ou de traiter Parkinson, Alzheimer et d'autres maladies neurologiques.\n\nLa réponse développée par notre équipe : **un système d'ultrasons focalisés assisté par robot**, capable d'ouvrir la BHE de façon temporaire, sûre et réversible, sur des cibles précises. Cela ressemble à de la science-fiction, mais la physique est solide, et le besoin clinique est urgent.\n\nEn juillet 2026, je suis ravi d'annoncer que nos travaux ont été **brevetés (EP4445859A1) et licenciés en exclusivité à Therasonic**, une start-up issue de NeuroSpin (CEA). Ce n'est pas seulement une étape académique : c'est le début d'un vrai système thérapeutique qui atteindra les patients.\n\n---\n\n## La recherche derrière la technologie\n\nL'approche technique repose sur trois innovations :\n\n**1. La planification de trajectoires robotiques**\nNous avons développé des algorithmes d'optimisation qui calculent des trajectoires lisses et sans collision pour l'effecteur du robot, afin de positionner les transducteurs ultrasonores selon des angles précis. Cela garantit un couplage acoustique optimal tout en limitant l'exposition hors cible et les effets thermiques indésirables.\n\n**2. Contrôle en temps réel et sécurité**\nSur la base de ROS, nous avons créé des chaînes de contrôle en boucle fermée qui :\n- ajustent en temps réel la puissance et la fréquence des ultrasons ;\n- surveillent l'imagerie échographique en mode B et Doppler ;\n- s'arrêtent automatiquement en cas d'anomalie.\n\n**3. La validation préclinique**\nDes tests rigoureux ex vivo et in vivo ont confirmé l'ouverture de la BHE et l'extravasation des nanoparticules, ouvrant la voie à la translation clinique.\n\n---\n\n## Du laboratoire à la réalité commerciale\n\nLe chemin de la thèse à la licence :\n\n- **2022–2023 :** conception de la recherche, validation expérimentale, rédaction\n- **2023 :** dépôt de la demande de brevet (avec la SATT Conectus et le CEA)\n- **2024 :** délivrance du brevet (EP4445859A1)\n- **2026 :** licence exclusive accordée à Therasonic\n\nCe type de transfert de technologie demande plus que de la bonne science : il faut des partenariats, une anticipation réglementaire et une équipe de start-up assez audacieuse pour croire au projet. Les cofondateurs de Therasonic et l'équipe du CEA en sont l'exemple.\n\n<div class=\"row mt-3\">\n    <div class=\"col-sm mt-3 mt-md-0\">\n        <figure><img src=\"assets/img/blog/post02_1.jpg\" class=\"img-fluid rounded z-depth-1\" alt=\"Transfert de technologie vers Therasonic\" title=\"Transfert de technologie vers Therasonic\" loading=\"lazy\"></figure>\n    </div>\n    <div class=\"col-sm mt-3 mt-md-0\">\n        <figure><img src=\"assets/img/blog/post02_2.jpg\" class=\"img-fluid rounded z-depth-1\" alt=\"Brevet et licence\" title=\"Brevet et licence\" loading=\"lazy\"></figure>\n    </div>\n</div>\n\n---\n\n## Et maintenant ?\n\nEn tant qu'**ingénieur consultant chez Therasonic**, je reste pleinement impliqué. L'entreprise est en train de :\n- finaliser la conformité réglementaire du premier prototype ;\n- préparer les essais cliniques (vers 2028) ;\n- viser une mise sur le marché vers 2030 ;\n- lever des fonds en financement participatif pour accélérer le développement.\n\nLes ultrasons focalisés ne sont pas nouveaux (des décennies de recherche), mais **leur combinaison avec la robotique de précision et la planification de trajectoires par apprentissage automatique** ouvre une nouvelle frontière clinique.\n\n---\n\n## Remerciements\n\nCe résultat est le fruit de 15 ans de recherche collaborative entre le CEA/NeuroSpin, BioMaps et ICube. Merci tout particulièrement :\n- à la **Prof. Adriana Tapus** (ma directrice de thèse, qui continue de me soutenir) ;\n- au **Dr Benoit Larrat et au Dr Jonathan Vappou** (pionniers des ultrasons focalisés au CEA) ;\n- au **Prof. Florent Nageotte** (équipe robotique d'ICube) ;\n- à CEA Investissement, à l'ANR, à Bpifrance et aux partenaires régionaux qui ont financé les travaux ;\n- et surtout à la **SATT Conectus**, qui a mené le transfert de technologie avec excellence.\n\n---\n\n## Ce que cela signifie\n\nPour un chercheur, c'est la preuve que son travail compte au-delà des salles de conférence. Pour les patients atteints de Parkinson, de Huntington, de SLA, d'Alzheimer ou de tumeurs cérébrales, c'est un espoir : celui que, d'ici quelques années, un petit robot ouvre une voie de traitement jusque-là impossible.\n\nPour les détails techniques, consultez nos publications sur la modélisation d'un champ acoustique contrôlé (CRAS 2023) et sur la sécurité et l'efficacité des ultrasons (ISTU 2023), sur la page Publications. Ou rendez-vous sur [therasonic.fr](https://www.therasonic.fr/) pour en savoir plus sur l'entreprise.\n\n---\n\n**À voir aussi :**\n- Le [projet Therasonic / BHE](projects/?p=bbb-opening) pour tous les détails techniques\n- Publications : CRAS 2023, ISTU 2023, sur la page Publications"
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
        <figure><img src="assets/img/blog/post02_1.jpg" class="img-fluid rounded z-depth-1" alt="Therasonic tech transfer" title="Therasonic tech transfer" loading="lazy"></figure>
    </div>
    <div class="col-sm mt-3 mt-md-0">
        <figure><img src="assets/img/blog/post02_2.jpg" class="img-fluid rounded z-depth-1" alt="Patent and licensing" title="Patent and licensing" loading="lazy"></figure>
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
- See the [Therasonic BBB project](projects/) for full technical details
- Publications: CRAS 2023, ISTU 2023 on the Publications page
