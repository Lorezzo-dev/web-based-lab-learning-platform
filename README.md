# Web-Based Lab Learning Platform

A full-stack educational platform designed to simulate laboratory activities through interactive modules, automated evaluation, and real-time progress tracking.

This system was developed and deployed as part of a published IEEE research study evaluating gamified web-based laboratory learning in a real classroom environment.

---

## 🧠 Research Context

This platform was used in a study published under :contentReference[oaicite:0]{index=0}:

"Science Education in Public Upper Secondary Schools: Utilizing the Web-Based Lab as an Interactive Learning Tool"

🔗 https://ieeexplore.ieee.org/abstract/document/11380788

The system was utilized with real student participants to compare digital laboratory simulations with traditional laboratory instruction, measuring engagement, performance, and learning outcomes.

---

## 🧠 My Contribution

- Led development of ~95% of the system
- Designed and implemented interactive lab modules (drag-and-drop, simulations, quizzes)
- Built backend logic for grading, scoring, and progress tracking
- Designed relational database for experimental data collection
- Integrated frontend interactions with backend evaluation systems

---

## ⚡ Core Features

### 🎮 Interactive Lab Simulations
- Phylogenetic Tree (drag-and-drop classification)
- Frog Dissection (interactive identification)
- Punnett Square (genetics simulation)
- Timeline Quiz (multi-step quiz system)

### 📊 Automated Evaluation System
- Module-based grading (labs + quizzes)
- Real-time scoring and performance tracking
- Structured recording of student results

### 📈 Progress Tracking
- Tracks completion across modules and activities
- Stores individual performance data per user

### 🧑‍🏫 Instructor / Admin Tools
- Create and manage lectures
- Create and edit quizzes
- Monitor student performance and engagement

### 👤 Role-Based Access
- Student
- Teacher / Advisor
- Admin

---

## 🏗️ System Architecture

- Backend: PHP-based application logic
- Database: MySQL (users, grades, progress tracking)
- Frontend: HTML, CSS, JavaScript
- Data Handling: XML for dynamic content (e.g., announcements)

---

## ⚙️ Setup (Local Development)

1. Install XAMPP or similar PHP/MySQL environment  
2. Place project in `htdocs/`  
3. Start Apache and MySQL  
4. Import database via phpMyAdmin (`thesislab.sql`)  
5. Run:
   http://localhost/your-folder/login.php  

---

## ⚠️ Notes

- Originally deployed in a controlled environment for research experimentation
- Some UI elements may vary slightly across environments (local vs hosted)
- Core functionality remains unaffected

---

## 🔐 Data & Privacy

- Database included is fully sanitized  
- No real student or experimental data is included  
- All sensitive information has been removed for compliance  

---

## 📸 Screenshots

*(Add 4–6 screenshots here: modules, dashboard, admin panel, quiz, etc.)*
