CREATE DATABASE zouhair_elearning;

USE zouhair_elearning;

-- Creating tables in order to avoid foreign key issues
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subject_id INT,
    level_id INT,
    content_type ENUM('pdf', 'video') NOT NULL,
    content TEXT,
    course_image VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (level_id) REFERENCES levels(id)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Now create the students table, as the other tables exist
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    phone VARCHAR(20),
    registration_status ENUM('pending', 'validated', 'rejected') NOT NULL,
    assigned_course_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (assigned_course_id) REFERENCES courses(id)
);

CREATE TABLE screenshot_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    screenshot_count INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

CREATE TABLE course_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    course_id INT,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);
