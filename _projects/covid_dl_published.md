---
layout: page
title: COVID-19 Lung CT Image Segmentation using Deep Learning
description: Comparative study of U-Net versus SegNet architectures for semantic segmentation of COVID-19 infected tissue in CT scans.
img: assets/img/covid_segmentation.png
importance: 5
category: Research
related_publications: true
github: https://github.com/adnan-saood/COVID19-DL
---

## Project Overview

{% include figure.liquid loading="eager" path="assets/img/covid_segmentation.png" title="COVID-19 lung CT segmentation" class="img-fluid rounded z-depth-1" %}

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
