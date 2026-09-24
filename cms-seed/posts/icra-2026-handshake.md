---
layout: post
title: "ICRA 2026 Recap: Contributing Factors in Human-Robot Handshake"
date: 2026-06-10
description: What makes a natural, comfortable handshake between humans and robots? Results from our empirical study.
tags: ICRA conference HRI human-robot-interaction haptics
categories: research publication
related_posts: true
giscus_comments: true
title_fr: "Retour sur ICRA 2026 : les facteurs d'une poignée de main humain-robot réussie"
description_fr: "Qu'est-ce qui rend une poignée de main entre humain et robot naturelle et agréable ? Les résultats de notre étude empirique."
body_fr: "## La poignée de main comme question de recherche\n\nL'une des interactions les plus fondamentales, et pourtant peu étudiées, entre humains et robots est la **poignée de main**. Au-delà du salut social, une poignée de main porte une information tactile riche sur la confiance, l'intention, le confort et la personnalité. Mais qu'est-ce qui rend naturelle la poignée de main d'un robot ? Et peut-on le mesurer ?\n\nCet été, à **ICRA 2026 à Vienne**, la Prof. Adriana Tapus et moi avons présenté notre article *« Contributing Factors in Human-Robot Handshake: Compliance, Hand Grip, and Synchrony »*, qui répond à ces questions par une expérience contrôlée et une analyse statistique.\n\n---\n\n## L'expérience\n\nNous avons recruté 16 participants qui ont serré la main d'un **bras collaboratif Franka Emika Panda** équipé d'une pince adaptative sur mesure (inspirée de la main Meka), qui nous permettait de moduler :\n\n- **la compliance** (souplesse/raideur) : rigide, modérée, souple ;\n- **la force de préhension** : douce, normale, ferme ;\n- **la synchronie temporelle** : le robot suit parfaitement le mouvement humain, ou avec un léger retard / une légère avance.\n\nSoit un **plan factoriel 2×2×2**, 16 conditions au total. Après chaque poignée de main, les participants évaluaient le confort, le naturel et la confiance sur des échelles validées. Nous avons aussi enregistré les mouvements et les courbes de force pour une analyse a posteriori.\n\n---\n\n## Principaux résultats\n\n**(Résultats sous presse ; article complet disponible sur demande)**\n\n**1. La compliance compte le plus**\nUn contact souple et compliant est préféré par tous. Une prise rigide est perçue comme agressive ou défensive. Un principe de conception clé : **adapter l'impédance de la prise pour qu'elle paraisse humaine, pas mécanique.**\n\n**2. La force doit s'adapter au contexte**\nUne force modérée et socialement adaptée fait mieux que les deux extrêmes :\n- trop serré = inconfortable, agressif, froid ;\n- trop lâche = impersonnel, désinvolte, sans engagement.\n\nLe bon réglage : une force qui suit l'intention et la personnalité de l'humain.\n\n**3. La synchronie est essentielle**\nUn robot qui *anticipe* le mouvement de la main crée une interaction plus fluide et naturelle qu'un robot qui réagit passivement. Quand le robot saisit un peu avant que la main humaine soit totalement tendue, ou relâche un peu avant que la prise humaine se desserre, l'interaction paraît coordonnée et intentionnelle.\n\n<div class=\"row mt-3 mb-3\">\n    <div class=\"col-sm mt-3 mt-md-0\">\n        <figure><img src=\"assets/img/blog/post03_1.jpg\" class=\"img-fluid rounded z-depth-1\" alt=\"ICRA 2026 à Vienne\" title=\"ICRA 2026 à Vienne\" loading=\"lazy\"></figure>\n    </div>\n    <div class=\"col-sm mt-3 mt-md-0\">\n        <figure><img src=\"assets/img/blog/post06.jpg\" class=\"img-fluid rounded z-depth-1\" alt=\"Démonstration de poignée de main\" title=\"Démonstration de poignée de main\" loading=\"lazy\"></figure>\n    </div>\n</div>\n\n---\n\n## Pourquoi c'est important\n\n### Pour la robotique sociale\nCes résultats nourrissent des recommandations de conception pour les robots collaboratifs et de service. Un robot qui serre la main avec soin et attention paraît plus digne de confiance et moins menaçant.\n\n### Pour les plateformes humanoïdes\nLes systèmes de contrôle du haut du corps (mains Meka, bras industriels) peuvent désormais régler leur compliance, leurs profils de force et leurs algorithmes de prédiction de mouvement à partir de ces résultats empiriques.\n\n### Pour la confiance en IHR\nL'interaction tactile est un canal puissant pour créer du lien. Une bonne poignée de main ouvre la conversation.\n\n### Pour la robotique d'assistance\nLes personnes âgées ou en situation de handicap bénéficient particulièrement de robots capables de répondre au toucher avec sensibilité et respect.\n\n---\n\n## Le contexte plus large\n\nCe travail se situe au croisement de la **perception tactile**, du **contrôle robotique** et des **facteurs humains**. Il s'inscrit dans mes recherches sur le toucher en robotique :\n\n- **Matrice tactile pour main humanoïde** : permettre aux robots de *percevoir* le contact de la main\n- **Affordance tactile générative** : permettre aux robots de *prédire* les sensations à venir\n- **Interface haptique robotique souple** : permettre aux robots d'*exprimer* des émotions par le toucher\n\nUne poignée de main est donc un condensé : elle combine perception (le robot sent la prise humaine), prédiction (il anticipe le relâchement) et expression (il transmet de l'attention par la compliance et le timing).\n\n---\n\n## Merci à Vienne et à la communauté IHR\n\nICRA 2026 a été un rassemblement incroyable. L'énergie, l'ampleur, la qualité des travaux : de quoi se rappeler pourquoi nous faisons ce métier. Merci tout particulièrement :\n- à la Prof. Adriana Tapus pour son encadrement sans faille ;\n- à l'équipe Franka Emika pour un matériel collaboratif robuste ;\n- à nos 16 courageux participants, qui ont serré la main d'un robot 16 fois chacun ;\n- à la communauté IHR, qui repousse sans cesse les limites de ce qui rend une interaction *juste*.\n\n---\n\n## Participer\n\nSi la méthodologie complète, l'analyse statistique ou la réplication de l'étude vous intéressent, l'article sera disponible sur arXiv et IEEE Xplore. Nous publions aussi le jeu de données de capture de mouvement pour de futures recherches.\n\n---\n\n**À voir aussi :**\n- Le [projet Poignée de main humain-robot](projects/?p=handshake) pour tous les détails techniques\n- Publication : Saood & Tapus (2026), ICRA, article ThI1I.171\n- Et aussi : [Matrice tactile pour main humanoïde](projects/?p=tactile-array) et [Interface haptique robotique souple](projects/?p=haptic-interface)"
---

