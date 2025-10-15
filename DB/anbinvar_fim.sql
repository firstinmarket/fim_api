-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 10, 2025 at 07:46 PM
-- Server version: 10.11.13-MariaDB-cll-lve
-- PHP Version: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anbinvar_fim`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Finance', '2025-08-31 14:41:55'),
(2, 'Business Management', '2025-08-31 14:41:55'),
(3, 'Economics & Policy', '2025-08-31 14:41:55'),
(4, 'Technology & Innovation', '2025-08-31 14:41:55'),
(5, 'Industry & Sector-Specific Business', '2025-08-31 14:41:55'),
(6, 'Future of Business & Technology', '2025-08-31 14:41:55');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint(20) NOT NULL,
  `title` mediumtext NOT NULL,
  `image` mediumtext DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `likes_count` int(10) UNSIGNED DEFAULT 0,
  `shares_count` int(10) UNSIGNED DEFAULT 0,
  `saves_count` int(10) UNSIGNED DEFAULT 0,
  `views_count` int(10) UNSIGNED DEFAULT 0,
  `category_id` int(11) NOT NULL,
  `language` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `image`, `content`, `likes_count`, `shares_count`, `saves_count`, `views_count`, `category_id`, `language`, `created_at`, `updated_at`) VALUES
(1, 'Technology is the application of conceptual knowledge to achieve', 'img.png', 'வெற்றி ஒரே நாளில் கிடைப்பதில்லை; அது தொடர் முயற்சி,வெற்றி ஒரே நாளில் கிடைப்பதில்லை; அது தொடர் முயற்சி, கற்றல், சிந்தனை ஆகியவற்றின் மூலம் வளர்கிறது. தோல்வி ஒவ்வொன்றும் பாடமாகும், ஒவ்வொரு படியும் நம்பிக்கையை வளர்க்கும், சவால்கள் கவனத்தை கூர்மையாக்கும். பொறுமையும் உறுதியும் இருந்தால் சாதாரண யோசனைகள் மகத்தான சாதனைகளாக மாறும். கற்றல், சிந்தனை ஆகியவற்றின் மூலம் வளர்கிறது. தோல்வி ஒவ்வொன்றும் பாடமாகும், ஒவ்வொரு படியும் நம்பிக்கையை வளர்க்கும், சவால்கள் கவனத்தை கூர்மையாக்கும். பொறுமையும் உறுதியும் இருந்தால் சாதாரண யோசனைகள் மகத்தான சாதனைகளாக மாறும்.', 9066, 8, 9, 1442, 58, 'tamil', '2025-10-03 01:50:25', '2025-10-06 14:39:57'),
(2, 'just new one', 'img.png', 'Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...', 9067, 6, 9, 1204, 58, '', '2025-10-03 01:50:25', '2025-10-05 16:21:00'),
(3, 'new one', 'img.png', 'Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...', 9067, 6, 9, 1362, 58, '', '2025-10-05 01:50:25', '2025-10-05 16:20:12'),
(4, 'its new', 'img.png', 'Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...', 9067, 6, 9, 1733, 58, '', '2025-10-05 03:50:25', '2025-10-05 16:20:10'),
(5, 'new one one', 'img.png', 'Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...', 9067, 6, 9, 1441, 58, '', '2025-10-05 04:05:25', '2025-10-05 16:10:59'),
(6, 'new one two', 'img.png', 'Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...', 9067, 6, 9, 1495, 58, '', '2025-10-05 04:10:25', '2025-10-05 16:10:58'),
(7, 'new one oneone', 'img.png', 'Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...Technology is the application of conceptual knowle...', 9067, 6, 9, 2085, 58, '', '2025-10-05 04:11:25', '2025-10-05 17:04:40'),
(8, 'new one oneone', 'img.png', 'Success rarely comes overnight; it grows through consistent effort, learning, and reflection. Every failure teaches a lesson, every step builds confidence, and every challenge sharpens focus. With patience and determination, ordinary ideas transform into meaningful achievements that inspire others and create lasting impact in life.', 9067, 7, 10, 1947, 58, '', '2025-10-05 04:12:25', '2025-10-05 17:04:40'),
(9, 'Innovations Driving Tomorrow’s Technology', 'img.png', 'Technology evolves at lightning speed, blending artificial intelligence, blockchain, biotech, quantum computing, and robotics into daily life. These innovations are transforming industries, shaping economies, and redefining how humans work, connect, and imagine possibilities for the future.', 1, 1, 1, 0, 31, 'english', '2025-10-05 17:46:05', '2025-10-06 14:35:57');

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL,
  `post_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `saved_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `post_likes`
--

INSERT INTO `post_likes` (`id`, `post_id`, `user_id`, `saved_at`) VALUES
(25, 9, 4, '2025-10-06 20:03:00');

-- --------------------------------------------------------

--
-- Table structure for table `saved_counts`
--

CREATE TABLE `saved_counts` (
  `id` int(11) NOT NULL,
  `post_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `saved_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `saved_counts`
--

INSERT INTO `saved_counts` (`id`, `post_id`, `user_id`, `saved_at`) VALUES
(28, 8, 2, '2025-10-05 20:50:26'),
(29, 1, 2, '2025-10-06 18:40:48'),
(30, 9, 4, '2025-10-06 20:03:20');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `category_id`, `name`, `created_at`) VALUES
(1, 1, 'Personal Finance', '2025-08-31 14:42:36'),
(2, 1, 'Corporate Finance', '2025-08-31 14:42:36'),
(3, 1, 'Investment Banking', '2025-08-31 14:42:36'),
(4, 1, 'Asset Management', '2025-08-31 14:42:36'),
(5, 1, 'Wealth Management', '2025-08-31 14:42:36'),
(6, 1, 'Fintech & Digital Payments', '2025-08-31 14:42:36'),
(7, 1, 'Venture Capital & Private Equity', '2025-08-31 14:42:36'),
(8, 1, 'Stock Markets & Trading', '2025-08-31 14:42:36'),
(9, 1, 'Risk Management', '2025-08-31 14:42:36'),
(10, 1, 'Insurance & Actuarial Science', '2025-08-31 14:42:36'),
(11, 2, 'Leadership & Strategy', '2025-08-31 14:42:36'),
(12, 2, 'Operations Management', '2025-08-31 14:42:36'),
(13, 2, 'Human Resource Management', '2025-08-31 14:42:36'),
(14, 2, 'Supply Chain & Logistics', '2025-08-31 14:42:36'),
(15, 2, 'Marketing & Branding', '2025-08-31 14:42:36'),
(16, 2, 'Sales & Business Development', '2025-08-31 14:42:36'),
(17, 2, 'Entrepreneurship & Startups', '2025-08-31 14:42:36'),
(18, 2, 'Corporate Governance', '2025-08-31 14:42:36'),
(19, 2, 'Business Analytics & Intelligence', '2025-08-31 14:42:36'),
(20, 2, 'Project Management', '2025-08-31 14:42:36'),
(21, 3, 'Microeconomics', '2025-08-31 14:42:36'),
(22, 3, 'Macroeconomics', '2025-08-31 14:42:36'),
(23, 3, 'International Trade & Finance', '2025-08-31 14:42:36'),
(24, 3, 'Public Policy & Regulation', '2025-08-31 14:42:36'),
(25, 3, 'Economic Development', '2025-08-31 14:42:36'),
(26, 3, 'Behavioral Economics', '2025-08-31 14:42:36'),
(27, 3, 'Monetary Policy & Central Banking', '2025-08-31 14:42:36'),
(28, 3, 'Fiscal Policy & Taxation', '2025-08-31 14:42:36'),
(29, 3, 'Energy & Environmental Economics', '2025-08-31 14:42:36'),
(30, 3, 'Globalization & Geopolitics', '2025-08-31 14:42:36'),
(31, 4, 'Artificial Intelligence & Machine Learning', '2025-08-31 14:42:36'),
(32, 4, 'Blockchain & Cryptocurrencies', '2025-08-31 14:42:36'),
(33, 4, 'Cloud Computing', '2025-08-31 14:42:36'),
(34, 4, 'Internet of Things (IoT)', '2025-08-31 14:42:36'),
(35, 4, 'Cybersecurity', '2025-08-31 14:42:36'),
(36, 4, 'Big Data & Analytics', '2025-08-31 14:42:36'),
(37, 4, 'Software Development & Programming', '2025-08-31 14:42:36'),
(38, 4, 'Robotics & Automation', '2025-08-31 14:42:36'),
(39, 4, 'Augmented Reality (AR) & Virtual Reality (VR)', '2025-08-31 14:42:36'),
(40, 4, 'Quantum Computing', '2025-08-31 14:42:36'),
(41, 5, 'Banking & Financial Services', '2025-08-31 14:42:36'),
(42, 5, 'Healthcare & Biotechnology', '2025-08-31 14:42:36'),
(43, 5, 'Energy & Renewable Resources', '2025-08-31 14:42:36'),
(44, 5, 'Real Estate & Infrastructure', '2025-08-31 14:42:36'),
(45, 5, 'Retail & E-commerce', '2025-08-31 14:42:36'),
(46, 5, 'Manufacturing & Industry 4.0', '2025-08-31 14:42:36'),
(47, 5, 'Transportation & Logistics', '2025-08-31 14:42:36'),
(48, 5, 'Media & Entertainment', '2025-08-31 14:42:36'),
(49, 5, 'Education & EdTech', '2025-08-31 14:42:36'),
(50, 5, 'Tourism & Hospitality', '2025-08-31 14:42:36'),
(51, 6, 'Sustainable Finance & ESG', '2025-08-31 14:42:36'),
(52, 6, 'Digital Transformation', '2025-08-31 14:42:36'),
(53, 6, 'Smart Cities', '2025-08-31 14:42:36'),
(54, 6, 'Space Economy & Aerospace Tech', '2025-08-31 14:42:36'),
(55, 6, 'Green Technologies & Clean Energy', '2025-08-31 14:42:36'),
(56, 6, 'Remote Work & Workforce Automation', '2025-08-31 14:42:36'),
(57, 6, 'Metaverse & Web3', '2025-08-31 14:42:36'),
(58, 6, '5G & Next-gen Connectivity', '2025-08-31 14:42:36'),
(59, 6, 'Biotechnology & Life Sciences', '2025-08-31 14:42:36'),
(60, 6, 'Ethical AI & Responsible Innovation', '2025-08-31 14:42:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `otp_created_at` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `language` mediumtext DEFAULT 'English',
  `bio` mediumtext DEFAULT NULL,
  `remove` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `mobile`, `email`, `password`, `otp`, `otp_created_at`, `is_verified`, `language`, `bio`, `remove`, `created_at`) VALUES
(2, 'Naveen bharathi', '6369800628', 'naveenbharathi5050@gmail.com', '$2y$10$rGRhfKNMSqkbRKJWM8glPO6oqbpkXs8QTxx3f8.IfcjDmychlPyhO', '354823', '2025-09-22 20:15:21', 1, 'tamil', 'its my bio. Vanakanda Maapla', 'yes', '2025-09-22 20:15:21'),
(3, 'Rithick', '1283838676', 'mailtorithickg@gmail.com', '$2y$10$PfyucLopSreXwF9/3BXJwOyF9JHrUiKDX0RgwcMD/JoXzAJbwsS1K', '118093', '2025-10-05 20:45:45', 1, 'English', 'Best Place to Read News ', 'yes', '2025-10-05 20:38:15'),
(4, 'Madhan Kumar', '8248964865', 'elumalaimadhan996@gmail.com', '$2y$10$LUYRy7farSv6hormtRo02./PnAYd7BCfakwJ.3BEDmKp6fqp1ApWC', '856690', '2025-10-06 19:59:54', 1, 'english', 'Hhjjh', NULL, '2025-10-06 19:58:44'),
(5, 'naveen', '6369800233', 'nnaveen@gmaul.com', '$2y$10$czFeYBYF5uA1SitmVE26d.T45nJoWhNCEvZL0J00kuKS8SI4a680m', '155658', '2025-10-06 22:03:09', 0, 'English', NULL, NULL, '2025-10-06 22:03:09'),
(6, 'Rithick ', '8248964886', 'mailtoinvestor@gmail.com', '$2y$10$AlxP1a5WX9h4e529mgHvveLDqxbZqFkyx3XYCwrxASCTD.TKxDPQO', '511503', '2025-10-06 22:11:35', 1, 'English', NULL, NULL, '2025-10-06 22:05:15'),
(7, 'Prakash', '8464656858', 'prakash12131102@gmail.com', '$2y$10$Go3IQHEFoG1CS4.wv23QSOh.kilZb9nHOgXqBpeh..MWS.wByUyBS', '492211', '2025-10-06 22:07:34', 1, 'English', NULL, NULL, '2025-10-06 22:07:34'),
(9, 'jfi', '6369800523', 'jfi@gma.com', '$2y$10$sjITc9IDG8025vuk.eakX.rZOK/xNCcBHCqR1zvE.wxyAXKsdWEn.', '958551', '2025-10-06 22:14:02', 0, 'English', NULL, NULL, '2025-10-06 22:14:02'),
(10, 'MADHANKUMAR ', '9790261489', 'elumalaimadhan53@gmail.com', '$2y$10$qim95jW.6JZaHj..LCRR2Oa3xkuSayi7cl4MZP2LIYdoSKlo8Ljfm', '205813', '2025-10-06 22:16:12', 1, 'English', NULL, NULL, '2025-10-06 22:14:30');

-- --------------------------------------------------------

--
-- Table structure for table `user_categories`
--

CREATE TABLE `user_categories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subcategory_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_categories`
--

INSERT INTO `user_categories` (`id`, `user_id`, `subcategory_id`, `created_at`) VALUES
(102, 2, 58, '2025-10-05 23:17:20'),
(103, 2, 31, '2025-10-05 23:17:20'),
(104, 2, 60, '2025-10-05 23:17:20'),
(105, 2, 6, '2025-10-05 23:17:20'),
(106, 2, 42, '2025-10-05 23:17:20'),
(107, 2, 50, '2025-10-05 23:17:20'),
(109, 4, 58, '2025-10-06 20:01:48'),
(110, 4, 31, '2025-10-06 20:01:48'),
(111, 4, 4, '2025-10-06 20:01:48'),
(112, 4, 39, '2025-10-06 20:01:48'),
(113, 4, 41, '2025-10-06 20:01:48'),
(114, 4, 26, '2025-10-06 20:01:48'),
(115, 4, 36, '2025-10-06 20:01:48'),
(116, 4, 59, '2025-10-06 20:01:48'),
(117, 4, 32, '2025-10-06 20:01:48'),
(118, 4, 19, '2025-10-06 20:01:48'),
(119, 4, 33, '2025-10-06 20:01:48'),
(120, 4, 2, '2025-10-06 20:01:48'),
(121, 4, 18, '2025-10-06 20:01:48'),
(122, 4, 35, '2025-10-06 20:01:48'),
(123, 4, 52, '2025-10-06 20:01:48'),
(124, 4, 25, '2025-10-06 20:01:48'),
(125, 4, 49, '2025-10-06 20:01:48'),
(126, 4, 29, '2025-10-06 20:01:48'),
(127, 4, 43, '2025-10-06 20:01:48'),
(128, 4, 17, '2025-10-06 20:01:48'),
(129, 4, 60, '2025-10-06 20:01:48'),
(130, 4, 6, '2025-10-06 20:01:48'),
(131, 4, 28, '2025-10-06 20:01:48'),
(132, 4, 30, '2025-10-06 20:01:48'),
(133, 4, 55, '2025-10-06 20:01:48'),
(134, 4, 42, '2025-10-06 20:01:48'),
(135, 4, 13, '2025-10-06 20:01:48'),
(136, 4, 10, '2025-10-06 20:01:48'),
(137, 4, 23, '2025-10-06 20:01:48'),
(138, 4, 34, '2025-10-06 20:01:48'),
(139, 4, 3, '2025-10-06 20:01:48'),
(140, 4, 11, '2025-10-06 20:01:48'),
(141, 4, 22, '2025-10-06 20:01:48'),
(142, 4, 46, '2025-10-06 20:01:48'),
(143, 4, 15, '2025-10-06 20:01:48'),
(144, 4, 48, '2025-10-06 20:01:48'),
(145, 4, 57, '2025-10-06 20:01:48'),
(146, 4, 21, '2025-10-06 20:01:48'),
(147, 4, 27, '2025-10-06 20:01:48'),
(148, 4, 12, '2025-10-06 20:01:48'),
(149, 4, 1, '2025-10-06 20:01:48'),
(150, 4, 20, '2025-10-06 20:01:48'),
(151, 4, 24, '2025-10-06 20:01:48'),
(152, 4, 40, '2025-10-06 20:01:48'),
(153, 4, 44, '2025-10-06 20:01:48'),
(154, 4, 56, '2025-10-06 20:01:48'),
(155, 4, 45, '2025-10-06 20:01:48'),
(156, 4, 9, '2025-10-06 20:01:48'),
(157, 4, 38, '2025-10-06 20:01:48'),
(158, 4, 16, '2025-10-06 20:01:48'),
(159, 4, 53, '2025-10-06 20:01:48'),
(160, 4, 37, '2025-10-06 20:01:48'),
(161, 4, 54, '2025-10-06 20:01:48'),
(162, 4, 8, '2025-10-06 20:01:48'),
(163, 4, 14, '2025-10-06 20:01:48'),
(164, 4, 51, '2025-10-06 20:01:48'),
(165, 4, 50, '2025-10-06 20:01:48'),
(166, 4, 47, '2025-10-06 20:01:48'),
(167, 4, 7, '2025-10-06 20:01:48'),
(168, 4, 5, '2025-10-06 20:01:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_posts_category` (`category_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `saved_counts`
--
ALTER TABLE `saved_counts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_categories`
--
ALTER TABLE `user_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `saved_counts`
--
ALTER TABLE `saved_counts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_categories`
--
ALTER TABLE `user_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_category` FOREIGN KEY (`category_id`) REFERENCES `subcategories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_counts`
--
ALTER TABLE `saved_counts`
  ADD CONSTRAINT `saved_counts_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_counts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_categories`
--
ALTER TABLE `user_categories`
  ADD CONSTRAINT `user_categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_categories_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
