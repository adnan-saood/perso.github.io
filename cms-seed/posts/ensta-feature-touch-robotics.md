---
layout: post
title: "A Touch of Feeling in Robotics (Adapted from ENSTA Feature)"
date: 2026-01-10
description: Why touch is the frontier of robotics—bridging the gap between machines and humanity.
tags: robotics touch haptics HRI research
categories: research outreach
related_posts: true
giscus_comments: true
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
