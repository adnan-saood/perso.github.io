---
layout: post
title: "hid_ros2: A 500Hz+ Hardware Bridge for ROS2 (ROSCon FR Talk Writeup)"
date: 2025-12-20
description: How we built a universal, high-performance USB-HID interface for custom hardware in ROS2—no custom C++ needed.
tags: ROS2 robotics open-source hardware-interface embedded
categories: engineering tools
related_posts: true
giscus_comments: true
title_fr: "hid_ros2 : une passerelle matérielle à plus de 500 Hz pour ROS 2 (retour sur mon exposé à la ROSConFr)"
description_fr: "Comment nous avons construit une interface USB-HID universelle et performante pour le matériel sur mesure dans ROS 2, sans écrire de C++."
body_fr: "## Le problème : intégrer du matériel sur mesure dans ROS 2\n\nL'un des plus gros points de friction en robotique, c'est **connecter du matériel sur mesure à ROS 2**. Pince, capteur d'effecteur, contrôleur à microcontrôleur ou périphérique d'entrée maison : le parcours actuel est pénible.\n\n1. Écrire un driver d'interface matérielle en C++ (qui demande une bonne maîtrise de ROS 2 et du contrôle)\n2. Le compiler avec les bibliothèques ROS 2 du système\n3. Déboguer l'intégration\n4. Recommencer pour chaque nouvel appareil\n\n**Il doit y avoir plus simple.**\n\n---\n\n## L'idée : l'USB-HID comme standard universel\n\nL'USB-HID (Human Interface Device) est partout : souris, claviers, manettes, joysticks maison. C'est un protocole standard intégré à tous les systèmes d'exploitation. Pourtant, les roboticiens y pensent rarement comme à un outil robotique.\n\nEt si l'on **inversait la démarche** ? Au lieu d'écrire du C++ pour chaque appareil, on pourrait :\n\n1. Décrire la structure de données de l'appareil dans un simple **fichier de configuration YAML**\n2. Brancher son microcontrôleur (Teensy, ESP32, STM32…) qui parle HID\n3. Obtenir immédiatement une interface matérielle ros2_control, sans compilation\n\nC'est **hid_ros2**.\n\n---\n\n## L'architecture\n\n`hid_ros2` est un plugin d'interface matérielle unique et réutilisable pour `ros2_control` qui assure :\n\n**La découverte et la gestion du matériel**\n- Détection automatique des périphériques HID par identifiant fabricant/produit\n- Gestion propre du branchement à chaud (déconnexion/reconnexion)\n- Connexions persistantes et résilientes\n\n**Une intégration pilotée par la configuration**\n- Le fichier YAML décrit :\n  - les identifiants fabricant/produit ;\n  - la structure des rapports (quels octets correspondent à quels capteurs/actionneurs) ;\n  - les types de données, facteurs d'échelle, décalages ;\n  - la fréquence de mise à jour (100 Hz, 500 Hz, 1000 Hz selon le système).\n- Aucun changement de code pour prendre en charge un nouvel appareil\n\n**Des E/S performantes**\n- Accès USB-HID au niveau du noyau via `libhidapi` (multiplateforme : Linux, macOS, Windows)\n- File de données sans verrou pour la sûreté temps réel\n- Fréquences d'interrogation garanties jusqu'à 1000 Hz sur du matériel compatible\n- Communication déterministe à faible latence (< 5 ms en général, souvent < 2 ms)\n\n**Une intégration native ROS 2**\n- Interface matérielle `ros2_control` standard, compatible avec tous les contrôleurs `ros2_control`\n- Communication sûre en temps réel (mémoire préallouée)\n- Intégration transparente avec MoveIt2, les contrôleurs de trajectoire et les publieurs d'état\n\n---\n\n## Exemple : un effecteur sur mesure\n\nImaginez une **pince sur mesure à 4 degrés de liberté** avec 4 moteurs asservis en position et 6 capteurs tactiles. Votre microcontrôleur (disons une Teensy 4.1) est déjà programmé pour :\n- lire 4 consignes moteur dans un rapport HID (8 octets, 2 par moteur) ;\n- mesurer 4 positions de codeur et les renvoyer dans un rapport HID (8 octets, 2 par codeur) ;\n- mesurer 6 capteurs tactiles analogiques et les envoyer dans le même rapport (12 octets, 2 par capteur).\n\n**Avec hid_ros2**, votre configuration ressemble à ceci :\n\n```yaml\ndevice:\n  vendor_id: 0x16C0     # Teensy's default VID\n  product_id: 0x0486    # Your custom PID\n  report_rate_hz: 500\n\njoints:\n  motor_0:\n    out_report: [0, 1]    # bytes 0-1 of outgoing HID report\n    in_report: [0, 1]     # bytes 0-1 of incoming (encoder feedback)\n    scale: 0.01           # encoder counts to radians\n  motor_1:\n    out_report: [2, 3]\n    in_report: [2, 3]\n    scale: 0.01\n  # ... (motor_2, motor_3)\n\nsensors:\n  tactile_0:\n    in_report: [8, 9]     # byte 8-9 of incoming report\n    scale: 0.001          # analog to normalized pressure\n  # ... (tactile_1 through tactile_5)\n```\n\nEnsuite, dans votre fichier de lancement, vous chargez le contrôleur de pince `ros2_control` standard, et c'est terminé. Aucun driver C++ à écrire.\n\n---\n\n## Pourquoi c'est important\n\n### Pour les roboticiens qui ne développent pas de drivers\nOn peut intégrer du matériel sur mesure sans être programmeur système C++. L'intégration matérielle devient accessible à tous.\n\n### Pour les fabricants de matériel\nDécrire une fois, fonctionner partout. Un fabricant de pinces peut livrer un seul fichier YAML avec son produit, et ses utilisateurs peuvent brancher et utiliser sur n'importe quelle plateforme ROS 2.\n\n### Pour l'accessibilité\nUne barrière d'entrée plus basse, c'est plus de monde qui construit des robots. Un matériel plus varié, c'est plus d'innovation.\n\n### Pour la performance\nL'USB-HID est natif dans le noyau de tous les systèmes modernes. On obtient une communication déterministe à faible latence sans privilèges particuliers.\n\n### Pour la reproductibilité\nLes équipes peuvent partager leurs configurations matérielles et reproduire des résultats. Les laboratoires collaborent plus facilement.\n\n---\n\n## L'exposé à la ROSConFr 2025\n\n<div class=\"row mt-3 mb-3\">\n    <div class=\"col-12\">\n        <figure><img src=\"assets/img/blog/post09.jpg\" class=\"img-fluid rounded z-depth-1\" alt=\"Présentation de hid_ros2 à la ROSConFr\" title=\"Présentation de hid_ros2 à la ROSConFr\" loading=\"lazy\"></figure>\n    </div>\n</div>\n<div class=\"caption\" style=\"text-align: center;\">\n    Présentation de hid_ros2 à la ROSCon France 2025 (en toute transparence : la photo est générée par IA faute de photographe sur place, mais j'y étais vraiment !)\n</div>\n\n## Les chiffres (présentés à la ROSConFr 2025)\n\n**Mesures de performance** sur un poste Linux classique :\n\n- **Latence de communication :** 1,2 ms en général, < 2 ms au 99e centile\n- **Utilisation CPU :** < 5 % sur un système 4 cœurs\n- **Fréquence d'interrogation :** jusqu'à 1000 Hz (selon l'appareil et le système ; 500 Hz couramment)\n- **Temps de configuration :** moins d'une minute pour un nouvel appareil (écrire le YAML, tester, c'est fait)\n- **Code à écrire par nouvel appareil :** zéro ligne de C++\n\n**Exemple concret :** nous avons intégré 3 plateformes matérielles sur mesure (une pince à microcontrôleur, une matrice de capteurs, un périphérique d'entrée maison) en une seule session de la ROSCon France, littéralement pendant l'exposé.\n\n---\n\n## Open source et communauté\n\n`hid_ros2` est publié sous licence Apache 2.0, entièrement open source :\n\n**GitHub :** [adnan-saood/hid_ros2](https://github.com/adnan-saood/hid_ros2)\n**Documentation :** guides d'installation, exemples de configuration, dépannage\n**Communauté :** déjà utilisé dans plusieurs laboratoires et start-up ; les contributions sont les bienvenues\n\n---\n\n## Et ensuite ?\n\n**Fonctionnalités prévues :**\n- un outil graphique pour générer les configurations YAML (détection de l'appareil, correspondance automatique) ;\n- la prise en charge des périphériques HID composites (plusieurs points de terminaison) ;\n- un signal de vie pour la sûreté temps réel (chiens de garde) ;\n- journalisation et télémétrie intégrées pour le débogage.\n\n**Vision à long terme :**\nUn écosystème standardisé et indépendant des fabricants, où matériel et logiciel s'intègrent aussi facilement que dans l'électronique grand public.\n\n---\n\n## Merci à la ROSConFr 2025\n\nCe travail a mûri grâce aux retours de la formidable communauté robotique de la ROSCon France. Merci en particulier :\n- aux équipes d'ICube Strasbourg et de l'U2IS pour les tests en conditions réelles ;\n- aux mainteneurs de ROS 2 / ros2_control pour la qualité du framework ;\n- aux communautés Teensy, STM32 et ESP32 qui prennent en charge le HID nativement.\n\nSi vous avez un projet matériel et voulez essayer `hid_ros2`, commencez ici : [README sur GitHub](https://github.com/adnan-saood/hid_ros2), ou installez le binaire ROS 2 depuis apt.ros.org.\n\n---\n\n**À voir aussi :**\n- GitHub : [hid_ros2](https://github.com/adnan-saood/hid_ros2)\n- Le projet [paxini_ros2](projects/) (qui s'appuie sur hid_ros2)"
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
        <figure><img src="assets/img/blog/post09.jpg" class="img-fluid rounded z-depth-1" alt="hid_ros2 presentation at ROSCon FR" title="hid_ros2 presentation at ROSCon FR" loading="lazy"></figure>
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
- See the [paxini_ros2](projects/) project (uses hid_ros2 under the hood)
- Blog: "Introducing hid_ros2" (launch announcement, 1 month prior)
