CREATE TABLE `timelines` (
    `id` int (255) NOT NULL AUTO_INCREMENT,
    `user_id` int (255) NOT NULL,
    `rows` int (255) NOT NULL,
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY `id` (`id`)
) ENGINE = InnoDB;CREATE TABLE `timeline_subjects` (
    `id` int (255) NOT NULL AUTO_INCREMENT,
    `timeline_id` int (255) NOT NULL,
    `cell` int (255) NOT NULL,
    `subject_id` int (255) NOT NULL,
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY `id` (`id`)
) ENGINE = InnoDB;CREATE TABLE `timeline_hours` (
    `id` int (255) NOT NULL AUTO_INCREMENT,
    `timeline_id` int (255) NOT NULL,
    `hour` varchar (255) COLLATE utf8_spanish_ci NOT NULL,
    `subject_id` int (255) NOT NULL,
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY `id` (`id`)
) ENGINE = InnoDB;CREATE TABLE `subjects` (
    `id` int (255) NOT NULL AUTO_INCREMENT,
    `user_id` int (255) NOT NULL,
    `name` varchar (255) COLLATE utf8_spanish_ci NOT NULL,
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY `id` (`id`)
) ENGINE = InnoDB;CREATE TABLE `subjects` (
    `id` int (255) NOT NULL AUTO_INCREMENT,
    `name` varchar (50) COLLATE utf8_spanish_ci NOT NULL,
    `surname` varchar (100) COLLATE utf8_spanish_ci DEFAULT NULL,
    `role` varchar (20) COLLATE utf8_spanish_ci DEFAULT NULL,
    `email` varchar (255) COLLATE utf8_spanish_ci NOT NULL,
    `password` varchar (255) COLLATE utf8_spanish_ci NOT NULL,
    `created_at` datetime DEFAULT NULL,
    `updated_at` datetime DEFAULT NULL,
    `remember_token` varchar (255) COLLATE utf8_spanish_ci DEFAULT NULL,
    PRIMARY KEY `id` (`id`)
) ENGINE = InnoDB;
