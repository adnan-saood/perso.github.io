---
title: Generative Factorized Model of Action-Conditioned Tactile Affordance
description: Learning how robots can predict and understand tactile interactions through generative models that condition on actions and prior touch.
img: assets/img/tactile_affordance.jpg
github: https://github.com/adnan-saood/fractal_ros2
category: "Tactile sensing"
importance: 2
featured: true
title_fr: "Modèle génératif factorisé de l'affordance tactile conditionnée par l'action"
description_fr: "Apprendre aux robots à prédire et comprendre les interactions tactiles grâce à des modèles génératifs conditionnés par l'action et le toucher passé."
category_fr: "Perception tactile"
body_fr: "## Présentation du projet\n\nC'est l'axe phare actuel de ma thèse : développer un **modèle génératif et factorisé** des affordances tactiles conditionnées par l'action. Le but est d'apprendre aux robots à prédire et comprendre les sensations tactiles avant et pendant le contact physique, pour une manipulation plus fine, une interaction humain-robot plus sûre et une meilleure compréhension des objets et des surfaces.\n\n## L'idée centrale\n\nPlutôt que de traiter les données tactiles comme un flux passif, ce travail pose deux questions : **« Quelles sensations tactiles dois-je attendre si j'effectue l'action X sur l'objet Y ? »** et **« Que me disent ces signaux tactiles sur ce qui se passe ? »**\n\nLes idées clés :\n- **Factorisé** : séparer les différentes dimensions de la compréhension tactile (géométrie du contact, propriétés du matériau, dynamique, intention)\n- **Conditionné par l'action** : les prédictions tiennent compte de ce que le robot *compte faire*, pas seulement de ce qu'il observe\n- **Génératif** : apprendre un modèle capable d'*imaginer* les sensations tactiles à venir et de les comparer à la réalité\n\n## Approche technique\n\n### Apprentissage à partir des données\n\nLe modèle est entraîné sur de grands jeux de données d'interactions tactiles, collectés sur la plateforme de main humanoïde avec des actions contrôlées (appuyer, caresser, saisir, relâcher…) sur des objets et matériaux variés.\n\n### Représentation multimodale\n\nLa factorisation découpe la compréhension tactile en sous-modèles indépendants mais liés :\n- **Modèle de contact** : où et avec quelle profondeur le robot touche-t-il ?\n- **Modèle de matériau** : quelle souplesse, quelle texture, quelles propriétés thermiques ?\n- **Modèle dynamique** : comment forces et vibrations évoluent-elles pendant l'interaction ?\n- **Modèle d'intention** : que cherche à faire le robot ? (saisir, caresser, explorer, pousser)\n\n### Inférence en temps réel\n\nLes modèles appris permettent de prédire et d'interpréter les signaux tactiles en ligne, immédiatement, au service du contrôle réactif et de la sécurité.\n\n## Applications\n\n- **Manipulation dextre** : manipuler des objets fragiles avec assurance, en adaptant la prise au retour tactile prédit\n- **Exploration d'objets** : découvrir les propriétés d'un objet par le toucher, sans vision\n- **Retour haptique** : permettre au robot de répondre de façon appropriée pendant l'interaction\n- **Sécurité** : détecter anomalies et situations dangereuses quand le toucher contredit la prédiction\n- **Collaboration humain-robot** : des robots qui anticipent le toucher humain et y répondent justement\n\n## État actuel\n\nLe développement du modèle est en cours ; les premiers résultats montrent une généralisation prometteuse, sans exemple préalable, à de nouveaux objets et combinaisons de matériaux. Une publication est en préparation.\n\n## Ressources\n\n- Matériel tactile : voir le projet *Matrice tactile pour main humanoïde*\n- Plateforme : main robotique Meka intégrant des capteurs tactiles PaXini"
---

## Project Overview



This is the current flagship direction of my PhD research — developing a **generative, factorized model** of action-conditioned tactile affordances. The goal is to teach robots to predict and understand tactile sensations before and during physical contact, enabling smarter manipulation, safer human-robot interaction, and richer understanding of objects and surfaces.

## The Core Innovation

Rather than treating tactile data as a passive sensor stream, this work asks: **"What tactile sensations should I expect when I perform action X on object Y?"** and **"What do these tactile signals tell me about what's happening?"**

The key insights are:
- **Factorized**: Separating different aspects of tactile understanding (contact geometry, material properties, dynamics, intent)
- **Action-conditioned**: Predictions are informed by what the robot *intends to do*, not just what it observes
- **Generative**: Learning a model that can *imagine* tactile futures and compare them to reality

## Technical Approach

### Data-Driven Learning

The model is trained on large-scale tactile interaction datasets, collected from the humanoid hand platform with controlled robot actions (pressing, stroking, grasping, releasing, etc.) and diverse objects and materials.

### Multi-Modal Representation

Factorization splits tactile understanding into independent but related sub-models:
- **Contact model**: Where and how deeply is the robot touching?
- **Material model**: What's the softness, texture, thermal properties?
- **Dynamic model**: How do forces and vibrations evolve over the interaction?
- **Intent model**: What is the robot trying to accomplish? (grasp, caress, explore, push)

### Real-Time Inference

The learned models enable immediate, online prediction and interpretation of tactile signals, supporting reactive control and human-robot safety.

## Applications

- **Dexterous Manipulation**: Robots that can handle delicate objects with confidence, adapting grip to predicted tactile feedback
- **Object Exploration**: Tactile-driven discovery of object properties without requiring visual input
- **Haptic Feedback**: Enabling robots to provide appropriate haptic responses during interaction
- **Safety**: Detecting anomalies or dangerous conditions through tactile prediction violations
- **Human-Robot Collaboration**: Robots that anticipate and respond to human touch appropriately

## Current Status

Model development is underway; preliminary results show promising zero-shot generalization to novel objects and material combinations. Research is being prepared for publication.

## Related Resources

- Tactile hardware: See *Tactile Array for Humanoid Hand* project
- Hardware platform: Meka Robotic Hand with paxini tactile sensor integration
- Blog: See "Generative Tactile Affordances" post (TODO: to be written by Adnan)
