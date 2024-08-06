CREATE DATABASE IF NOT EXISTS ExamPlanningSystem;
USE ExamPlanningSystem;

CREATE TABLE Faculty (
    FacultyID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL
);

CREATE TABLE Department (
    DepartmentID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    FacultyID INT,
    FOREIGN KEY (FacultyID) REFERENCES Faculty(FacultyID)
);

CREATE TABLE Employee (
    EmployeeID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    DepartmentID INT,
    Role VARCHAR(100),
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    score INT DEFAULT 0,
    FOREIGN KEY (DepartmentID) REFERENCES Department(DepartmentID)
);

CREATE TABLE Courses (
    CourseID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    DepartmentID INT,
    TimeSlot VARCHAR(50),
    Day VARCHAR(50),
    FOREIGN KEY (DepartmentID) REFERENCES Department(DepartmentID)
);

CREATE TABLE Exam (
    ExamID INT AUTO_INCREMENT PRIMARY KEY,
    CourseID INT,
    ExamDate DATE,
    ExamTime TIME,
    FOREIGN KEY (CourseID) REFERENCES Courses(CourseID)
);

CREATE TABLE IF NOT EXISTS Schedule (
    ScheduleID INT AUTO_INCREMENT PRIMARY KEY,
    AssistantID INT,
    TimeSlot VARCHAR(50),
    Monday VARCHAR(255),
    Tuesday VARCHAR(255),
    Wednesday VARCHAR(255),
    Thursday VARCHAR(255),
    Friday VARCHAR(255),
    FOREIGN KEY (AssistantID) REFERENCES Employee(EmployeeID)
);

CREATE TABLE IF NOT EXISTS AssistantExams (
    AssistantExamID INT AUTO_INCREMENT PRIMARY KEY,
    AssistantID INT,
    ExamID INT,
    FOREIGN KEY (AssistantID) REFERENCES Employee(EmployeeID),
    FOREIGN KEY (ExamID) REFERENCES Exam(ExamID)
);

CREATE TABLE IF NOT EXISTS AssistantCourses (
    AssistantCourseID INT AUTO_INCREMENT PRIMARY KEY,
    AssistantID INT,
    CourseID INT,
    TimeSlot VARCHAR(50),
    Day VARCHAR(50),
    FOREIGN KEY (AssistantID) REFERENCES Employee(EmployeeID),
    FOREIGN KEY (CourseID) REFERENCES Courses(CourseID)
);

INSERT INTO Faculty (Name) VALUES 
('Engineering'), 
('Business'), 
('Arts and Humanities'), 
('Sciences'), 
('Health Sciences');

INSERT INTO Department (Name, FacultyID) VALUES 
('Computer Science', 1), 
('Mechanical Engineering', 1), 
('Business Administration', 2), 
('Fine Arts', 3), 
('Biology', 4);

INSERT INTO Employee (Name, DepartmentID, Role, username, password) VALUES 
('Ayşe Turgut', 1, 'Assistant', 'ayşeturgut', 'ayşe'), 
('Beste Özdemir', 1, 'Head of Department', 'besteözdemir', 'beste'), 
('Berfin Ergün', 1, 'Head of Secretary', 'berfinergün', 'berfin'), 
('Deniz Köse', 1, 'Dean', 'denizköse', 'deniz'), 
('Ada Cebe', 1, 'Secretary', 'adacebe', 'ada'),
('Emre Özkan', 1, 'Assistant', 'emreözkan', 'emre'),
('Ali Karaca', 1, 'Assistant', 'alikaraca', 'ali'),
('Kıvanç Türker', 2, 'Assistant', 'kıvançtürker', 'kıvanç');

INSERT INTO Courses (Name, DepartmentID, TimeSlot, Day) VALUES 
('Introduction to Computing', 1, '12:00-14:00', 'Monday'), 
('Introduction to Computing', 1, '14:00-16:00', 'Wednesday'),
('Algorithm Analysis', 1, '14:00-16:00', 'Tuesday'), 
('Algorithm Analysis', 1, '16:00-18:00', 'Thursday'), 
('Software Engineering', 1, '16:00-18:00', 'Wednesday'), 
('Software Engineering', 1, '08:00-10:00', 'Friday'), 
('Computer Architectures', 1, '08:00-10:00', 'Monday'), 
('Computer Architectures', 1, '10:00-12:00', 'Wednesday');
