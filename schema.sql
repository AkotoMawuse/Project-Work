CREATE DATABASE IF NOT EXISTS relationship_system;
USE relationship_system;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    department VARCHAR(100),
    interests TEXT
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255)
);

-- Default admin account (password is 'vasco123')
INSERT INTO admin (username, password) VALUES ('admin', '$2y$10$twJOB068rbYQKPBWBccaE./IaddpxNGP53fsJbgEr2PE/Clu5tgnC');
