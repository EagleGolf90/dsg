-- Database setup for DSG Christmas Luncheon Registration System
-- Run this SQL to create the necessary tables

-- Create registrations table (primary registrant information)
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    cell_phone VARCHAR(20) NOT NULL,
    banquet_attendees INT NOT NULL,
    total_cost DECIMAL(10, 2) NOT NULL,
    submitted_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create registrations_others table (additional attendees)
CREATE TABLE IF NOT EXISTS registrations_others (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    INDEX idx_registration_id (registration_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Create a view to see complete registration information
CREATE OR REPLACE VIEW registrations_complete AS
SELECT 
    r.id,
    r.first_name AS primary_first_name,
    r.last_name AS primary_last_name,
    r.email,
    r.cell_phone,
    r.banquet_attendees,
    r.total_cost,
    r.submitted_at,
    r.created_at,
    GROUP_CONCAT(
        CONCAT(ro.first_name, ' ', ro.last_name) 
        ORDER BY ro.id 
        SEPARATOR ', '
    ) AS additional_attendees
FROM registrations r
LEFT JOIN registrations_others ro ON r.id = ro.registration_id
GROUP BY r.id, r.first_name, r.last_name, r.email, r.cell_phone, r.banquet_attendees, r.total_cost, r.submitted_at, r.created_at;
