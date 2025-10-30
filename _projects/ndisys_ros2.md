---
layout: page
title: NDI Systems ROS2 Driver
description: Professional-grade medical tracking system integration for ROS2, enabling precision motion capture in surgical and rehabilitation applications
img: assets/img/projects/ndi_tracking.jpg
importance: 1
category: Medical Robotics
related_publications: false
github: https://github.com/adnan-saood/ndisys_ros2
---

## Overview

The NDI Systems ROS2 Driver is a comprehensive software stack that bridges Northern Digital Inc. (NDI) optical and electromagnetic tracking systems with the Robot Operating System 2 (ROS2). This professional-grade integration enables medical robotics applications requiring sub-millimeter precision tracking for surgical navigation, rehabilitation robotics, and biomechanical analysis.

## Technical Architecture

### Hardware Interface Layer
- **NDI Hardware Interface**: Custom C++ implementation managing direct communication with NDI Polaris and Aurora tracking systems
- **Combined API Integration**: Unified interface supporting both optical (Polaris) and electromagnetic (Aurora) tracking modalities
- **Tool Management**: Dynamic loading and initialization of tracking tools from ROM definition files
- **Real-time Data Acquisition**: Continuous pose estimation at configurable update rates up to 60Hz

### ROS2 Control Integration
- **Sensor Interface**: Standards-compliant `hardware_interface::SensorInterface` implementation
- **State Broadcasting**: Real-time publication of 6DOF pose data (position + quaternion orientation)
- **Lifecycle Management**: Complete activation/deactivation lifecycle for clinical workflow integration
- **Parameter Configuration**: Runtime reconfigurable tracking parameters and coordinate frames

### Controller Framework
- **Rigid Pose Broadcaster**: Dedicated controller for publishing tracked rigid body poses
- **Multi-tool Support**: Simultaneous tracking of multiple surgical instruments or anatomical markers
- **Transform Management**: Automatic coordinate frame transformations and calibration
- **Error Handling**: Comprehensive error detection and recovery mechanisms

## Key Features

### Precision Tracking
- **Sub-millimeter Accuracy**: Achieves tracking precision suitable for surgical navigation applications
- **6DOF Pose Estimation**: Complete position (X, Y, Z) and orientation (quaternion) data
- **Tool Identification**: Automatic recognition and identification of tracked instruments
- **Outlier Rejection**: Robust filtering algorithms for eliminating tracking artifacts

### Medical-Grade Reliability
- **Lifecycle Safety**: Structured activation/deactivation preventing unsafe state transitions
- **Connection Monitoring**: Continuous monitoring of tracking system connectivity
- **Data Validation**: Real-time validation of tracking data quality and accuracy
- **Fault Tolerance**: Graceful degradation and recovery from temporary tracking loss

### Clinical Integration
- **URDF/Xacro Support**: Complete robot description framework for surgical systems
- **Launch File Templates**: Pre-configured launch files for rapid deployment
- **Calibration Tools**: Integrated calibration procedures for clinical environments
- **Documentation**: Comprehensive clinical usage guidelines and safety protocols

## Implementation Details

### Core Components

**NdiSensorHardwareInterface**
```cpp
class NdiSensorHardwareInterface : public hardware_interface::SensorInterface
{
    // Real-time tracking data acquisition
    hardware_interface::return_type read(const rclcpp::Time & time, 
                                       const rclcpp::Duration & period) override;
    
    // Tool initialization and management
    void initializeAndEnableTools();
    void loadTool(const char *toolDefinitionFilePath);
};
```

**RigidPoseBroadcaster Controller**
```cpp
class RigidPoseBroadcaster : public controller_interface::ControllerInterface
{
    // Real-time pose publishing
    controller_interface::return_type update(const rclcpp::Time & time, 
                                           const rclcpp::Duration & period) override;
};
```

### Data Flow Architecture
1. **Hardware Layer**: NDI API communicates with tracking hardware
2. **Interface Layer**: Hardware interface abstracts tracking system specifics
3. **Controller Layer**: Specialized controllers process and publish tracking data
4. **Application Layer**: Clinical applications consume standardized pose messages

## Medical Applications

### Surgical Navigation
- **Instrument Tracking**: Real-time position monitoring of surgical tools
- **Patient Registration**: Dynamic patient positioning and anatomy tracking
- **Workspace Monitoring**: Surgical field boundary enforcement and collision avoidance
- **Procedure Documentation**: Complete motion logging for post-operative analysis

### Rehabilitation Robotics
- **Patient Movement Analysis**: Quantitative assessment of rehabilitation progress
- **Robotic Assistance**: Precision control of rehabilitation robots
- **Biomechanical Studies**: Research-grade motion capture for clinical studies
- **Progress Monitoring**: Longitudinal tracking of patient recovery metrics

### Research Applications
- **Medical Device Testing**: Validation and characterization of medical devices
- **Human-Robot Interaction**: Study of interaction dynamics in clinical settings
- **Surgical Skill Assessment**: Objective evaluation of surgical proficiency
- **Ergonomic Analysis**: Workspace optimization for surgical environments

## Technical Specifications

### Supported Hardware
- **NDI Polaris Systems**: Optical tracking with passive marker support
- **NDI Aurora Systems**: Electromagnetic tracking for internal navigation
- **Mixed Environments**: Simultaneous optical and electromagnetic tracking

### Performance Characteristics
- **Update Rate**: Up to 60 Hz real-time tracking
- **Latency**: < 50ms end-to-end system latency
- **Accuracy**: Sub-millimeter position accuracy, < 0.5° orientation accuracy
- **Tracking Volume**: Up to 1.4m³ for Polaris, unlimited for Aurora (within field generator range)

### Software Requirements
- **ROS2 Humble**: Primary development and testing platform
- **Ubuntu 22.04 LTS**: Recommended operating system
- **Real-time Kernel**: Optional for hard real-time applications
- **NDI API**: Proprietary tracking system communication library

## Clinical Impact

This driver enables the integration of professional medical tracking systems into research and clinical robotic applications, supporting advances in minimally invasive surgery, precision rehabilitation, and human-robot collaboration in medical environments. The system has been validated in research settings at ICube Laboratory, University of Strasbourg, contributing to multiple peer-reviewed publications in medical robotics.

## Development and Collaboration

Developed in collaboration with ICube Laboratory, University of Strasbourg, this project represents a significant contribution to the medical robotics community by providing open-source access to professional-grade tracking system integration. The modular architecture supports extension to additional NDI systems and integration with custom medical robotic platforms.