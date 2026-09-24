---
layout: page
title: Contributing Factors in Human-Robot Handshake
description: Empirical study of what makes a natural, comfortable handshake between humans and robots — compliance, hand grip, and temporal synchrony.
img: assets/img/handshake.jpg
importance: 3
category: Research
related_publications: true
github: https://github.com/adnan-saood/franka_handshake_ros2
---

## Project Overview

{% include figure.liquid loading="eager" path="assets/img/handshake.jpg" title="Human-robot handshake study" class="img-fluid rounded z-depth-1" %}

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
