-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 22, 2025 at 10:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ticketing_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied','archived') DEFAULT 'unread',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 'Lotfi', 'let@gmail.com', 'General Inquiry', 'TESTGFDFHGFHFGHFGHFGHGH', 'unread', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2025-12-19 22:31:30', '2025-12-19 22:31:30');

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

CREATE TABLE `hotels` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `available_rooms` int(11) DEFAULT 0,
  `rating` decimal(3,1) DEFAULT 0.0,
  `amenities` text DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `distance_to_stadium` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotels`
--

INSERT INTO `hotels` (`id`, `name`, `location`, `description`, `price_per_night`, `available_rooms`, `rating`, `amenities`, `image`, `distance_to_stadium`, `created_at`) VALUES
(1, 'Stadium Grand Hotel', 'City Center', '5-star luxury hotel with stadium view, premium amenities and excellent service for sports enthusiasts.', 250.00, 14, 4.8, 'Pool, Spa, Gym, Restaurant, Free WiFi, Parking, Room Service, Bar, Concierge', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '0.5 miles', '2025-12-19 20:54:22'),
(2, 'Victory Inn', 'Near Stadium', 'Modern hotel perfect for sports fans with shuttle service to the stadium and sports bar.', 180.00, 25, 4.5, 'Free Breakfast, Bar, Parking, Shuttle Service, Free WiFi, Gym, Business Center', 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '0.3 miles', '2025-12-19 20:54:22'),
(4, 'Sports View Hotel', 'Stadium District', 'Affordable hotel with great stadium views and convenient location for match days.', 120.00, 32, 4.2, 'Free WiFi, Restaurant, Parking, 24-hour Front Desk, Laundry Service, TV, Air Conditioning', 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '0.8 miles', '2025-12-19 20:54:22'),
(5, 'The Athlete\'s Lodge', 'Sports Complex', 'Boutique hotel for sports enthusiasts with gym, sauna, and sports-themed decor.', 200.00, 10, 4.6, 'Gym, Sauna, Sports Bar, Free Shuttle, Free WiFi, Breakfast Included, Game Room', 'https://images.unsplash.com/photo-1516496636080-14fb876e029d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '0.4 miles', '2025-12-19 20:54:22'),
(6, 'Premium Suites', 'Business District', 'Luxury suites with premium services, private pools, and limo service.', 280.00, 5, 4.7, 'Butler Service, Private Pool, Limo Service, Fine Dining, Spa, Gym, Concierge, Airport Transfer', 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '2.0 miles', '2025-12-19 20:54:22'),
(7, 'Match Day Hotel', 'Stadium Area', 'Budget-friendly hotel specifically designed for match day visitors.', 90.00, 45, 3.9, 'Free WiFi, Basic Breakfast, Parking, TV, Fan Zone, Match Day Specials', 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '0.2 miles', '2025-12-19 20:54:22'),
(8, 'Royal Sports Hotel', 'City Center', 'Elegant hotel combining luxury with sports heritage and memorabilia.', 350.00, 10, 4.8, 'Pool, Sports Museum, Fine Dining, Spa, Gym, Library, Conference Rooms, Valet', 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '1.0 miles', '2025-12-19 20:54:22'),
(9, 'Goalpost Inn', 'Near Arena', 'Family-friendly hotel with kids play area and family rooms.', 150.00, 20, 4.3, 'Family Rooms, Kids Play Area, Restaurant, Free WiFi, Parking, Laundry', 'https://images.unsplash.com/photo-1584132915807-fd1f5fbc078f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '0.6 miles', '2025-12-19 20:54:22'),
(10, 'Victory Tower', 'Downtown', 'High-rise hotel with panoramic city views and rooftop bar overlooking the stadium.', 290.00, 18, 4.6, 'Rooftop Bar, Infinity Pool, Restaurant, Gym, Spa, Free WiFi, Valet Parking', 'https://images.unsplash.com/photo-1564501049418-3c27787d01e8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', '1.5 miles', '2025-12-19 20:54:22'),
(11, 'Nahran Hotel', 'Nahran ', 'Nahran ', 250.00, 5, 5.0, 'wifi', NULL, '500', '2025-12-21 18:19:08');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_bookings`
--

