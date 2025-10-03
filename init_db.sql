-- Cria a base de dados e a tabela `users` para o projeto
CREATE DATABASE IF NOT EXISTS `fotografia_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fotografia_db`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

