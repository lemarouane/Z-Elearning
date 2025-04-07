-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 07 avr. 2025 à 03:52
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `zouhair_elearning`
--

-- --------------------------------------------------------

--
-- Structure de la table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` enum('admin','student') NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `action_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `user_role`, `action`, `details`, `action_time`) VALUES
(1, 1, 'admin', 'Logged in', 'Admin logged in successfully', '2025-04-07 01:39:42'),
(2, 1, 'admin', 'Added course', 'Course ID: 1', '2025-04-07 01:39:42'),
(3, 1, 'admin', 'Added course', 'Course ID: 3', '2025-04-07 01:41:39'),
(4, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:46:56'),
(5, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:46:56'),
(6, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:46:56'),
(7, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:46:56'),
(8, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:46:56'),
(9, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:20'),
(10, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:20'),
(11, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:20'),
(12, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:20'),
(13, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:20'),
(14, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:24'),
(15, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:25'),
(16, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:25'),
(17, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:25'),
(18, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:25'),
(19, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:26'),
(20, 1, 'admin', 'Streamed PDF', 'File: pdfs/1743990099_oncf-voyages-ismail haddad.pdf', '2025-04-07 01:47:45');

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `last_login`, `created_at`) VALUES
(1, 'admin1', 'adminpass123', NULL, '2025-04-07 01:39:42');

-- --------------------------------------------------------

--
-- Structure de la table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `content_type` enum('pdf','video') NOT NULL,
  `content_path` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `difficulty` enum('Easy','Medium','Hard') NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `courses`
--

INSERT INTO `courses` (`id`, `title`, `subject_id`, `level_id`, `content_type`, `content_path`, `image_path`, `difficulty`, `created_by`, `created_at`) VALUES
(1, 'Algebra Basics', 1, 1, 'pdf', 'pdfs/algebra_basics.pdf', NULL, 'Easy', 1, '2025-04-07 01:39:42'),
(2, 'Quantum Mechanics', 2, 1, 'video', 'https://www.youtube.com/embed/xyz123', NULL, 'Hard', 1, '2025-04-07 01:39:42'),
(3, 'last', 3, 1, 'pdf', 'pdfs/1743990099_oncf-voyages-ismail haddad.pdf', 'images/1743990099_minist_re de l\'education -vector.ma.png', 'Easy', 1, '2025-04-07 01:41:39');

-- --------------------------------------------------------

--
-- Structure de la table `levels`
--

CREATE TABLE `levels` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `levels`
--

INSERT INTO `levels` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Bac+1', 'First year after high school', '2025-04-07 01:39:42'),
(2, 'Bac+2', 'Second year after high school', '2025-04-07 01:39:42');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` enum('admin','student') NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `user_role`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'student', 'Welcome to Zouhair E-Learning!', 0, '2025-04-07 01:39:42');

-- --------------------------------------------------------

--
-- Structure de la table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `is_validated` tinyint(1) DEFAULT 0,
  `device_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `students`
--

INSERT INTO `students` (`id`, `username`, `password`, `full_name`, `email`, `phone`, `is_validated`, `device_token`, `created_at`) VALUES
(1, 'student1', 'studentpass123', 'John Doe', 'john@example.com', '1234567890', 0, NULL, '2025-04-07 01:39:42');

-- --------------------------------------------------------

--
-- Structure de la table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `difficulty` enum('Easy','Medium','Hard') DEFAULT NULL,
  `auto_assign_new_courses` tinyint(1) DEFAULT 0,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `level_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `level_id`, `created_at`) VALUES
(1, 'Mathematics', 1, '2025-04-07 01:39:42'),
(2, 'Physics', 1, '2025-04-07 01:39:42'),
(3, 'Chemistry', 2, '2025-04-07 01:39:42');

-- --------------------------------------------------------

--
-- Structure de la table `user_courses`
--

CREATE TABLE `user_courses` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `progress` int(11) DEFAULT 0,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_logs_user` (`user_id`,`user_role`);

--
-- Index pour la table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Index pour la table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `level_id` (`level_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_courses_subject_level` (`subject_id`,`level_id`);

--
-- Index pour la table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`,`user_role`);

--
-- Index pour la table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_subject` (`student_id`,`subject_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_student_subjects_student` (`student_id`);

--
-- Index pour la table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `level_id` (`level_id`);

--
-- Index pour la table `user_courses`
--
ALTER TABLE `user_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_course` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `levels`
--
ALTER TABLE `levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `user_courses`
--
ALTER TABLE `user_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`);

--
-- Contraintes pour la table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD CONSTRAINT `student_subjects_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `user_courses`
--
ALTER TABLE `user_courses`
  ADD CONSTRAINT `user_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
