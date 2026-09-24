---
title: SWARM Motion Planning via Fluid Dynamics
description: Novel motion planning algorithm for non-holonomic robot swarms using Navier-Stokes equations with a 15-robot experimental platform.
img: assets/img/swarm_robots.webp
github: https://github.com/adnan-saood/swarm-robot_firmware
category: "Swarm robotics"
importance: 7
title_fr: "Planification de mouvement d'essaims par la dynamique des fluides"
description_fr: "Un algorithme de planification de mouvement pour essaims de robots non holonomes fondé sur les équations de Navier-Stokes, testé sur une plateforme de 15 robots."
category_fr: "Robotique en essaim"
body_fr: "## Présentation du projet\n\nCe projet de fin d'études s'attaque à l'un des défis les plus stimulants de la robotique : **coordonner le mouvement de grands groupes de robots**. En s'inspirant de la dynamique des fluides et de l'élégance mathématique des équations de Navier-Stokes, nous avons développé une approche de la robotique en essaim qui traite les formations de robots comme des écoulements.\n\n## Motivation : les solutions de la nature\n\nLes essaims naturels (nuées d'oiseaux, bancs de poissons, foules) montrent qu'une coordination élégante peut émerger sans contrôle central. L'idée clé : ces phénomènes peuvent être décrits mathématiquement par les principes de la dynamique des fluides.\n\n## Innovation technique\n\n### Algorithme central\n\nLes équations de Navier-Stokes, fondamentales en mécanique des fluides, décrivent le mouvement des fluides visqueux :\n\n$$\\frac{\\partial \\mathbf{v}}{\\partial t} + (\\mathbf{v} \\cdot \\nabla)\\mathbf{v} = -\\nabla p + \\nu \\nabla^2 \\mathbf{v} + \\mathbf{f}$$\n\nNous les discrétisons et les adaptons aux essaims de robots :\n- **Champ de vitesse** → vitesses des robots\n- **Champ de pression** → forces d'évitement des collisions\n- **Viscosité** → cohésion de la formation\n- **Forces volumiques** → obstacles, objectifs, commandes externes\n\n### Principales innovations\n\n- **Viscosité adaptative** : réglage dynamique entre formation serrée et dispersion rapide\n- **Contraintes non holonomes** : prise en compte des limites de braquage d'un robot différentiel (un vrai robot ne peut pas se déplacer latéralement)\n- **Coordination multi-échelle** : fonctionnement simultané à l'échelle micro (évitement), méso (groupe local) et macro (trajectoire globale)\n\n## Plateforme matérielle\n\nUn essaim de 15 robots conçu sur mesure :\n- **cinématique différentielle** (deux roues motrices) ;\n- **un microcontrôleur ARM Cortex-M4** par robot ;\n- **réseau maillé sans fil à 2,4 GHz** ;\n- **boucle de contrôle à 100 Hz** pour la coordination en temps réel ;\n- **batterie Li-Po** : 4 heures d'autonomie.\n\n## Expériences\n\nDémonstrations :\n- maintien de formation dans des environnements encombrés ;\n- planification de mouvement à l'échelle (jusqu'à 15 robots) ;\n- densité d'essaim adaptative pour franchir les obstacles ;\n- robustesse aux pannes de robots (dégradation progressive).\n\n## Impact et applications\n\n- **Recherche et sauvetage** : exploration autonome de terrains inconnus\n- **Surveillance environnementale** : réseaux de capteurs distribués\n- **Art et spectacle** : sculptures dynamiques formées par des essaims\n- **Logistique industrielle** : transport coordonné de marchandises en entrepôt\n\n## Ressources\n\n- GitHub : [swarm-robot_firmware](https://github.com/adnan-saood/swarm-robot_firmware) (code embarqué)\n\n## État actuel\n\nPreuve de concept démontrée ; développement mis en pause au profit de la thèse. Le code et la conception matérielle sont documentés pour de futurs étudiants."
---

## Project Overview



This undergraduate/early-career project tackles one of robotics' most compelling challenges: **coordinated motion planning for large robot groups**. By drawing inspiration from fluid dynamics and the mathematical elegance of the Navier-Stokes equations, we developed a novel approach to swarm robotics that treats robot formations as fluid flow patterns.

## Motivation: Nature's Solutions

Observing natural swarms — flocks of birds, schools of fish, crowds of humans — reveals that elegant coordination emerges without central control. The key insight: these phenomena can be mathematically described using fluid dynamics principles.

## Technical Innovation

### Core Algorithm

The Navier-Stokes equations, fundamental to fluid mechanics, describe the motion of viscous fluids:

$$\frac{\partial \mathbf{v}}{\partial t} + (\mathbf{v} \cdot \nabla)\mathbf{v} = -\nabla p + \nu \nabla^2 \mathbf{v} + \mathbf{f}$$

We discretize and adapt this to robot swarms:
- **Velocity field** → robot velocities
- **Pressure field** → collision avoidance forces
- **Viscosity** → coordination/formation tightness
- **Body forces** → obstacles, goals, external inputs

### Key Innovations

- **Adaptive Viscosity Control**: Dynamic parameter tuning for tight formations vs. rapid dispersion
- **Non-holonomic Constraints**: Integration of differential-drive steering limits (real robots can't move sideways)
- **Multi-Scale Coordination**: Simultaneous operation at micro (collision avoidance), meso (local group), and macro (global path planning) scales

## Hardware Platform

Custom-designed 15-robot swarm platform:
- **Differential-drive kinematics** (2-wheel steering)
- **ARM Cortex-M4 microcontroller** per robot
- **2.4 GHz wireless mesh networking**
- **100 Hz control loop** for real-time coordination
- **Li-Po battery**: 4-hour runtime

## Experiments

Demonstrated:
- Formation-keeping through cluttered environments
- Scalable motion planning (tested up to 15 robots)
- Adaptive swarm density for obstacle negotiation
- Robustness to robot failures (graceful degradation)

## Impact & Applications

- **Search and rescue**: Autonomous exploration of unknown terrain
- **Environmental monitoring**: Distributed sensor networks
- **Entertainment/art**: Dynamic sculptural swarm displays
- **Industrial logistics**: Coordinated material transport in warehouses

## Related Resources

- GitHub: [swarm-robot_firmware](https://github.com/adnan-saood/swarm-robot_firmware) (embedded control code)
- Blog: "Swarm Robotics Inspired by Fluid Dynamics" (TODO: link to blog post once written)

## Current Status

Demonstrated proof-of-concept; further development paused in favor of higher-priority PhD research. Code and hardware designs documented for future students.
