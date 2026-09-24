---
layout: post
title: "hid_ros2: A 500Hz+ Hardware Bridge for ROS2 (ROSCon FR Talk Writeup)"
date: 2025-12-20
description: How we built a universal, high-performance USB-HID interface for custom hardware in ROS2—no custom C++ needed.
tags: ROS2 robotics open-source hardware-interface embedded
categories: engineering tools
related_posts: true
giscus_comments: true
---

## The Problem: Custom Hardware Integration in ROS2

One of the biggest friction points in robotics is **connecting custom hardware to ROS2**. Whether you're building a gripper, an end-effector sensor, a microcontroller-based controller, or a custom input device, the current workflow is painful:

1. Write C++ hardware interface driver (requires deep ROS2/control systems knowledge)
2. Compile against system ROS2 libraries
3. Debug integration issues
4. Repeat for every new device

**There has to be a better way.**

---

## The Insight: USB-HID as a Universal Standard

USB-HID (Human Interface Device) is everywhere—mice, keyboards, game controllers, custom joysticks. It's a standardized protocol built into every OS. But roboticists rarely think of it as a robotics tool.

What if we **inverted the workflow**? Instead of writing C++ for each device, what if you could:

1. Define your device's data structure in a simple **YAML configuration file**
2. Plug in your microcontroller (Teensy, ESP32, STM32, etc.) speaking HID
3. Instantly get a ros2_control hardware interface—no compilation needed

That's **hid_ros2**.

---

## The Architecture

`hid_ros2` is a single, reusable hardware interface plugin for `ros2_control` that:

**Hardware Discovery & Management**
- Auto-detects connected HID devices by vendor/product ID
- Gracefully handles hot-plugging (disconnect/reconnect)
- Maintains persistent, resilient connections

**Configuration-Driven Integration**
- YAML file defines the device's:
  - Vendor/product IDs
  - Report structure (which bytes encode which sensors/actuators)
  - Data types, scaling factors, offsets
  - Update rate (100 Hz, 500 Hz, 1000 Hz—OS permitting)
- No code changes needed to support a new device

**High-Performance I/O**
- Kernel-level USB-HID access via `libhidapi` (cross-platform: Linux, macOS, Windows)
- Lock-free data queuing for real-time safety
- Guaranteed polling rates up to 1000 Hz on compliant hardware
- Deterministic, low-latency communication (<5ms typical, <2ms often)

**ROS2-Native Integration**
- Standard `ros2_control` hardware interface—works with all `ros2_control` controllers
- Real-time-safe communication (memory pre-allocated)
- Seamless integration with MoveIt2, trajectory controllers, state publishers

---

## Example: A Custom End-Effector

Imagine you build a **4-DOF custom gripper** with 4 position-control motors and 6 tactile sensors. Your microcontroller (say, a Teensy 4.1) is already programmed to:
- Read 4 motor setpoints from a HID report (8 bytes, 2 per motor)
- Measure 4 encoder positions and send back a HID report (8 bytes, 2 per encoder)
- Measure 6 analog tactile sensors and send them in the same report (12 bytes, 2 per sensor)

**With hid_ros2**, your config looks like:

```yaml
device:
  vendor_id: 0x16C0     # Teensy's default VID
  product_id: 0x0486    # Your custom PID
  report_rate_hz: 500

joints:
  motor_0:
    out_report: [0, 1]    # bytes 0-1 of outgoing HID report
    in_report: [0, 1]     # bytes 0-1 of incoming (encoder feedback)
    scale: 0.01           # encoder counts to radians
  motor_1:
    out_report: [2, 3]
    in_report: [2, 3]
    scale: 0.01
  # ... (motor_2, motor_3)

sensors:
  tactile_0:
    in_report: [8, 9]     # byte 8-9 of incoming report
    scale: 0.001          # analog to normalized pressure
  # ... (tactile_1 through tactile_5)
```

Then in your `ros2_launch`, you load the standard `ros2_control` gripper controller, and you're done. No custom C++ driver written.

---

## Why This Matters

### For Roboticists Without Driver Development Skills
You can integrate custom hardware without being a C++ systems programmer. This democratizes hardware integration.

### For Hardware Vendors
Define once, support everywhere. A custom gripper manufacturer can ship a single YAML config file with their product, and users on any ROS2 platform can plug and play.

### For Accessibility
Lower barrier to entry = more people building robots. More diverse hardware = more innovation.

### For Performance
USB-HID is kernel-native on all modern OSes. You get deterministic, low-latency communication without special privileges.

### For Reproducibility
Teams can share hardware configs and reproduce research results. Academic robotics labs can collaborate more easily.

---

## The Talk at ROSCon FR 2025

<div class="row mt-3 mb-3">
    <div class="col-12">
        <figure><img src="/~saood/assets/img/blog/post09.jpg" class="img-fluid rounded z-depth-1" alt="hid_ros2 presentation at ROSCon FR" title="hid_ros2 presentation at ROSCon FR" loading="lazy"></figure>
    </div>
</div>
<div class="caption" style="text-align: center;">
    Presenting hid_ros2 at ROSCon France 2025 (self-aware note: the photo is AI-generated due to missing conference camera, but I promise I was really there!)
</div>

## The Numbers (from ROSCon FR 2025 Talk)

**Performance benchmarks** on a typical Linux desktop:

- **Communication latency:** 1.2ms typical, <2ms 99th percentile
- **CPU usage:** <5% on a 4-core system
- **Supported polling rate:** Up to 1000 Hz (device/OS permitting; commonly 500 Hz)
- **Configuration time:** <1 minute for a new device (write YAML, test, done)
- **Code overhead per new device:** Zero lines of C++

**Real-world example:** We integrated 3 custom hardware platforms (microcontroller gripper, sensor array, custom input device) in a single ROSCon France workshop session—literally during the talk.

---

## Open Source & Community

`hid_ros2` is released under the Apache 2.0 license and fully open-source:

**GitHub:** [adnan-saood/hid_ros2](https://github.com/adnan-saood/hid_ros2)  
**Documentation:** Full setup guides, example configs, troubleshooting  
**Community:** Already in use at several robotics labs and startups; contributions welcome

---

## What's Next?

**Planned features:**
- GUI tool to generate YAML configs (detect device, auto-map ports)
- Support for composite HID devices (multiple endpoints)
- Real-time safety heartbeat (watchdog timers)
- Built-in logging and telemetry for debugging

**Long-term vision:**  
A standardized, vendor-agnostic ecosystem where hardware and software integrate as seamlessly as they do in the consumer electronics world.

---

## Thank You ROSCon FR 2025

This work crystallized thanks to feedback from the amazing robotics community at ROSCon France. Thanks especially to:
- The ICube Strasbourg and U2IS teams for real-world testing
- The ROS2 / ros2_control maintainers for excellent framework design
- The Teensy, STM32, and ESP32 communities for supporting HID out of the box

If you have a custom hardware project and want to try `hid_ros2`, start here: [GitHub README](https://github.com/adnan-saood/hid_ros2) or grab the prebuilt ROS2 binary from apt.ros.org.

---

**Related:**
- GitHub: [hid_ros2](https://github.com/adnan-saood/hid_ros2)
- See the [paxini_ros2](/~saood/projects/#) project (uses hid_ros2 under the hood)
- Blog: "Introducing hid_ros2" (launch announcement, 1 month prior)