CREATE TABLE `hotel_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `hotel_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` int(11) NOT NULL,
  `nights` int(11) NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','cancelled') DEFAULT 'confirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotel_bookings`
--

INSERT INTO `hotel_bookings` (`id`, `user_id`, `hotel_id`, `hotel_name`, `location`, `room_type`, `check_in`, `check_out`, `guests`, `nights`, `price_per_night`, `total_amount`, `payment_method`, `transaction_id`, `booking_date`, `status`) VALUES
(1, 1, 1, 'Stadium Grand Hotel', 'City Center', 'double', '2025-12-19', '2025-12-20', 3, 1, 300.00, 300.00, 'paypal', 'TXN_1766178658_9223', '2025-12-19 21:10:58', 'confirmed'),
(4, 2, 5, 'The Athlete\'s Lodge', 'Sports Complex', 'family', '2025-12-25', '2025-12-26', 4, 1, 360.00, 360.00, 'paypal', 'TXN_1766355472_6256', '2025-12-21 22:17:52', 'confirmed'),
(5, 2, 5, 'The Athlete\'s Lodge', 'Sports Complex', 'double', '2025-12-21', '2025-12-22', 2, 1, 240.00, 240.00, 'paypal', 'TXN_1766355529_3672', '2025-12-21 22:18:49', 'confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `team1` varchar(100) NOT NULL,
  `team2` varchar(100) NOT NULL,
  `competition` varchar(100) NOT NULL,
  `stadium` varchar(100) NOT NULL,
  `match_date` date NOT NULL,
  `match_time` time NOT NULL,
  `category` varchar(50) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `available_tickets` int(11) NOT NULL,
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `team1`, `team2`, `competition`, `stadium`, `match_date`, `match_time`, `category`, `base_price`, `available_tickets`, `image`, `created_at`) VALUES
(8, 'Chelsea', 'Arsenal', 'Premier League', 'Stamford Bridge', '2025-12-24', '15:00:00', 'Football', 65.00, 186, 'https://images.unsplash.com/photo-1575361204480-aadea25e6e68', '2025-12-19 19:46:35'),
(9, 'England', 'Australia', 'The Ashes', 'Lord\'s Cricket Ground', '2025-12-26', '11:00:00', 'Cricket', 55.00, 321, 'https://images.unsplash.com/photo-1531415074968-036ba1b575da', '2025-12-19 19:46:35'),
(10, 'Los Angeles Lakers', 'Boston Celtics', 'NBA Finals', 'Staples Center', '2025-12-21', '19:30:00', 'Basketball', 120.00, 156, 'https://images.unsplash.com/photo-1546519638-68e109498ffc', '2025-12-19 19:46:35'),
(11, 'Roger Federer', 'Rafael Nadal', 'Wimbledon Final', 'All England Club', '2025-12-29', '14:00:00', 'Tennis', 95.00, 82, 'https://images.unsplash.com/photo-1595435934247-5d33b7f92c70', '2025-12-19 19:46:35'),
(13, 'Real Madrid', 'Barcelona', 'La Liga', 'Santiago Bernabeu', '2025-12-27', '21:00:00', 'Football', 90.00, 145, 'https://images.unsplash.com/photo-1598880940080-ff9a29891b85', '2025-12-19 19:46:35'),
(14, 'New York Yankees', 'Boston Red Sox', 'MLB', 'Yankee Stadium', '2025-12-31', '19:00:00', 'Baseball', 60.00, 300, 'https://images.unsplash.com/photo-1546519638-68e109498ffc', '2025-12-19 19:46:35'),
(15, 'Bayern Munich', 'Borussia Dortmund', 'Bundesliga', 'Allianz Arena', '2025-12-30', '18:30:00', 'Football', 70.00, 220, 'https://images.unsplash.com/photo-1547347298-4074fc3086f0', '2025-12-19 16:46:35'),
(16, 'Paris Saint-Germain', 'Olympique Marseille', 'Ligue 1', 'Parc des Princes', '2026-01-03', '20:45:00', 'Football', 68.00, 260, 'https://images.unsplash.com/photo-1521412644187-c49fa049e84d', '2025-12-19 16:46:35');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_type` enum('ticket','hotel','transport') NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','paypal','bank_transfer') NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `ticket_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `booking_type`, `booking_id`, `amount`, `payment_method`, `status`, `transaction_id`, `payment_date`, `ticket_id`) VALUES
(1, 1, 'ticket', 0, 224.99, 'credit_card', 'pending', NULL, '2025-12-18 22:12:07', 11),
(2, 1, 'ticket', 0, 224.99, 'bank_transfer', 'pending', NULL, '2025-12-18 22:26:50', 12),
(3, 1, 'ticket', 0, 224.99, 'credit_card', 'pending', NULL, '2025-12-18 22:38:54', 13),
(4, 1, 'ticket', 0, 224.99, 'credit_card', 'pending', NULL, '2025-12-18 22:41:41', 14),
(5, 1, 'ticket', 0, 224.99, 'bank_transfer', 'pending', NULL, '2025-12-18 22:47:04', 15),
(6, 1, 'ticket', 0, 224.99, 'credit_card', 'pending', NULL, '2025-12-18 22:55:20', 16),
(7, 1, 'ticket', 0, 224.99, 'credit_card', 'pending', NULL, '2025-12-18 22:58:25', 17),
(8, 1, 'ticket', 0, 224.99, 'credit_card', 'pending', NULL, '2025-12-18 23:13:46', 18),
(9, 1, 'ticket', 0, 224.99, 'paypal', 'pending', NULL, '2025-12-19 18:13:41', 19),
(10, 1, 'ticket', 0, 224.99, 'paypal', 'pending', NULL, '2025-12-19 18:18:51', 20),
(11, 1, 'ticket', 0, 224.99, 'paypal', 'pending', NULL, '2025-12-19 18:24:48', 21);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `team1` varchar(100) NOT NULL,
  `team2` varchar(100) NOT NULL,
  `match_date` date NOT NULL,
  `venue` varchar(100) NOT NULL,
  `seat_category` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `booking_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','cancelled') DEFAULT 'confirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `match_id`, `team1`, `team2`, `match_date`, `venue`, `seat_category`, `quantity`, `unit_price`, `total_amount`, `payment_method`, `transaction_id`, `booking_date`, `status`) VALUES
(1, 1, 13, 'Real Madrid', 'Barcelona', '2025-12-27', 'Santiago Bernabeu', 'vip', 5, 180.00, 900.00, 'paypal', 'TXN_1766174429_7181', '2025-12-19 23:00:29', 'confirmed'),
(2, 1, 8, 'Chelsea', 'Arsenal', '2025-12-24', 'Stamford Bridge', 'corporate', 3, 195.00, 585.00, 'paypal', 'TXN_1766174807_2965', '2025-12-19 23:06:47', 'confirmed'),
(4, 2, 11, 'Roger Federer', 'Rafael Nadal', '2025-12-29', 'All England Club', 'premium', 7, 142.50, 997.50, 'paypal', 'TXN_1766355426_8745', '2025-12-22 01:17:06', 'confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `tour_guides`
--

CREATE TABLE `tour_guides` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `country` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `languages` varchar(255) NOT NULL,
  `specialties` varchar(255) NOT NULL,
  `experience_years` int(11) DEFAULT 0,
  `hourly_rate` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tour_guides`
