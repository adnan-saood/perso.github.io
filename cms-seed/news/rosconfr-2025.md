---
layout: post
date: 2025-10-30 07:00:00-0400
inline: false
title: Debuting hid_ros2 at ROSConfr 2025
related_posts: false
title_fr: "Lancement de hid_ros2 à la ROSConFr 2025"
body_fr: "Je présenterai pour la première fois **hid_ros2**, une nouvelle interface matérielle ros2_control, à la ROSConFr 2025.\n\nhid_ros2 se veut un pont universel et performant entre le framework ROS 2 control et tout périphérique utilisant le protocole standard HID (Human Interface Device).\n\n#### Pourquoi ça change la donne ?\n\n- **Intégration de matériel sur mesure sans driver** : connectez capteurs, projets sur microcontrôleur (Teensy, ESP32, STM32…) et interfaces physiques sans écrire de plugin C++. Il suffit de décrire les paquets de données de votre appareil dans un fichier de configuration simple.\n- **Faible latence et hautes performances** : en s'appuyant sur la spécification USB-HID native du système, hid_ros2 offre la robustesse du noyau, un vrai plug-and-play et des fréquences d'échantillonnage élevées.\n- **La configuration plutôt que le code** : pas de code complexe, vous vous concentrez sur votre application robotique.\n- **Branchement à chaud et résilience** : les déconnexions et reconnexions sont gérées proprement, et votre matériel se comporte comme un produit commercial.\n\nC'est une étape importante pour simplifier et accélérer l'intégration de matériel sur mesure dans ROS 2, avec une alternative plus robuste et plus performante aux liaisons série ou réseau pour de nombreuses tâches d'E/S.\n\nVenez me voir à la ROSConFr 2025 !\n\n[Site de la ROSConFr 2025](https://roscon.fr/)"
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
