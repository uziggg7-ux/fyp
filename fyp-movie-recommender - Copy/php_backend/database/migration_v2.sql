-- migration_v2.sql
-- Updates for Premium Admin Panel Features

USE `mood_recommender_db`;

-- 1. Add is_banned to users table
ALTER TABLE `users` ADD COLUMN `is_banned` TINYINT(1) DEFAULT 0;

-- 2. Create Moods Table
CREATE TABLE IF NOT EXISTS `moods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `associated_genres` varchar(100) DEFAULT NULL, -- Comma-separated TMDB Genre IDs
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create Movie-Mood Mapping Table (Many-to-Many)
CREATE TABLE IF NOT EXISTS `movie_mood_mapping` (
  `movie_id` int(11) NOT NULL, -- TMDB ID
  `mood_id` int(11) NOT NULL,
  PRIMARY KEY (`movie_id`, `mood_id`),
  FOREIGN KEY (`mood_id`) REFERENCES `moods`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create Movie Feedback Table (Likes/Dislikes)
CREATE TABLE IF NOT EXISTS `movie_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `feedback_type` enum('like','dislike') NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Create System Settings Table
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Create User Activity Logs Table
CREATE TABLE IF NOT EXISTS `user_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Ensure Admins table exists (in case it doesn't)
CREATE TABLE IF NOT EXISTS `admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Add Release Year to cached_movies
ALTER TABLE `cached_movies` ADD COLUMN `release_year` int(4) DEFAULT NULL;

-- 9. Initial Data Inserts
INSERT IGNORE INTO `moods` (`name`, `description`, `associated_genres`) VALUES
('Happy', 'Movies that make you feel good and laugh.', '35'),
('Sad', 'Emotional and dramatic stories.', '18'),
('Angry', 'High-energy and intense action.', '28'),
('Excited', 'Fun and adventurous for the whole family.', '10751'),
('Anxious', 'Tense and thrilling suspense.', '53'),
('Relaxed', 'Calm and romantic atmosphere.', '10749'),
('Neutral', 'Documentaries or steady-paced films.', '10752');

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('app_name', 'MoodAI'),
('rec_count', '10'),
('rec_logic', 'top_rated'),
('tmdb_api_key', '6bef3d72fb99db38620ed01b065e0d9e');

-- Default Admin (password: admin123)
INSERT IGNORE INTO `admins` (`username`, `email`, `password`) VALUES
('admin', 'admin@moodai.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
