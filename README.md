Web-Based Lab Learning Platform
Academic Context:

This project was developed as part of the thesis:

"Science Education in Public Upper Secondary Schools: Utilizing the Web-Based Lab as an Interactive Learning Tool"

It served as the primary system used in an experimental study comparing gamified web-based laboratory learning with traditional laboratory instruction in a real classroom setting.

📄 Published Research:
https://ieeexplore.ieee.org/abstract/document/11380788

The study involved actual student participants and evaluated the impact of interactive digital lab activities on student engagement, performance, and learning outcomes.

Project Contribution:
Developed approximately 95% of the system
Designed and implemented core interactive lab modules (drag-and-drop, simulations, quizzes)
Built backend logic for grading, scoring, and progress tracking
Designed and structured the relational database for experimental data collection
Integrated frontend interactions with backend evaluation for consistent user experience

Overview:

This project is a research-backed web-based educational platform designed to support and evaluate gamified laboratory learning.

It provides multiple interactive biology modules that simulate laboratory activities while tracking user performance and progress across different learning components.

Features:
🎮 Interactive Lab Simulations
Phylogenetic Tree (drag-and-drop classification)
Frog Dissection (interactive identification)
Punnett Square (genetics simulation)
Timeline Quiz (multi-step quiz system)
📊 Automated Grading System
Module-based scoring (lab and quiz components)
Performance recording per user
📈 Progress Tracking System
Tracks completion of modules, labs, and quizzes
🧑‍🏫 Instructor / Advisor Features
Create and manage lectures
Create and edit quizzes
Monitor student performance and progress
👤 User Roles
Student (0)
Teacher/Advisor (1)
Admin (2)
💾 Database & Data Handling
MySQL for structured data (users, grades, progress)
XML used for storing and managing dynamic content (e.g., announcements)

Tech Stack:
PHP
MySQL
XML
JavaScript
HTML/CSS

Setup Instructions:
Install XAMPP (or similar local server environment)
Place the project folder inside:
htdocs/
Start Apache and MySQL
Import the database:
Open phpMyAdmin
Create a database (e.g. thesislab)
Import thesislab.sql
Open in browser:
http://localhost/your-folder/login.php
Usage Notes
Users should register accounts through the system interface
User roles can be adjusted via:
Database (users.role)
Admin panel (if enabled)

Notes on Implementation:

This system was originally deployed in a hosted environment and optimized for a fixed display setup during thesis presentation and controlled experimentation.

As a result, some UI elements may appear slightly misaligned when run in different local environments (e.g., XAMPP vs hosting platforms).

These differences do not affect core functionality or system behavior.

Disclaimer:

This repository contains a sanitized database structure only.
All personal data, including student information and experimental records, has been removed to ensure privacy and compliance.