--

INSERT INTO `tour_guides` (`id`, `name`, `email`, `phone`, `country`, `city`, `languages`, `specialties`, `experience_years`, `hourly_rate`, `description`, `profile_image`, `is_available`, `rating`, `total_reviews`, `created_at`, `updated_at`) VALUES
(1, 'Ahmed Al-Salem', 'ahmed.guide@example.com', '+966501234567', 'Saudi Arabia', 'Riyadh', 'Arabic,English', 'Historical Tours,City Tours', 8, 45.00, 'Experienced local guide specializing in Riyadh historical landmarks and city tours.', 'ahmed.jpg', 1, 4.60, 25, '2025-12-22 19:44:34', '2025-12-22 19:44:34'),
(2, 'Fatima Hassan', 'fatima.guide@example.com', '+971551112233', 'UAE', 'Dubai', 'Arabic,English,French', 'Luxury Tours,Shopping Tours', 6, 60.00, 'Professional tour guide offering luxury experiences and shopping tours in Dubai.', 'fatima.jpg', 1, 4.80, 40, '2025-12-22 19:44:34', '2025-12-22 19:44:34'),
(3, 'Omar Khaled', 'omar.guide@example.com', '+201001234567', 'Egypt', 'Cairo', 'Arabic,English,Spanish', 'Museum Tours,Historical Tours', 10, 35.00, 'Certified guide with deep knowledge of ancient Egyptian history and museums.', 'omar.jpg', 1, 4.90, 70, '2025-12-22 19:44:34', '2025-12-22 20:40:15');

