---
layout: post
title: "A Touch of Feeling in Robotics (Adapted from ENSTA Feature)"
date: 2026-01-10
description: Why touch is the frontier of robotics—bridging the gap between machines and humanity.
tags: robotics touch haptics HRI research
categories: research outreach
related_posts: true
giscus_comments: true
title_fr: "Un peu de sensibilité dans la robotique (adapté d'un article de l'ENSTA)"
description_fr: "Pourquoi le toucher est la nouvelle frontière de la robotique, entre les machines et l'humain."
body_fr: "> Cet article est une version adaptée d'un article institutionnel de l'ENSTA, publié en janvier 2026 sur [ensta.fr](https://www.ensta.fr). Il est republié ici avec davantage de contexte technique et des liens vers mes travaux.\n\n---\n\n## Le sens caché de la robotique\n\nLes robots voient mieux que nous. La vision par ordinateur détecte objets, visages et émotions avec une précision surhumaine. Ils entendent aussi : la reconnaissance vocale rivalise avec l'humain. Ils sentent même les odeurs, avec des capteurs de gaz qui repèrent des traces que nous ne percevons pas.\n\nEt leur toucher ? Rudimentaire. Maladroit. Absent.\n\nPour un domaine qui rêve de robots humanoïdes capables de manipuler, de prendre soin et d'interagir socialement, cette lacune est une faiblesse majeure.\n\n<div class=\"row mt-3 mb-3\">\n    <div class=\"col-sm mt-3 mt-md-0\">\n        <figure><img src=\"assets/img/blog/post08_1.jpg\" class=\"img-fluid rounded z-depth-1\" alt=\"Recherche sur la perception tactile\" title=\"Recherche sur la perception tactile\" loading=\"lazy\"></figure>\n    </div>\n    <div class=\"col-sm mt-3 mt-md-0\">\n        <figure><img src=\"assets/img/blog/post08_2.jpg\" class=\"img-fluid rounded z-depth-1\" alt=\"Interface haptique\" title=\"Interface haptique\" loading=\"lazy\"></figure>\n    </div>\n    <div class=\"col-sm mt-3 mt-md-0\">\n        <figure><img src=\"assets/img/blog/post08_3.jpg\" class=\"img-fluid rounded z-depth-1\" alt=\"Résultats de recherche\" title=\"Résultats de recherche\" loading=\"lazy\"></figure>\n    </div>\n</div>\n\n---\n\n## La frontière : peau artificielle et intelligence tactile\n\n**Le toucher n'est pas un sens unique, c'est une symphonie de sensations :**\n\n- **Pression :** où et avec quelle force me touche-t-on ?\n- **Texture :** cette surface est-elle rugueuse, lisse, collante ?\n- **Température :** est-ce froid ou chaud ?\n- **Vibration :** quelles fréquences parcourent ma peau ?\n- **Proprioception :** où est ma main dans l'espace ?\n- **Kinesthésie :** quelles forces s'exercent sur mes articulations ?\n\nLes humains intègrent tout cela en continu, souvent sans même y penser. On plonge la main dans sa poche et on reconnaît ses clés au toucher. On serre une main et on évalue instantanément l'assurance et les intentions de l'autre.\n\nPour que les robots en fassent autant, il leur faut une **peau artificielle** et une **intelligence tactile**.\n\n---\n\n## Mes recherches : combler le fossé haptique\n\nÀ l'ENSTA Paris, au laboratoire U2IS (Autonomous Systems & Robotics Lab), ma thèse porte précisément sur cette frontière.\n\n### La matrice tactile pour mains humanoïdes\nNous avons développé une **matrice de capteurs tactiles à grande échelle** pour mains de robots humanoïdes : une sorte de peau artificielle collée sur la paume, les doigts et le dos de la main. Le système :\n- mesure la **géométrie du contact** (où l'humain me touche-t-il ?) ;\n- déduit la **morphologie de la main** (quelle est la taille de la main humaine ?) ;\n- détecte **l'intention de préhension** (cette personne serre-t-elle doucement ou fermement ?) ;\n- prédit **l'état émotionnel** (ce toucher exprime-t-il l'assurance, l'hésitation, la confiance ?).\n\nCe n'est pas de la science-fiction : ce matériel fonctionne aujourd'hui dans notre laboratoire.\n\n### De la perception à la prédiction : les affordances génératives\nMais percevoir ne suffit pas. Un robot vraiment intelligent doit aussi *imaginer* : « Quelle sensation tactile dois-je attendre si j'effectue l'action X ? » et « Que me disent ces signaux tactiles sur ce qui se passe ? »\n\nNous développons des **modèles génératifs d'affordance tactile conditionnés par l'action** : des systèmes d'apprentissage qui apprennent à prédire et à comprendre les sensations à venir. Les applications sont considérables :\n\n- **Une manipulation plus sûre :** prédire les forces avant qu'elles ne dépassent les seuils de sécurité\n- **Une préhension dextre :** adapter la prise au retour tactile prédit\n- **L'interaction sociale :** comprendre le toucher humain et y répondre de façon juste\n\n### De la compréhension à l'expression : le retour haptique\nEnfin, un robot socialement intelligent doit *communiquer* par le toucher. Nous avons conçu une **interface haptique pneumatique souple** qui exprime des signaux sociaux involontaires (respiration, battements de cœur) pour transmettre calme et présence à une personne en détresse.\n\nLes premiers résultats montrent qu'un retour haptique synchronisé **réduit significativement l'anxiété** dans l'interaction humain-robot. Un robot qui respire avec vous est un robot en qui l'on a confiance.\n\n---\n\n## Pourquoi maintenant ? Pourquoi c'est important.\n\n### La frontière médicale\nLes robots entrent à l'hôpital et en clinique : robots chirurgicaux, robots de rééducation, robots d'aide aux personnes âgées. Ces systèmes doivent être sûrs, réactifs et *doux*. Et la douceur se communique par le toucher.\n\n### Le vieillissement de la population\nLes pays développés font face à une vague de vieillissement. Les robots feront sans doute partie de la solution pour prendre soin des personnes âgées. Mais un robot sans égards, qui serre trop fort, trop vite, sans sensibilité, sera rejeté. Les robots qui touchent avec attention seront acceptés.\n\n### La frontière sociale\nÀ mesure que les robots quittent les usines pour les maisons, les écoles et les espaces sociaux, ils devront être non seulement fonctionnels mais aussi *de bonne compagnie*. Et la compagnie commence par le toucher.\n\n---\n\n## La vision d'ensemble\n\nMes recherches s'inscrivent dans un mouvement plus large : **passer de l'outil au compagnon**. Pas des compagnons conscients (cela relève encore de la philosophie), mais des robots qui interagissent avec nous de façon intuitive et attentive aux émotions.\n\nCela demande :\n1. **Percevoir :** peau artificielle et transducteurs tactiles\n2. **Comprendre :** modèles d'apprentissage de l'interaction tactile\n3. **Exprimer :** retour haptique et communication incarnée\n4. **Sécuriser :** surveillance tactile en temps réel et contrôle adaptatif\n\n---\n\n## Reconnaissance et élan\n\nEn décembre 2025, j'ai reçu le **2e prix Demeny-Vaucanson** (décerné par la Fédération française de mécanique et des sciences du mouvement) pour ces travaux. Cette reconnaissance par une communauté de mécaniciens et de spécialistes du mouvement me touche profondément.\n\nElle montre que **le toucher devient central en robotique** : non plus une niche, mais une frontière.\n\n---\n\n## Et après ?\n\nMes recherches se poursuivent dans plusieurs directions :\n\n1. **Changer d'échelle :** étendre la matrice tactile à toute la main et au bras\n2. **Apprendre en temps réel :** entraîner les modèles génératifs directement sur le robot, en s'adaptant à chaque utilisateur\n3. **Aller vers la clinique :** travailler avec des hôpitaux pour déployer des robots sensibles au toucher en thérapie et en soin\n4. **Ouvrir :** publier logiciels et plans matériels pour que d'autres laboratoires puissent s'en saisir\n\nMais surtout, j'aimerais inspirer la prochaine génération de roboticiens à se demander : **« Comment concevoir des robots qui touchent avec attention ? »**\n\n---\n\n## En prenant du recul\n\nLes robots vont interagir de plus en plus avec nous. La façon dont ils nous toucheront, avec sensibilité, douceur et réactivité, décidera si nous les accueillons ou si nous les craignons.\n\nAu fond, le toucher n'est pas une affaire de capteurs ou d'algorithmes. **Le toucher, c'est le lien.** Et le lien, c'est ce qui nous rend humains.\n\nSi nous savons construire des robots qui comprennent le toucher, peut-être saurons-nous construire des robots que les humains auront envie de toucher en retour.\n\n---\n\n**À voir aussi :**\n- [Matrice tactile pour main humanoïde](projects/?p=tactile-array)\n- [Modèle génératif factorisé de l'affordance tactile conditionnée par l'action](projects/?p=tactile-affordance)\n- [Interface haptique robotique souple contre l'anxiété](projects/?p=haptic-interface)\n- [Poignée de main humain-robot](projects/?p=handshake)\n- **Prix :** prix Demeny-Vaucanson 2025 (2e place), Journée FeDeV, Inria Saclay\n\n---\n\n*Article publié à l'origine en janvier 2026 sur [ensta.fr](https://www.ensta.fr). Enrichi et republié avec autorisation. Merci à l'ENSTA et à la Prof. Adriana Tapus de m'avoir permis de partager cette histoire.*"
---

