<?php
// File: db_migrate.php
// This script generates the SQL queries needed to adapt the database schema.
// Run this script from your browser and then execute the output in your database management tool (e.g., phpMyAdmin).

header('Content-Type: text/plain');

$sql = "-- SQL MIGRATION SCRIPT FOR MOROCCAN ELECTION SIMULATION\n\n";

// 1. Create political_parties table
$sql .= "-- 1. Create `political_parties` table\n";
$sql .= "CREATE TABLE `political_parties` (\n";
$sql .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
$sql .= "  `name` varchar(255) NOT NULL,\n";
$sql .= "  `logo_path` varchar(255) DEFAULT NULL,\n";
$sql .= "  PRIMARY KEY (`id`)\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

// 2. Create electoral_districts table
$sql .= "-- 2. Create `electoral_districts` table\n";
$sql .= "CREATE TABLE `electoral_districts` (\n";
$sql .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
$sql .= "  `name` varchar(255) NOT NULL,\n";
$sql .= "  `available_seats` int(11) NOT NULL DEFAULT 1,\n";
$sql .= "  PRIMARY KEY (`id`)\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

// 3. Create party_votes table
$sql .= "-- 3. Create `party_votes` table to track votes per district\n";
$sql .= "CREATE TABLE `party_votes` (\n";
$sql .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
$sql .= "  `party_id` int(11) NOT NULL,\n";
$sql .= "  `district_id` int(11) NOT NULL,\n";
$sql .= "  `vote_count` int(11) NOT NULL DEFAULT 0,\n";
$sql .= "  PRIMARY KEY (`id`),\n";
$sql .= "  UNIQUE KEY `party_district` (`party_id`, `district_id`),\n";
$sql .= "  FOREIGN KEY (`party_id`) REFERENCES `political_parties` (`id`) ON DELETE CASCADE,\n";
$sql .= "  FOREIGN KEY (`district_id`) REFERENCES `electoral_districts` (`id`) ON DELETE CASCADE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

// 3.5. Add description to political_parties table
$sql .= "-- 3.5. Add `description` to `political_parties` table\n";
$sql .= "ALTER TABLE `political_parties` ADD `description` TEXT DEFAULT NULL;\n\n";

// 4. Alter users table
$sql .= "-- 4. Add `district_id` to `users` table\n";
$sql .= "ALTER TABLE `users` ADD `district_id` INT(11) NULL AFTER `full_name`;\n";
$sql .= "ALTER TABLE `users` ADD FOREIGN KEY (`district_id`) REFERENCES `electoral_districts`(`id`) ON DELETE SET NULL;\n\n";

// 5. Alter candidates table
$sql .= "-- 5. Modify `candidates` table\n";
$sql .= "ALTER TABLE `candidates` ADD `party_id` INT(11) NULL AFTER `user_id`;\n";
$sql .= "ALTER TABLE `candidates` ADD `district_id` INT(11) NULL AFTER `party_id`;\n";
$sql .= "ALTER TABLE `candidates` ADD FOREIGN KEY (`party_id`) REFERENCES `political_parties`(`id`) ON DELETE SET NULL;\n";
$sql .= "ALTER TABLE `candidates` ADD FOREIGN KEY (`district_id`) REFERENCES `electoral_districts`(`id`) ON DELETE SET NULL;\n";
$sql .= "ALTER TABLE `candidates` DROP COLUMN `vote_count`;\n\n";

// Note on existing data
$sql .= "-- NOTE: Any existing user accounts will have a NULL `district_id`.\n";
$sql .= "-- You may need to manually update them or have users re-register.\n";
$sql .= "-- All existing candidates will have NULL party_id and district_id and their vote counts are now removed.\n";
$sql .= "-- It is recommended to apply this migration to a clean database.\n";

echo $sql;

?>
