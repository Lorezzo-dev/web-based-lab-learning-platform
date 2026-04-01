### 🚀 Web-Based Lab Learning Platform

A full-stack **Learning Management System (LMS) with integrated interactive laboratory simulations**, designed to deliver structured content, automated evaluation, and real-time student analytics.


Developed and deployed as part of a **published IEEE research study**, this platform was used in a real classroom environment to evaluate the effectiveness of gamified digital laboratory learning.

---

## 🧠 Research Background

**"Science Education in Public Upper Secondary Schools: Utilizing the Web-Based Lab as an Interactive Learning Tool"**

🔗 https://ieeexplore.ieee.org/abstract/document/11380788

The platform was used with real student participants to compare:

- Traditional laboratory instruction  
- Interactive web-based simulations  

Metrics evaluated:

- Student engagement  
- Performance outcomes  
- Learning effectiveness  

---

## 🧑‍💻 My Role & Contribution

- Developed ~95% of the system (frontend + backend)
- Designed and implemented **interactive lab modules**
- Built **automated grading and evaluation logic**
- Engineered **progress tracking and analytics system**
- Designed relational database for structured data collection
- Integrated dynamic frontend interactions with backend processing

---

## ⚡ Key Features

### 🎮 Interactive Laboratory Modules

- **Phylogenetic Tree** – drag-and-drop classification system  
- **Frog Dissection** – interactive identification interface  
- **Punnett Square** – genetics simulation  
- **Timeline Quiz** – multi-step assessment flow  

---

### 🧩 Content Management System (CMS)

A built-in CMS enabling instructors to dynamically create and manage learning content:

- Create structured lectures (topics, sections, paragraphs)
- Attach videos and descriptions
- Build quizzes with multiple questions and answer validation
- Dynamically update modules without code changes

---

### 🧪 Interactive Quiz Engine

- Timed quizzes with multiple questions  
- Per-question scoring system  
- Automated grading and submission handling  
- Integrated directly with lecture content  

---

### 📊 Analytics & Performance Dashboard

- Tracks student progress across modules  
- Displays completion status:
  - Complete  
  - In Progress  
  - Not Started  
- Aggregates quiz and lab scores  
- Provides instructors with performance insights  

---

### 📈 Automated Evaluation System

- Module-based grading (labs + quizzes)  
- Real-time score computation  
- Persistent storage of student results  
- Final grading with remarks (e.g., Pass/Fail)  

---

### 👥 Role-Based Access Control

- **Student** – access modules, complete labs, take quizzes  
- **Teacher / Advisor** – monitor progress, manage content, view grades  
- **Admin** – manage users, modules, and system-wide configuration  

---

### 💬 Messaging System

- Internal messaging between students and instructors  
- Inbox-based communication system  
- Role-aware message visibility  

---

## 🏗️ System Architecture

- **Backend:** PHP (application logic, evaluation engine)  
- **Database:** MySQL (users, grades, progress tracking)  
- **Frontend:** HTML, CSS, JavaScript  
- **Content Storage:** XML (dynamic structured content)  

---

## ⚙️ Local Setup

1. Install XAMPP (or similar PHP/MySQL environment)  
2. Place project folder 
   htdocs/your-project-folder
4. Start Apache and MySQL  
5. Import database via phpMyAdmin:

- Create database (e.g., `thesislab`)
- Import `thesislab.sql`

5. Run in browser:
   http://localhost/your-project-folder/login.php
---

## 📸 Screenshots

### 🧩 Content Management (Instructor Side)
1. Admin CMS – Create Lecture Content  
<img width="1920" height="1080" alt="Admin Create Lecture" src="https://github.com/user-attachments/assets/f8e643f7-256f-4a72-90b3-5df0f2733f82" />
2. Admin CMS – Create Quiz
<img width="1920" height="1080" alt="Add Quiz" src="https://github.com/user-attachments/assets/3023e6f7-e0d3-4c14-a385-2e1703e0ee5e" />


### 📚 Learning Experience (Student Side)
3. Rendered Lecture Page
<img width="1920" height="1080" alt="RenderedLecturePage" src="https://github.com/user-attachments/assets/e5d12729-1811-4246-a320-ba834cff6f3f" />

4. Interactive Quiz System  
<img width="1920" height="1080" alt="Interactive Quiz" src="https://github.com/user-attachments/assets/83a2af1a-a8a5-4449-b148-1e5770a1ce2c" />

### 🧪 Interactive Lab Modules
5. Lab Simulation
<img width="1920" height="1080" alt="FrogLab" src="https://github.com/user-attachments/assets/9a3ad12b-f6cf-4ed6-ad46-ec0bc391d581" />
<br/>
<img width="1433" height="495" alt="punnett_collage_final" src="https://github.com/user-attachments/assets/00e66886-2a57-4cc9-b7fa-dbd8140cbc46" />
> Demonstrates dynamic genotype combinations and real-time phenotype probability updates across different genetic scenarios.
<br/>
<img width="1920" height="1080" alt="PhylogeneticTree" src="https://github.com/user-attachments/assets/4b32deb2-3a1d-479c-a937-7c9e81e85487" />
> These modules simulate real laboratory activities through interactive user input and automated evaluation logic.

### 📊 Analytics & Monitoring
6. Student Dashboard
<img width="1920" height="1080" alt="StudentDashboard" src="https://github.com/user-attachments/assets/9c4a6468-aa64-48e6-a1e9-7db9c4376ed1" />
> Demonstrates real-time grading logic, including partial completion, failed modules, and automated final grade computation.
7. Teacher Dashboard
<img width="1920" height="1080" alt="TeacherDashboard" src="https://github.com/user-attachments/assets/533ac72e-ecec-467d-8b51-6d9e3762cfd1" />

---

## ⚠️ Implementation Notes

- Originally deployed in a controlled research environment  
- Optimized for classroom testing and experimentation  
- Minor UI differences may appear across environments (local vs hosted)  
- Core system functionality remains consistent  

---

## 🔐 Data & Privacy

- Repository includes **sanitized database only**  
- All student data and experimental records have been removed  
- Complies with privacy and research data handling standards  

