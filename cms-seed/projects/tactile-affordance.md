---
title: Generative Factorized Model of Action-Conditioned Tactile Affordance
description: Learning how robots can predict and understand tactile interactions through generative models that condition on actions and prior touch.
img: assets/img/tactile_affordance.jpg
github: https://github.com/adnan-saood/fractal_ros2
category: "Tactile sensing"
importance: 2
featured: true
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
