CREATE DATABASE IF NOT EXISTS `personaldiary`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `personaldiary`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `login_date` DATE NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `diary_entries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `diary_date` DATE NOT NULL,
  `diary_username` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_diary_entries_username` (`diary_username`),
  CONSTRAINT `fk_diary_entries_user`
    FOREIGN KEY (`diary_username`) REFERENCES `users` (`username`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reminder` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reminder_date` DATE NOT NULL,
  `message` TEXT NOT NULL,
  `reminder_username` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reminder_username` (`reminder_username`),
  CONSTRAINT `fk_reminder_user`
    FOREIGN KEY (`reminder_username`) REFERENCES `users` (`username`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
