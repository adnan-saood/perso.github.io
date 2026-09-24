---
title: Contributing Factors in Human-Robot Handshake
description: Empirical study of what makes a natural, comfortable handshake between humans and robots — compliance, hand grip, and temporal synchrony.
img: assets/img/handshake.jpg
github: https://github.com/adnan-saood/franka_handshake_ros2
category: "Human–robot interaction"
importance: 4
featured: true
title_fr: "Les facteurs d'une poignée de main humain-robot réussie"
description_fr: "Étude empirique de ce qui rend une poignée de main entre humain et robot naturelle et agréable : compliance, force de préhension et synchronie."
category_fr: "Interaction humain-robot"
body_fr: "## Présentation du projet\n\nCette recherche explore l'une des interactions les plus fondamentales, et pourtant peu étudiées, entre humains et robots : **la poignée de main**. Au-delà du rituel social, une poignée de main porte des informations riches sur la confiance, l'intention et le confort : des signaux tactiles que les robots devraient comprendre et rendre.\n\nNous avons mené une étude empirique contrôlée pour identifier et quantifier les **facteurs clés qui rendent une poignée de main humain-robot naturelle et agréable**.\n\n## Questions de recherche\n\n- Quelles *propriétés physiques* d'une poignée de main robotique sont perçues comme naturelles ou non ?\n- Comment la **compliance** (souplesse/raideur), la **force de préhension** et la **synchronie temporelle** du robot influencent-elles le confort et la confiance ?\n- Peut-on construire un modèle qui prédit la qualité d'une poignée de main à partir de ces paramètres ?\n\n## Protocole expérimental\n\n### Plateforme matérielle\n\nBras collaboratif Franka Emika Panda équipé d'une pince adaptative sur mesure (inspirée de la main Meka) capable de moduler :\n- la force de préhension (retour par capteur de force) ;\n- la compliance (paramètres ressort-amortisseur) ;\n- le timing (synchronisation de la saisie et du relâchement avec le mouvement humain).\n\n### Déroulé de l'étude\n\nLes participants ont serré la main du robot dans des conditions variées de façon systématique :\n- **Niveaux de compliance** : rigide, modéré, souple (comme différentes « personnalités »)\n- **Forces de préhension** : douce, normale, ferme\n- **Types de synchronie** : suivi parfait de la main, léger retard, initiation anticipée\n\nChaque poignée de main a été enregistrée, et les participants ont évalué le confort, le naturel et la confiance.\n\n## Principaux résultats\n\n(Résultats de l'article ICRA 2026, publication en cours)\n\n- **La compliance compte le plus** : un contact souple est préféré par tous ; une prise rigide est perçue comme agressive ou défensive\n- **Moduler la force** : une force modérée et adaptée au contexte fait mieux qu'une force constante ; trop serré est inconfortable, trop lâche paraît impersonnel\n- **La synchronie est essentielle** : un robot qui *anticipe* le mouvement de la main crée une interaction plus fluide et naturelle qu'un robot qui réagit passivement\n\n## Applications\n\n- **Robotique sociale** : recommandations pour régler la « personnalité » des robots collaboratifs\n- **Plateformes humanoïdes** : un contrôle du haut du corps plus proche de l'humain\n- **Confiance humain-robot** : l'interaction tactile comme canal pour créer du lien\n- **Robotique d'assistance** : des poignées de main adaptées aux personnes âgées ou en situation de handicap\n\n## Publications\n\n- **Saood & Tapus (2026).** « Contributing Factors in Human-Robot Handshake: Compliance, Hand Grip, and Synchrony. » *Proc. IEEE International Conference on Robotics and Automation (ICRA)*, 2026.\n\n## Projets liés\n\n- Matrice tactile pour main humanoïde\n- Affordance tactile générative\n- Interface haptique robotique souple"
---

## Project Overview



This research explores one of the most fundamental yet understudied interactions between humans and robots: **the handshake**. Beyond its social ritual importance, a handshake encodes rich information about trust, intention, and comfort — tactile signals that robots should understand and reciprocate.

We conducted a controlled empirical study to identify and quantify the **key factors that make a human-robot handshake feel natural and pleasant**.

## Research Questions

- What *physical properties* of a robot handshake are perceived as natural or unnatural?
- How do robots' **compliance** (softness/stiffness), **grip force**, and **temporal synchrony** affect human comfort and trust?
- Can we build a model to predict handshake quality from these parameters?

## Experimental Design

### Hardware Platform

Franka Emika Panda collaborative robot arm equipped with a custom adaptive gripper (based on Meka hand) that can modulate:
- Grip force (load-cell feedback)
- Compliance (spring-damper parameters)
- Timing (grasp/release synchronization with human motion)

### Study Protocol

Recruited participants performed handshakes with the robot under systematically varied conditions:
- **Compliance levels**: Stiff, moderate, soft (simulating different "personalities")
- **Grip forces**: Gentle, normal, firm
- **Synchrony types**: Perfect human tracking, slight delay, early initiation

Each handshake was recorded, and participants rated comfort, naturalness, and trustworthiness.

## Key Findings

(Results from ICRA 2026 paper — publication in press)

- **Compliance matters most**: Soft, compliant contact is universally preferred; rigid grasps are perceived as aggressive or defensive
- **Grip force modulation**: Moderate, context-appropriate force outperforms constant force; too-tight is uncomfortable; too-loose feels impersonal
- **Synchrony is critical**: Robots that *anticipate* human hand motion create more fluid, natural interactions than those who react passively

## Applications

- **Social robotics**: Design guidelines for collaborative robot personality tuning
- **Humanoid platforms**: Improving upper-body control to feel more human-like
- **Human-robot trust**: Tactile interaction as a channel for building rapport
- **Assistive robotics**: Adapted handshake protocols for elderly or disabled users

## Publications

- **Saood & Tapus (2026).** "Contributing Factors in Human-Robot Handshake: Compliance, Hand Grip, and Synchrony." *Proc. IEEE International Conference on Robotics and Automation (ICRA)*, 2026.

## Related Projects

- Tactile Array for Humanoid Hand
- Generative Tactile Affordance
- Soft Robotic Haptic Interface