> This post is an adapted version of an ENSTA institutional feature article. Original published January 2026 on [ensta.fr](https://www.ensta.fr). Republished here with expanded technical context and cross-links to my research.

---

## The Hidden Sense in Robotics

Robots can see better than humans. Computer vision systems can detect objects, faces, and emotions with superhuman accuracy. They can listen too—speech recognition rivals human performance. And they can even smell, with gas sensors that detect traces of compounds humans miss.

But their touch? Rudimentary. Clumsy. Absent.

For a field that aspires to humanoid robots capable of manipulation, caregiving, and social interaction, this gap is a critical vulnerability.

<div class="row mt-3 mb-3">
    <div class="col-sm mt-3 mt-md-0">
        <figure><img src="assets/img/blog/post08_1.jpg" class="img-fluid rounded z-depth-1" alt="Tactile sensing research" title="Tactile sensing research" loading="lazy"></figure>
    </div>
    <div class="col-sm mt-3 mt-md-0">
        <figure><img src="assets/img/blog/post08_2.jpg" class="img-fluid rounded z-depth-1" alt="Haptic interface" title="Haptic interface" loading="lazy"></figure>
    </div>
    <div class="col-sm mt-3 mt-md-0">
        <figure><img src="assets/img/blog/post08_3.jpg" class="img-fluid rounded z-depth-1" alt="Research results" title="Research results" loading="lazy"></figure>
    </div>
</div>

---

## The Frontier: Artificial Skin & Tactile Intelligence

**Touch is not a single sense—it's a complex symphony of sensations:**

- **Pressure:** Where and how hard am I being touched?
- **Texture:** Is this surface rough, smooth, sticky?
- **Temperature:** Is this cold or warm?
- **Vibration:** What frequencies are oscillating through my skin?
- **Proprioception:** Where is my hand in space?
- **Kinesthesia:** What forces are acting on my joints?

Humans integrate all of these continuously, often without conscious awareness. We reach into our pocket and know the shape of our keys by touch alone. We shake hands and instantly assess the other person's confidence and intent.

For robots to do the same, they need **artificial skin** and **tactile intelligence**.

---

## My Research: Bridging the Haptic Gap

At ENSTA Paris, in the U2IS (Autonomous Systems & Robotics Lab), my PhD work focuses on exactly this frontier.

### The Tactile Array for Humanoid Hands
We've developed a **large-scale tactile sensor array** for humanoid robot hands—essentially artificial skin glued to the palm, fingers, and back of the hand. The system:
- Measures **contact geometry** (where is the human touching me?)
- Infers **hand morphology** (what size is the human hand?)
- Detects **grasp intent** (is this person grasping gently or firmly?)
- Predicts **emotional state** (does their touch convey confidence, hesitation, trust?)

This isn't science fiction. It's working hardware in our lab right now.

### From Sensing to Prediction: Generative Affordances
But sensing alone isn't enough. A truly intelligent robot must also *imagine*: "What tactile sensation should I expect when I perform action X?" and "What do these tactile signals tell me about what's happening?"

We're developing **generative, action-conditioned models of tactile affordance**—machine learning systems that learn to predict and understand tactile futures. The applications are profound:

- **Safer manipulation:** Predict forces before they exceed safety thresholds
- **Dexterous grasping:** Adapt grip to predicted tactile feedback
- **Social interaction:** Understand and respond appropriately to human touch

### From Understanding to Expression: Haptic Feedback
And finally, a socially intelligent robot must *communicate* through touch. We've built a **soft, pneumatic haptic interface** that expresses involuntary social cues—breathing patterns, heartbeats—to convey calm and presence to a human in distress.

Early results show that synchronized haptic feedback **significantly reduces human anxiety** in human-robot interaction. A robot that breathes with you is a robot you trust.

---

## Why Now? Why This Matters.

### The Medical Frontier
Robots are entering hospitals and clinics. Surgical robots, rehabilitation robots, robots for elder care. These systems need to be safe, responsive, and *gentle*. Touch is how we communicate gentleness.

### The Aging Population
The developed world faces a wave of aging. Robots will likely be part of the solution to care for elderly individuals. But an uncaring robot—one that grasps too hard, too fast, without sensitivity—will be rejected. Robots that touch with care will be accepted.

### The Social Frontier
As robots move from factories into homes, classrooms, and social spaces, they'll need to be not just functional but *companionable*. And companionship begins with touch.

---

## The Broader Vision

My research is part of a larger movement in robotics: **moving from tool to companion**. Not sentient companions (that's still philosophy), but robots that interact with humans in intuitive, emotionally informed ways.

This requires:
1. **Sensing:** Artificial skin and tactile transducers
2. **Understanding:** Machine learning models of tactile interaction
3. **Expression:** Haptic feedback and embodied communication
4. **Safety:** Real-time tactile monitoring and adaptive control

---

## Recognition & Momentum

In December 2025, I was awarded the **2nd place Demeny-Vaucanson Prize** (an honor bestowed by the French Federation for Mechanics & Movement Science) for this body of work. This recognition from a community of mechanicists and motor scientists is deeply meaningful.

It signals that **touch is becoming central to robotics research**—not a niche, but a frontier.

---

## What Comes Next?

My research continues in several directions:

1. **Scaling up:** Expanding the tactile array to cover the entire hand and arm
2. **Real-time learning:** Training generative models on-robot, adapting to individual users
3. **Clinical translation:** Partnering with hospitals to deploy tactile-aware robots in therapy and care settings
4. **Open-source:** Releasing software and hardware designs so other labs can build on this work

But perhaps most importantly, I want to inspire the next generation of roboticists to ask: **"How can we make robots that touch with care?"**

---

## The Bigger Picture

Robots will increasingly interact with humans. How they touch—whether with sensitivity, gentleness, and responsiveness—will shape whether we welcome them or fear them.

Touch is not about hardware sensors or machine learning algorithms, in the end. **Touch is about connection.** And connection is what makes us human.

If we can build robots that understand touch, perhaps we can build robots that humans want to touch back.

---

**Related:**
- [Tactile Array for Humanoid Hand](projects/?p=tactile-array)
- [Generative Factorized Model of Action-Conditioned Tactile Affordance](projects/?p=tactile-affordance)
- [Soft Robotic Haptic Interface for Anxiety Reduction](projects/?p=haptic-interface)
- [Human-Robot Handshake](projects/?p=handshake)
- **Award:** Demeny-Vaucanson Prize 2025 (2nd place), Journée FeDeV, Inria Saclay

---

*Original article published January 2026 on [ensta.fr](https://www.ensta.fr). Expanded and republished with permission. Thank you to ENSTA and Prof. Adriana Tapus for the opportunity to share this story.*