-- --------------------------------------------------------

--
-- Table structure for table `tour_guide_bookings`
--

CREATE TABLE `tour_guide_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `guide_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `hours` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `group_size` int(11) NOT NULL DEFAULT 1,
  `special_requirements` text DEFAULT NULL,
  `contact_name` varchar(100) NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `contact_phone` varchar(20) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tour_guide_bookings`
--

INSERT INTO `tour_guide_bookings` (`id`, `user_id`, `guide_id`, `booking_date`, `start_time`, `end_time`, `hours`, `total_amount`, `group_size`, `special_requirements`, `contact_name`, `contact_email`, `contact_phone`, `status`, `payment_status`, `payment_method`, `transaction_id`, `created_at`) VALUES
(1, 2, 3, '2025-12-26', '00:00:00', '00:00:00', 2, 70.00, 1, 'Test', 'Faris', 'fares@gmail.com', '+96652323327', 'pending', 'pending', 'cash', NULL, '2025-12-22 20:36:02');

-- --------------------------------------------------------

--
-- Table structure for table `tour_guide_reviews`
--

CREATE TABLE `tour_guide_reviews` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `guide_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transport`
--

CREATE TABLE `transport` (
  `id` int(11) NOT NULL,
  `vehicle_name` varchar(100) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `driver_name` varchar(100) NOT NULL,
  `driver_contact` varchar(20) NOT NULL,
  `registration_no` varchar(50) NOT NULL,
  `status` enum('available','in_use','maintenance','deleted') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transport`
--

