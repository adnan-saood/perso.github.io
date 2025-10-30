---
layout: post
date: 2025-10-30 07:00:00-0400
inline: false
title: Debuting hid_ros2 at ROSConfr 2025
related_posts: false
---

I'll be debuting **hid_ros2**, a new ros2_control hardware interface, at ROSConfr 2025.

hid_ros2 is designed to be a universal, high-performance bridge between the ROS2 control framework and any device using the standard Human Interface Device (HID) protocol.

#### Why is this a game-changer?

- **Driverless Custom Hardware Integration**: Seamlessly connect custom sensors, microcontroller projects (Teensy, ESP32, STM32, etc.), and physical interfaces without writing custom C++ plugins. Describe your device's data packets in a straightforward configuration file.

- **Guaranteed Low Latency & High Performance**: Leveraging the OS-native USB-HID specification, hid_ros2 offers kernel-level robustness, true plug-and-play capability, and high polling rates.

- **Configuration Over Code**: The core philosophy eliminates complex coding, letting you focus on your robotics application.

- **Hot-Pluggable & Resilient**: Gracefully handles device disconnection and reconnection, making your custom hardware feel like a commercial product.

This is a significant step towards simplifying and accelerating custom hardware integration in ROS2, offering a more robust and performant alternative to traditional serial or network-based solutions for many I/O tasks.

Come find me at ROSConfr 2025!

[ROSConfr 2025 Link](https://roscon.fr/)
