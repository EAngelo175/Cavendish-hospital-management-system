CREATE DATABASE IF NOT EXISTS hospital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hospital;

CREATE TABLE IF NOT EXISTS api_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX(user_id),
 INDEX(expires_at)
) ENGINE=InnoDB;

ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_photo VARCHAR(255) NULL AFTER email;
ALTER TABLE users ADD COLUMN IF NOT EXISTS pending_password VARCHAR(255) NULL AFTER profile_photo;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_token VARCHAR(100) NULL AFTER pending_password;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_reset_expires DATETIME NULL AFTER password_reset_token;
ALTER TABLE patients ADD COLUMN IF NOT EXISTS patient_code VARCHAR(30) NULL AFTER id;
ALTER TABLE patients ADD COLUMN IF NOT EXISTS age TINYINT UNSIGNED NULL AFTER gender;
ALTER TABLE patients DROP COLUMN IF EXISTS password;

CREATE TABLE IF NOT EXISTS payment_requests (
 id INT AUTO_INCREMENT PRIMARY KEY,
 invoice_id INT NOT NULL,
 patient_id INT NOT NULL,
 payment_method ENUM('Bank','Card','Mobile Money') NOT NULL,
 amount DECIMAL(12,2) NOT NULL,
 reference_number VARCHAR(100) NULL,
 card_last_four CHAR(4) NULL,
 status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX(invoice_id), INDEX(patient_id), INDEX(status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
 id INT AUTO_INCREMENT PRIMARY KEY,
 sender_id INT NOT NULL,
 recipient_id INT NOT NULL,
 subject VARCHAR(160) NOT NULL,
 body TEXT NOT NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX(sender_id), INDEX(recipient_id), INDEX(created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 title VARCHAR(160) NULL,
 message TEXT NOT NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX(user_id,created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NULL,
 username VARCHAR(100) NULL,
 action VARCHAR(255) NOT NULL,
 module VARCHAR(100) NOT NULL,
 ip_address VARCHAR(45) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX(created_at), INDEX(module)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS doctor_schedule (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 doctor_id BIGINT UNSIGNED NOT NULL,
 day_of_week TINYINT NOT NULL,
 start_time TIME NOT NULL,
 end_time TIME NOT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'Available',
 UNIQUE KEY doctor_day_time (doctor_id,day_of_week,start_time)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS medical_records (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 patient_id BIGINT UNSIGNED NOT NULL,
 doctor_id BIGINT UNSIGNED NOT NULL,
 diagnosis TEXT NOT NULL,
 treatment TEXT NULL,
 notes TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX(patient_id),
 INDEX(doctor_id),
 INDEX(created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS medicines (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 category VARCHAR(50),
 quantity INT NOT NULL DEFAULT 0,
 price DECIMAL(10,2) NOT NULL DEFAULT 0,
 expiry_date DATE,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX(name),
 INDEX(category)
) ENGINE=InnoDB;

-- Insert common medicines with realistic UGX prices
INSERT IGNORE INTO medicines (name, category, quantity, price, expiry_date) VALUES
('Paracetamol 500mg', 'Pain Relief', 150, 500, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Ibuprofen 400mg', 'Anti-inflammatory', 120, 800, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Aspirin 100mg', 'Pain Relief', 100, 600, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Amoxicillin 500mg', 'Antibiotic', 80, 2000, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Erythromycin 250mg', 'Antibiotic', 60, 1800, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Metformin 500mg', 'Diabetes', 100, 1200, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Lisinopril 10mg', 'Blood Pressure', 75, 2500, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Atenolol 50mg', 'Blood Pressure', 90, 2200, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Omeprazole 20mg', 'Antacid', 70, 1500, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Ranitidine 150mg', 'Antacid', 85, 1300, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Cough Syrup 100ml', 'Cough/Cold', 110, 3000, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Antihistamine Tablet', 'Allergy', 95, 1000, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Multivitamin Tablet', 'Supplement', 130, 2000, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Vitamin C 500mg', 'Supplement', 140, 800, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Iron Supplement', 'Anemia', 70, 1100, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Diclofenac 50mg', 'Anti-inflammatory', 65, 1600, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Metronidazole 400mg', 'Antibiotic', 55, 1400, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Ciprofloxacin 500mg', 'Antibiotic', 50, 3500, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Doxycycline 100mg', 'Antibiotic', 60, 2800, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Fluconazole 150mg', 'Antifungal', 40, 4500, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Antihistamine Syrup 100ml', 'Allergy', 80, 3500, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Glucose Solution 100ml', 'Supplement', 120, 1500, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Antiseptic Cream', 'Topical', 100, 2500, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Hydrocortisone Cream', 'Topical', 45, 3000, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Antibiotic Ointment', 'Topical', 70, 2800, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Acyclovir 400mg', 'Antiviral', 35, 5000, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Chloroquine 250mg', 'Antimalarial', 100, 1800, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Artemether 80mg', 'Antimalarial', 50, 8000, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Salbutamol Inhaler', 'Asthma', 30, 6500, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
('Prednisone 5mg', 'Steroid', 60, 2200, DATE_ADD(CURDATE(), INTERVAL 2 YEAR)),
('Amlodipine 5mg', 'Blood Pressure', 80, 2800, DATE_ADD(CURDATE(), INTERVAL 2 YEAR));
