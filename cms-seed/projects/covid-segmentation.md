---
title: COVID-19 Lung CT Image Segmentation using Deep Learning
description: Comparative study of U-Net versus SegNet architectures for semantic segmentation of COVID-19 infected tissue in CT scans.
img: assets/img/covid_segmentation.png
github: https://github.com/adnan-saood/COVID19-DL
category: "Medical robotics & imaging"
importance: 10
title_fr: "Segmentation d'images scanner pulmonaires COVID-19 par apprentissage profond"
description_fr: "Étude comparative des architectures U-Net et SegNet pour la segmentation sémantique des tissus infectés par la COVID-19 sur des images scanner."
category_fr: "Robotique médicale & imagerie"
body_fr: "## Présentation du projet\n\nCe projet de recherche a été mené au plus fort de la pandémie de COVID-19, face au besoin urgent d'outils de diagnostic automatisés pour aider les soignants à repérer rapidement et précisément les infections sur les scanners pulmonaires. Il compare deux architectures d'apprentissage profond de référence, **U-Net** et **SegNet**, pour la segmentation sémantique des zones de tissu infecté.\n\n## Motivation\n\nEn 2021–2022, le diagnostic de la COVID-19 reposait largement sur l'imagerie scanner et son interprétation par des radiologues, ce qui créait plusieurs goulets d'étranglement :\n- **Pénurie de radiologues** : peu d'experts pour analyser un grand volume d'examens\n- **Interprétation subjective** : variabilité dans l'identification des zones infectées\n- **Pression temporelle** : besoin d'un diagnostic rapide pour le triage\n- **Cohérence** : la segmentation automatique garantit une extraction standardisée des caractéristiques\n\nLa segmentation automatique permet aux radiologues de travailler plus vite, de façon plus homogène et avec moins de fatigue.\n\n## Approche technique\n\n### Architectures d'apprentissage profond\n\n#### U-Net\n- **Encodeur-décodeur** avec connexions résiduelles (skip connections)\n- Excellente localisation avec peu de données d'entraînement\n- Conçue à l'origine pour la segmentation d'images biomédicales\n- Contrepartie : empreinte mémoire plus importante\n\n#### SegNet\n- Encodeur-décodeur **économe en mémoire**\n- Utilise les indices de pooling pour le suréchantillonnage (moins de calcul)\n- Inférence plus rapide, moins de mémoire : adaptée à un déploiement clinique\n- Contrepartie possible : précision fine légèrement inférieure\n\n### Données et entraînement\n\n- Base internationale multicentrique de scanners (plus de 500 examens, patients et niveaux de sévérité variés)\n- Annotations d'experts radiologues (vérité terrain)\n- Augmentation de données poussée (rotation, déformation élastique, variation d'intensité)\n- Validation croisée et métriques d'évaluation rigoureuses\n\n### Métriques d'évaluation\n\n- **Score de Dice** (recouvrement entre zones prédites et vérité terrain)\n- **Intersection sur union (IoU)**\n- **Sensibilité et spécificité** (pertinence clinique)\n- **Efficacité de calcul** (temps d'inférence, mémoire)\n\n## Résultats clés\n\nU-Net obtient la meilleure précision de segmentation (Dice ≈ 0,92), tandis que SegNet offre une inférence plus rapide avec des performances proches (Dice ≈ 0,89). Recommandation selon le contexte : U-Net pour la recherche, SegNet pour le triage rapide.\n\n## Impact\n\nCe travail a été cité plus de 346 fois et a contribué à l'adoption clinique d'outils automatisés d'évaluation de la sévérité de la COVID-19.\n\n## Publications\n\nPublié dans *BMC Medical Imaging*, 2021. Voir la page Publications pour la référence complète.\n\n## Ressources\n\n- GitHub : [COVID19-DL](https://github.com/adnan-saood/COVID19-DL)\n- Données : scanners disponibles via Zenodo / IEEE DataPort (voir le README du dépôt)"
---

## Project Overview



This research project was conducted during the height of the COVID-19 pandemic, addressing the urgent need for automated diagnostic tools to assist healthcare professionals in quickly and accurately identifying COVID-19 infections in lung CT scans. The work compares two leading deep learning architectures — **U-Net** and **SegNet** — for the challenging task of semantic segmentation of infected tissue regions.

## Motivation

During 2021–2022, COVID-19 diagnosis relied heavily on CT imaging and radiologist interpretation. This created several bottlenecks:
- **Radiologist shortage**: Limited expertise to analyze high volume of scans
- **Subjective interpretation**: Variability in identifying infected regions
- **Time pressure**: Rapid diagnosis needed for patient triage
- **Consistency**: Automated segmentation ensures standardized feature extraction

Automated segmentation enables radiologists to work faster, more consistently, and with less fatigue.

## Technical Approach

### Deep Learning Architectures

#### U-Net
- **Encoder-decoder** with skip connections
- Excels at precise localization with limited training data
- Originally designed for biomedical image segmentation
- Trade-off: Higher memory footprint

#### SegNet
- **Memory-efficient encoder-decoder** design
- Uses pooling indices for upsampling (reduces computation)
- Faster inference, lower memory — suitable for clinical deployment
- Potential trade-off: Slightly lower fine-grained accuracy

### Dataset and Training

- Multi-center international CT scan database (500+ scans, diverse patient demographics, severity levels)
- Expert radiologist annotations (gold standard)
- Extensive data augmentation (rotation, elastic deformation, intensity variation)
- Cross-validation and rigorous evaluation metrics

### Evaluation Metrics

- **Dice score** (overlap between predicted and ground-truth regions)
- **Intersection-over-Union (IoU)**
- **Sensitivity and Specificity** (clinical relevance)
- **Computational efficiency** (inference time, memory)

## Key Results

U-Net achieved higher segmentation accuracy (~0.92 Dice), while SegNet offered faster inference with competitive performance (~0.89 Dice). Context-dependent deployment recommendation: U-Net for research; SegNet for rapid triage.

## Impact

This work has been cited 346+ times and influenced clinical adoption of automated COVID-19 severity assessment tools.

## Publications

Published in *BMC Medical Imaging*, 2021. See Publications page for full citation.

## Related Resources

- GitHub: [COVID19-DL](https://github.com/adnan-saood/COVID19-DL)
- Datasets: CT scans available via Zenodo / IEEE DataPort (see repository README)