INSERT INTO `transport` (`id`, `vehicle_name`, `vehicle_type`, `capacity`, `driver_name`, `driver_contact`, `registration_no`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Car', 'metro', 50, 'Sami', '+96652332474', '502332', 'available', '2025-12-21 18:22:31', '2025-12-21 18:22:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transport_bookings`
--

CREATE TABLE `transport_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transport_type` varchar(50) NOT NULL,
  `departure_date` date NOT NULL,
  `departure_time` time NOT NULL,
  `estimated_arrival` time DEFAULT NULL,
  `from_location` varchar(200) NOT NULL,
  `to_location` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_ticket` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `status` enum('confirmed','cancelled','pending') DEFAULT 'confirmed',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transport_bookings`
--

INSERT INTO `transport_bookings` (`id`, `user_id`, `transport_type`, `departure_date`, `departure_time`, `estimated_arrival`, `from_location`, `to_location`, `quantity`, `price_per_ticket`, `total_amount`, `vehicle_id`, `status`, `booking_date`) VALUES
(1, 1, 'metro', '2025-12-24', '20:35:00', '21:05:00', 'Najran', 'Jeddah', 1, 10.00, 10.00, NULL, 'confirmed', '2025-12-21 17:34:51'),
(2, 1, 'bus', '2025-12-31', '21:00:00', '22:00:00', 'Najran', 'Jeddah', 1, 10.00, 10.00, NULL, 'confirmed', '2025-12-21 17:37:41'),
(3, 2, 'train', '2025-12-31', '22:00:00', '00:00:00', 'Najran', 'Jeddah', 1, 25.00, 25.00, NULL, 'confirmed', '2025-12-21 18:23:05'),
(4, 2, 'train', '2025-12-31', '02:00:00', '04:00:00', 'Makkah', 'Dubai', 10, 25.00, 250.00, NULL, 'confirmed', '2025-12-21 22:19:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `newsletter_subscribed` tinyint(1) DEFAULT 0,
  `account_status` enum('active','suspended','pending') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `created_at`, `first_name`, `last_name`, `date_of_birth`, `country`, `address`, `newsletter_subscribed`, `account_status`, `last_login`, `profile_image`) VALUES
(1, 'Lotfim', 'lutfibanah@yahoo.com', '$2y$10$iS/NGuKiLYsU6E0.7kZKne2H.k5alPP/xWzPkFy7ixJEwji1x6L4q', 'Lotfi Salem', 'Lotfim', '2025-12-18 21:23:42', NULL, NULL, NULL, NULL, NULL, 0, 'active', NULL, NULL),
(2, 'admin', 'fares@gmail.com', '$2y$10$Jw.upoHsUOo0v3ea5RCdvOYgChbeAv6zReSax3y3bdNV2wJ3rhASK', 'Faris', '+966525546666', '2025-12-21 18:10:23', NULL, NULL, NULL, NULL, NULL, 0, 'active', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `email` (`email`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `hotels`
--
ALTER TABLE `hotels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tour_guides`
--
ALTER TABLE `tour_guides`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tour_guide_bookings`
--
ALTER TABLE `tour_guide_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `guide_id` (`guide_id`);

--
-- Indexes for table `tour_guide_reviews`
--
ALTER TABLE `tour_guide_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `guide_id` (`guide_id`);

--
-- Indexes for table `transport`
--
ALTER TABLE `transport`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_no` (`registration_no`);

--
-- Indexes for table `transport_bookings`
--
ALTER TABLE `transport_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hotels`
--
ALTER TABLE `hotels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tour_guides`
--
ALTER TABLE `tour_guides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tour_guide_bookings`
--
ALTER TABLE `tour_guide_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tour_guide_reviews`
--
ALTER TABLE `tour_guide_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport`
--
ALTER TABLE `transport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transport_bookings`
--
ALTER TABLE `transport_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hotel_bookings`
--
ALTER TABLE `hotel_bookings`
  ADD CONSTRAINT `hotel_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `hotel_bookings_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tour_guide_bookings`
--
ALTER TABLE `tour_guide_bookings`
  ADD CONSTRAINT `tour_guide_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tour_guide_bookings_ibfk_2` FOREIGN KEY (`guide_id`) REFERENCES `tour_guides` (`id`);

--
-- Constraints for table `tour_guide_reviews`
--
ALTER TABLE `tour_guide_reviews`
  ADD CONSTRAINT `tour_guide_reviews_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `tour_guide_bookings` (`id`),
  ADD CONSTRAINT `tour_guide_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tour_guide_reviews_ibfk_3` FOREIGN KEY (`guide_id`) REFERENCES `tour_guides` (`id`);

--
-- Constraints for table `transport_bookings`
--
ALTER TABLE `transport_bookings`
  ADD CONSTRAINT `transport_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transport_bookings_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `transport` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
