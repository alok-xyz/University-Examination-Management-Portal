CREATE DATABASE kuexam;
USE kuexam;

CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    registration_number VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    roll_number VARCHAR(50) NOT NULL,
    student_type ENUM('regular', 'backlog') NOT NULL,
    course VARCHAR(100) NOT NULL,
    current_semester INT NOT NULL,
    program ENUM('UG', 'PG') NOT NULL,
    mobile_number VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    fathers_name VARCHAR(100) NOT NULL,
    attendance TINYINT DEFAULT 0 NOT NULL,
    department_id INT,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    paper1_code VARCHAR(20),
    paper1_name VARCHAR(200),
    paper1_exam_date DATE,
    paper1_exam_time VARCHAR(50),
    paper2_code VARCHAR(20),
    paper2_name VARCHAR(200),
    paper2_exam_date DATE,
    paper2_exam_time VARCHAR(50),
    paper3_code VARCHAR(20),
    paper3_name VARCHAR(200),
    paper3_exam_date DATE,
    paper3_exam_time VARCHAR(50),
    paper4_code VARCHAR(20),
    paper4_name VARCHAR(200),
    paper4_exam_date DATE,
    paper4_exam_time VARCHAR(50),
    paper5_code VARCHAR(20),
    paper5_name VARCHAR(200),
    paper5_exam_date DATE,
    paper5_exam_time VARCHAR(50),
    paper6_code VARCHAR(20),
    paper6_name VARCHAR(200),
    paper6_exam_date DATE,
    paper6_exam_time VARCHAR(50),
    paper7_code VARCHAR(20),
    paper7_name VARCHAR(200),
    paper7_exam_date DATE,
    paper7_exam_time VARCHAR(50),
    paper8_code VARCHAR(20),
    paper8_name VARCHAR(200),
    paper8_exam_date DATE,
    paper8_exam_time VARCHAR(50)
);

CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    payment_id VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    is_backlog BOOLEAN DEFAULT FALSE
);


-- Create departments table
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    login_id VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS papers;

CREATE TABLE active_semesters (
    id INT(11) NOT NULL AUTO_INCREMENT,
    semester INT(11) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
