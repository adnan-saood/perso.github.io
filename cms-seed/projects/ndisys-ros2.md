---
title: NDI Systems ROS2 Driver
description: Professional-grade medical tracking system integration for ROS2, enabling precision motion capture in surgical and rehabilitation applications
img: assets/img/ndi_tracking.jpg
github: https://github.com/adnan-saood/ndisys_ros2
category: "Robotics software"
importance: 6
title_fr: "Driver ROS 2 pour les systèmes NDI"
description_fr: "Intégration de systèmes de suivi médicaux professionnels dans ROS 2, pour une capture de mouvement de précision en chirurgie et en rééducation."
category_fr: "Logiciel robotique"
body_fr: "## Présentation\n\nLe driver ROS 2 pour les systèmes NDI est une pile logicielle complète qui relie les systèmes de suivi optiques et électromagnétiques de Northern Digital Inc. (NDI) à Robot Operating System 2 (ROS 2). Cette intégration de niveau professionnel sert les applications de robotique médicale qui exigent un suivi submillimétrique : navigation chirurgicale, robotique de rééducation et analyse biomécanique.\n\n## Architecture technique\n\n### Couche d'interface matérielle\n- **Interface matérielle NDI** : implémentation C++ sur mesure gérant la communication directe avec les systèmes NDI Polaris et Aurora\n- **API combinée** : une interface unique pour le suivi optique (Polaris) et électromagnétique (Aurora)\n- **Gestion des outils** : chargement et initialisation dynamiques des outils suivis à partir de fichiers de définition ROM\n- **Acquisition en temps réel** : estimation continue de la pose à une fréquence configurable jusqu'à 60 Hz\n\n### Intégration ros2_control\n- **Interface capteur** : implémentation conforme de `hardware_interface::SensorInterface`\n- **Diffusion d'état** : publication en temps réel de poses 6 DDL (position + orientation en quaternion)\n- **Gestion du cycle de vie** : activation et désactivation complètes, adaptées au déroulé clinique\n- **Paramétrage** : paramètres de suivi et repères reconfigurables à l'exécution\n\n### Contrôleurs\n- **Rigid Pose Broadcaster** : contrôleur dédié à la publication des poses des corps rigides suivis\n- **Multi-outils** : suivi simultané de plusieurs instruments chirurgicaux ou marqueurs anatomiques\n- **Gestion des repères** : transformations et calibration automatiques\n- **Gestion des erreurs** : détection et reprise sur erreur complètes\n\n## Fonctionnalités clés\n\n### Suivi de précision\n- **Précision submillimétrique** : adaptée à la navigation chirurgicale\n- **Pose 6 DDL** : position (X, Y, Z) et orientation (quaternion) complètes\n- **Identification des outils** : reconnaissance automatique des instruments suivis\n- **Rejet des valeurs aberrantes** : filtrage robuste des artefacts de suivi\n\n### Fiabilité de niveau médical\n- **Cycle de vie sûr** : activation et désactivation structurées qui évitent les transitions d'état dangereuses\n- **Surveillance de la connexion** : contrôle continu de la liaison avec le système de suivi\n- **Validation des données** : vérification en temps réel de la qualité du suivi\n- **Tolérance aux pannes** : dégradation progressive et reprise après une perte de suivi temporaire\n\n### Intégration clinique\n- **URDF/Xacro** : description complète du robot pour les systèmes chirurgicaux\n- **Fichiers de lancement** : modèles préconfigurés pour un déploiement rapide\n- **Outils de calibration** : procédures intégrées pour l'environnement clinique\n- **Documentation** : guides d'utilisation clinique et protocoles de sécurité\n\n## Détails d'implémentation\n\n### Composants principaux\n\n**NdiSensorHardwareInterface**\n```cpp\nclass NdiSensorHardwareInterface : public hardware_interface::SensorInterface\n{\n    // Real-time tracking data acquisition\n    hardware_interface::return_type read(const rclcpp::Time & time,\n                                       const rclcpp::Duration & period) override;\n\n    // Tool initialization and management\n    void initializeAndEnableTools();\n    void loadTool(const char *toolDefinitionFilePath);\n};\n```\n\n**Contrôleur RigidPoseBroadcaster**\n```cpp\nclass RigidPoseBroadcaster : public controller_interface::ControllerInterface\n{\n    // Real-time pose publishing\n    controller_interface::return_type update(const rclcpp::Time & time,\n                                           const rclcpp::Duration & period) override;\n};\n```\n\n### Flux de données\n1. **Couche matérielle** : l'API NDI communique avec le matériel de suivi\n2. **Couche d'interface** : l'interface matérielle masque les spécificités du système de suivi\n3. **Couche contrôleur** : des contrôleurs dédiés traitent et publient les données\n4. **Couche applicative** : les applications cliniques consomment des messages de pose standardisés\n\n## Applications médicales\n\n### Navigation chirurgicale\n- **Suivi des instruments** : position des outils chirurgicaux en temps réel\n- **Recalage patient** : positionnement dynamique du patient et suivi de l'anatomie\n- **Surveillance de l'espace de travail** : limites du champ opératoire et évitement des collisions\n- **Traçabilité** : enregistrement complet des mouvements pour l'analyse postopératoire\n\n### Robotique de rééducation\n- **Analyse du mouvement** : évaluation quantitative des progrès du patient\n- **Assistance robotique** : contrôle précis des robots de rééducation\n- **Études biomécaniques** : capture de mouvement de niveau recherche pour les études cliniques\n- **Suivi des progrès** : indicateurs de récupération suivis dans le temps\n\n### Applications de recherche\n- **Test de dispositifs médicaux** : validation et caractérisation\n- **Interaction humain-robot** : étude des dynamiques d'interaction en milieu clinique\n- **Évaluation du geste chirurgical** : mesure objective de la maîtrise\n- **Analyse ergonomique** : optimisation de l'espace de travail chirurgical\n\n## Caractéristiques techniques\n\n### Matériel pris en charge\n- **NDI Polaris** : suivi optique avec marqueurs passifs\n- **NDI Aurora** : suivi électromagnétique pour la navigation interne\n- **Environnements mixtes** : suivi optique et électromagnétique simultané\n\n### Performances\n- **Fréquence** : jusqu'à 60 Hz en temps réel\n- **Latence** : < 50 ms de bout en bout\n- **Précision** : submillimétrique en position, < 0,5° en orientation\n- **Volume de suivi** : jusqu'à 1,4 m³ pour Polaris, illimité pour Aurora (dans la portée du générateur de champ)\n\n### Prérequis logiciels\n- **ROS 2 Humble** : plateforme principale de développement et de test\n- **Ubuntu 22.04 LTS** : système recommandé\n- **Noyau temps réel** : optionnel pour le temps réel strict\n- **API NDI** : bibliothèque propriétaire de communication avec le système de suivi\n\n## Impact clinique\n\nCe driver permet d'intégrer des systèmes de suivi médicaux professionnels dans des applications robotiques de recherche et cliniques, au service de la chirurgie mini-invasive, de la rééducation de précision et de la collaboration humain-robot en milieu médical. Il a été validé en recherche au laboratoire ICube (Université de Strasbourg) et a contribué à plusieurs publications en robotique médicale.\n\n## Développement et collaboration\n\nDéveloppé en collaboration avec le laboratoire ICube de l'Université de Strasbourg, ce projet offre à la communauté de la robotique médicale un accès open source à l'intégration de systèmes de suivi professionnels. Son architecture modulaire permet de l'étendre à d'autres systèmes NDI et de l'intégrer à des plateformes robotiques médicales sur mesure."
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
