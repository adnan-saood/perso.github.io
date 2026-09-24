---
title: SWARM Motion Planning via Fluid Dynamics
description: Novel motion planning algorithm for non-holonomic robot swarms using Navier-Stokes equations with a 15-robot experimental platform.
img: assets/img/swarm_robots.webp
github: https://github.com/adnan-saood/swarm-robot_firmware
category: "Swarm robotics"
importance: 7
---

## Project Overview



This undergraduate/early-career project tackles one of robotics' most compelling challenges: **coordinated motion planning for large robot groups**. By drawing inspiration from fluid dynamics and the mathematical elegance of the Navier-Stokes equations, we developed a novel approach to swarm robotics that treats robot formations as fluid flow patterns.

## Motivation: Nature's Solutions

Observing natural swarms — flocks of birds, schools of fish, crowds of humans — reveals that elegant coordination emerges without central control. The key insight: these phenomena can be mathematically described using fluid dynamics principles.

## Technical Innovation

### Core Algorithm

The Navier-Stokes equations, fundamental to fluid mechanics, describe the motion of viscous fluids:

$$\frac{\partial \mathbf{v}}{\partial t} + (\mathbf{v} \cdot \nabla)\mathbf{v} = -\nabla p + \nu \nabla^2 \mathbf{v} + \mathbf{f}$$

We discretize and adapt this to robot swarms:
- **Velocity field** → robot velocities
- **Pressure field** → collision avoidance forces
- **Viscosity** → coordination/formation tightness
- **Body forces** → obstacles, goals, external inputs

### Key Innovations

- **Adaptive Viscosity Control**: Dynamic parameter tuning for tight formations vs. rapid dispersion
- **Non-holonomic Constraints**: Integration of differential-drive steering limits (real robots can't move sideways)
- **Multi-Scale Coordination**: Simultaneous operation at micro (collision avoidance), meso (local group), and macro (global path planning) scales

## Hardware Platform

Custom-designed 15-robot swarm platform:
- **Differential-drive kinematics** (2-wheel steering)
- **ARM Cortex-M4 microcontroller** per robot
- **2.4 GHz wireless mesh networking**
- **100 Hz control loop** for real-time coordination
- **Li-Po battery**: 4-hour runtime

## Experiments

Demonstrated:
- Formation-keeping through cluttered environments
- Scalable motion planning (tested up to 15 robots)
- Adaptive swarm density for obstacle negotiation
- Robustness to robot failures (graceful degradation)

## Impact & Applications

- **Search and rescue**: Autonomous exploration of unknown terrain
- **Environmental monitoring**: Distributed sensor networks
- **Entertainment/art**: Dynamic sculptural swarm displays
- **Industrial logistics**: Coordinated material transport in warehouses

## Related Resources

- GitHub: [swarm-robot_firmware](https://github.com/adnan-saood/swarm-robot_firmware) (embedded control code)
- Blog: "Swarm Robotics Inspired by Fluid Dynamics" (TODO: link to blog post once written)

## Current Status

Demonstrated proof-of-concept; further development paused in favor of higher-priority PhD research. Code and hardware designs documented for future students.
