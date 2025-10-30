---
layout: page
title: Tactile Array for Humanoid Hand to Enable Socio-Affective Touch
description: Design, manufacturing, and validation of a tactile array embedded in a silicon-based substrate for humanoid hands.
img: assets/img/tactile_array.jpg
importance: 1
category: work
related_publications: true
---

## Project Overview

This project represents the core of my PhD thesis research, focusing on developing advanced tactile sensing capabilities for humanoid robots to enable more natural and empathetic human-robot interactions. The work involves creating a sophisticated tactile array that can perceive and interpret human touch with unprecedented sensitivity and accuracy.

## Motivation

Human touch is one of the most fundamental forms of communication, conveying emotions, intentions, and social cues that words alone cannot express. For robots to truly integrate into human society and provide meaningful assistance, they must be able to understand and respond to these subtle tactile communications. This project aims to bridge that gap by developing tactile sensing technology that goes beyond simple contact detection to understand the nuanced language of touch.

## Technical Approach

### Sensor Design and Manufacturing

The tactile array consists of multiple pressure-sensitive elements embedded within a flexible, biocompatible silicon substrate that closely mimics the mechanical properties of human skin. Each sensing element is carefully calibrated to detect variations in pressure, texture, and temperature, providing rich tactile information that can be processed to understand human intentions and emotional states.

Key design considerations include:
- **Spatial Resolution**: High-density sensor placement to capture fine-grained tactile information
- **Sensitivity Range**: Optimized to detect both gentle caresses and firm grasps
- **Durability**: Robust design to withstand repeated interactions
- **Integration**: Seamless mounting on humanoid hand structures

### Signal Processing and Interpretation

The raw tactile data is processed using advanced signal processing algorithms and machine learning techniques to extract meaningful information about:
- Contact force and pressure distribution
- Touch dynamics and temporal patterns
- Surface texture and material properties
- Human grasping behavior and hand posture

### Real-Time Estimation

A critical aspect of this work is the development of real-time estimation algorithms that can process tactile information with minimal latency, enabling immediate and appropriate robotic responses to human touch.

## Applications and Impact

This tactile sensing technology opens up numerous possibilities for human-robot interaction:

- **Healthcare Robotics**: Enabling robots to provide comforting touch in therapeutic settings
- **Assistive Technology**: Allowing robotic caregivers to understand and respond to the needs of elderly or disabled individuals
- **Social Robotics**: Creating robots that can engage in natural physical interactions with humans
- **Rehabilitation**: Developing robotic systems that can provide precise haptic feedback for physical therapy

## Current Status

The project is currently in the validation phase, where the manufactured tactile arrays are being tested under various conditions to ensure reliability and accuracy. Preliminary results show promising performance in detecting and classifying different types of human touch interactions.

## Future Directions

Future work will focus on:
- Expanding the sensory capabilities to include temperature and vibration sensing
- Developing more sophisticated machine learning models for touch interpretation
- Integrating the tactile system with other sensory modalities for multi-modal interaction
- Conducting extensive human-robot interaction studies to validate the system's effectiveness

This research represents a significant step forward in creating robots that can truly understand and respond to human touch, bringing us closer to a future where robots can provide genuine emotional support and companionship to humans.

<div class="row">
    <div class="col-sm mt-3 mt-md-0">
        {% include figure.liquid loading="eager" path="assets/img/tactile_array_design.jpg" title="Tactile array design" class="img-fluid rounded z-depth-1" %}
    </div>
    <div class="col-sm mt-3 mt-md-0">
        {% include figure.liquid loading="eager" path="assets/img/humanoid_hand.jpg" title="Humanoid hand with tactile array" class="img-fluid rounded z-depth-1" %}
    </div>
</div>
<div class="caption">
    Left: Detailed view of the tactile array sensor design. Right: Integration of the tactile array on a humanoid robotic hand.
</div>
