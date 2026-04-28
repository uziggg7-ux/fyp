-- Migration for MoodAI Admin Panel
-- Run this to update the database for Phase 1

-- 1. Update admins table
ALTER TABLE `admins`
ADD COLUMN `is_active` tinyint(1) DEFAULT 1,
ADD COLUMN `last_login` datetime DEFAULT NULL;

-- 2. Create admin_logs table
CREATE TABLE IF NOT EXISTS `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `target` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Insert Default Admin Account
-- Note: User requested plain text password 'admin123' for now
INSERT INTO `admins` (`username`, `email`, `password`, `is_active`)
VALUES ('admin', 'admin123@gmail.com', 'admin123', 1);