## A Handshake as a Research Question

One of the most fundamental—yet understudied—interactions between humans and robots is the **handshake**. Beyond its role as a social greeting, a handshake encodes rich tactile information about trust, intention, comfort, and personality. But what makes a robot's handshake feel *natural*? And can we quantify it?

This summer at **ICRA 2026 in Vienna**, my co-author Prof. Adriana Tapus and I presented our paper, *"Contributing Factors in Human-Robot Handshake: Compliance, Hand Grip, and Synchrony,"* which answers these questions through controlled experiment and statistical analysis.

---

## The Experiment

We recruited 16 participants and had them perform handshakes with a **Franka Emika Panda collaborative robot arm** equipped with a custom adaptive gripper (based on the Meka hand) that allowed us to modulate:

- **Compliance** (softness/stiffness): Stiff, moderate, soft
- **Grip force**: Gentle, normal, firm
- **Temporal synchrony**: Robot tracking human motion perfectly, or with slight delays/early initiations

This gave us a **2×2×2 factorial design**—16 total conditions. After each handshake, participants rated comfort, naturalness, and trustworthiness on validated scales. We also recorded motion data and force curves for post-hoc analysis.

---

## Key Findings

**(Results now in press; full paper available on request)**

**1. Compliance Matters Most**  
Soft, compliant contact is universally preferred. Rigid, stiff grasps are perceived as aggressive or defensive. A key design principle: **adapt your grip impedance to feel human-like, not mechanical.**

**2. Grip Force Must Be Context-Aware**  
Moderate, socially-appropriate force outperforms both extremes:
- Too tight = uncomfortable, aggressive, unpersonal
- Too loose = impersonal, dismissive, low-effort

The sweet spot: force modulation that tracks human intent and personality.

**3. Synchrony is Critical**  
Robots that *anticipate* human hand motion create more fluid, natural interactions than those who react passively. When the robot grasps slightly before the human hand is fully extended—or releases slightly before the human's grip loosens—the interaction feels coordinated and intentional.

<div class="row mt-3 mb-3">
    <div class="col-sm mt-3 mt-md-0">
        <figure><img src="assets/img/blog/post03_1.jpg" class="img-fluid rounded z-depth-1" alt="ICRA 2026 Vienna" title="ICRA 2026 Vienna" loading="lazy"></figure>
    </div>
    <div class="col-sm mt-3 mt-md-0">
        <figure><img src="assets/img/blog/post06.jpg" class="img-fluid rounded z-depth-1" alt="Handshake demonstration" title="Handshake demonstration" loading="lazy"></figure>
    </div>
</div>

---

## Why This Matters

### For Social Robotics
These findings inform design guidelines for collaborative robots and service robots. A robot that shakes hands with care and attention will be perceived as more trustworthy and less threatening.

### For Humanoid Platforms
Upper-body control systems (Meka hands, industrial arms) can now tune their compliance, force profiles, and motion prediction algorithms based on these empirical results.

### For Building Trust in HRI
Tactile interaction is a powerful channel for building rapport. A good handshake is a conversation starter.

### For Assistive Robotics
Elderly or disabled users especially benefit from robots that can respond to physical touch with sensitivity and respect.

---

## The Broader Context

This work sits at the intersection of **tactile sensing**, **robot control**, and **human factors**. It builds on my broader research into touch for robotics:

- **Tactile Array for Humanoid Hand** — enabling robots to *sense* hand contact
- **Generative Tactile Affordance** — enabling robots to *predict* tactile futures
- **Soft Robotic Haptic Interface** — enabling robots to *express* emotion through touch

A handshake, then, is a microcosm: it combines sensing (the robot feels the human's grasp), prediction (the robot anticipates release), and expression (the robot conveys care through compliance and timing).

---

## Thank You to Vienna & the HRI Community

ICRA 2026 was an incredible gathering. The energy, the scale, the quality of research—it reaffirmed why we do this work. Special thanks to:
- Prof. Adriana Tapus for unwavering mentorship
- The Franka Emika team for robust collaborative hardware
- Our 16 brave participants who shook hands with a robot 16 times each
- The HRI community for pushing the frontier on what makes interaction *feel* right

---

## Get Involved

If you're curious about the full methodology, statistical analysis, or want to replicate the study, the paper will be available on arXiv and IEEE Xplore. We're also releasing the motion capture dataset to enable future research.

---

**Related:**
- See the [Human-Robot Handshake project](projects/?p=handshake) for full technical details
- Publications: Saood & Tapus (2026), ICRA, Paper ThI1I.171
- See also: [Tactile Array for Humanoid Hand](projects/?p=tactile-array) and [Soft Robotic Haptic Interface](projects/?p=haptic-interface)
