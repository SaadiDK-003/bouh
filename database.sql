-- Create database
CREATE DATABASE IF NOT EXISTS medical_booking;
USE medical_booking;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    user_type ENUM('user', 'doctor') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Doctors table (extends users table)
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    years_of_experience INT NOT NULL,
    about TEXT,
    languages VARCHAR(255), -- JSON array of languages
    treatment_price DECIMAL(10, 2) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    user_id INT NULL, -- Links to users.id when the patient is a registered user
    patient_name VARCHAR(100) NOT NULL,
    patient_email VARCHAR(100) NOT NULL,
    patient_phone VARCHAR(20) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    cause_of_treatment TEXT,
    notes TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Chat messages linked to an appointment
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    sender_id INT NOT NULL, -- users.id of the sender (doctor or user)
    sender_type ENUM('user', 'doctor') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (appointment_id),
    INDEX (created_at)
);

-- Insert sample data for testing
INSERT INTO users (name, email, phone, password, user_type) VALUES
('Dr. John Smith', 'john.smith@email.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor'),
('Dr. Sarah Johnson', 'sarah.johnson@email.com', '0987654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor'),
('Test User', 'user@email.com', '5555555555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert sample doctors
INSERT INTO doctors (user_id, years_of_experience, about, languages, treatment_price, specialty) VALUES
(1, 15, 'Experienced cardiologist with over 15 years of practice. Specialized in heart diseases and cardiovascular treatments.', '["english", "arabic"]', 150.00, 'Cardiologist'),
(2, 10, 'Pediatrician dedicated to providing comprehensive healthcare for children from birth through adolescence.', '["english", "french"]', 120.00, 'Pediatrician');

-- Insert sample appointments
INSERT INTO appointments (doctor_id, user_id, patient_name, patient_email, patient_phone, appointment_date, appointment_time, cause_of_treatment, notes, status) VALUES
(1, NULL, 'Alice Brown', 'alice@email.com', '1111111111', '2025-02-10', '10:00:00', 'Regular checkup', 'Patient has high blood pressure history', 'confirmed'),
(2, NULL, 'Bob Wilson', 'bob@email.com', '2222222222', '2025-02-10', '11:00:00', 'Child fever', '3 days persistent fever', 'pending');

-- Assessments results table
CREATE TABLE IF NOT EXISTS assessments_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    assessment_type ENUM('ghq15','phq9','gad7') NOT NULL,
    total_score INT NOT NULL,
    severity VARCHAR(50) NOT NULL,
    answers JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (assessment_type),
    INDEX (created_at)
);

-- Reviews per appointment (rating and comment)
CREATE TABLE IF NOT EXISTS doctor_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    doctor_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 0 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_appointment (appointment_id),
    INDEX idx_doctor (doctor_id),
    INDEX idx_user (user_id),
    CONSTRAINT fk_reviews_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
