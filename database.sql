CREATE DATABASE IF NOT EXISTS tasksync;
USE tasksync;

CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sr_no INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    status ENUM('not_started', 'pending', 'completed') DEFAULT 'not_started',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);