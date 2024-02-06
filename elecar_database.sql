-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 06, 2024 at 06:00 PM
-- Wersja serwera: 10.4.24-MariaDB
-- Wersja PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elecar_database`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `maintenance`
--

CREATE TABLE `maintenance` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `service_status` tinyint(1) NOT NULL,
  `creation_date` datetime NOT NULL,
  `service_start` datetime NOT NULL,
  `service_end` datetime NOT NULL,
  `planned` tinyint(1) NOT NULL,
  `user_id` int(11) NOT NULL,
  `maintenance_description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_location` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`id`, `vehicle_id`, `service_status`, `creation_date`, `service_start`, `service_end`, `planned`, `user_id`, `maintenance_description`, `service_location`) VALUES
(17, 14, 1, '2024-01-27 04:53:39', '2024-01-27 16:53:00', '2024-02-10 16:53:00', 1, 33, 'Przegląd Techniczny', 'Stacja Diagnostyczna'),
(18, 15, 0, '2024-01-27 05:07:47', '2024-01-04 17:06:00', '2024-01-29 00:00:00', 1, 32, 'Remont Napędu', 'Salon Napraw Hyundai'),
(19, 16, 0, '2024-01-27 06:40:30', '2024-01-28 18:39:00', '2024-01-28 00:00:00', 1, 32, 'Zmiana K&oacute;ł', 'Stacja Diagnostyczna'),
(20, 16, 1, '2024-01-28 07:04:02', '2024-01-30 19:03:00', '2024-02-04 19:03:00', 1, 33, 'Wymiana klock&oacute;w hamulcowych', 'Stacja Diagnostyczna'),
(21, 15, 1, '2024-01-31 08:47:23', '2024-01-04 08:30:00', '2024-03-04 15:00:00', 1, 32, 'Remont Napędu', 'Salon Napraw Hyundai');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `prices`
--

CREATE TABLE `prices` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `time_unit` enum('hours','days') COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_from` datetime NOT NULL,
  `time_to` datetime NOT NULL,
  `is_flagged` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prices`
--

INSERT INTO `prices` (`id`, `vehicle_id`, `price`, `time_unit`, `time_from`, `time_to`, `is_flagged`) VALUES
(31, 11, 50.00, 'days', '2024-01-27 14:56:00', '2024-02-03 14:56:00', 1),
(32, 11, 60.00, 'days', '2024-02-03 14:56:00', '2024-02-08 15:02:00', 1),
(33, 11, 70.00, 'days', '2024-02-27 15:36:00', '2024-03-03 15:36:00', 1),
(34, 11, 65.00, 'days', '2024-01-27 15:54:00', '2024-01-27 15:56:00', 1),
(35, 11, 66.00, 'days', '2024-01-27 15:56:00', '2024-01-27 16:56:00', 0),
(36, 11, 105.00, 'days', '2024-01-27 17:59:00', '2024-02-10 17:00:00', 0),
(37, 11, 100.00, 'days', '2024-01-27 17:10:00', '2024-01-27 17:58:00', 0),
(38, 12, 120.00, 'days', '2024-01-20 17:11:00', '2024-02-27 23:59:00', 0),
(39, 13, 180.50, 'days', '2024-01-20 17:19:00', '2024-02-03 17:19:00', 0),
(40, 16, 0.00, 'days', '2024-01-13 17:19:00', '2024-01-28 17:19:00', 1),
(41, 16, 17.00, 'hours', '2024-01-13 17:20:00', '2024-02-10 17:20:00', 0),
(42, 16, 20.50, 'hours', '2024-02-11 17:24:00', '2024-02-23 17:25:00', 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `reservation_start` datetime NOT NULL,
  `reservation_end` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `Creation_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `vehicle_id`, `client_id`, `is_active`, `price`, `reservation_start`, `reservation_end`, `user_id`, `Creation_date`) VALUES
(56, 11, 30, 1, 630.00, '2024-01-26 15:30:00', '2024-01-31 16:00:00', 36, '2024-01-31 09:07:53'),
(57, 12, 31, 1, 2520.00, '2024-01-13 17:00:00', '2024-02-02 17:00:00', 36, '2024-01-31 09:09:12');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `roles`
--

CREATE TABLE `roles` (
  `id` tinyint(1) NOT NULL,
  `name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Administrator'),
(2, 'Serwisant'),
(3, 'Ekspedient'),
(4, 'Klient');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth` date NOT NULL,
  `pesel` bigint(11) NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_city` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_zipcode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_street` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_house` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_apartment` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` tinyint(1) NOT NULL,
  `ispasswordchanged` tinyint(1) NOT NULL,
  `isdeleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `firstname`, `lastname`, `birth`, `pesel`, `phone`, `address_city`, `address_zipcode`, `address_street`, `address_house`, `address_apartment`, `role_id`, `ispasswordchanged`, `isdeleted`) VALUES
(30, 'jan.nowak@klient.com', '', 'Jan', 'Nowak', '1975-01-11', 11737147, '111222333', 'Poznań', '61-670', 'Os. Bananowe', '12', '101', 4, 0, 0),
(31, 'joanna.robaczkowa@klient.com', '', 'Joanna', 'Robaczkowa', '2001-03-09', 10293593610, '222444666', 'Nowy Tomyśl', '64-300', 'Prosta', '2', '1', 4, 0, 0),
(32, 'krzysztof.zdolny@serwis.pl', '$2y$10$g1LQ5sadc5CfwfwWe2/Yk.s8pypPAJz.3dKWvTFjFgDQs3AG.G6IS', 'Krzysztof', 'Zdolny', '1987-04-15', 87041582197, '444555111', 'Poznań', '60-670', 'Umultowska', '3', '12', 2, 1, 0),
(33, 'bob.budowlaniec@serwis.pl', '$2y$10$h3qnayCg2se0MVvW.cGmIeNb7hKo9rzauksInegKdxVKq9NUyKan6', 'Bob', 'Budowlaniec', '1998-11-13', 98111346351, '999111777', 'Gniezno', '61-200', 'Słoneczna', '3', '', 2, 1, 0),
(34, 'jan.kochanowski@eksp.pl', '$2y$10$Wae2dH0RVCjB0KHCZFY0Deiz/jif1EtjMdfuFylKhIO7rR8lfJ9hC', 'Jan', 'Kochanowski', '1990-02-28', 90022822814, '130104150', 'Gdynia', '80-209', 'Poznańska', '15', '45', 3, 1, 0),
(35, 'adam.mickiewicz@eksp.pl', '$2y$10$/S6N/JRCFaeh6ikbQ0FBI.kVf4ECpVBXTmSt1R8fg1/2P6jSLT5G2', 'Adam', 'Mickiewicz', '1998-12-24', 98122498911, '991992993', 'Poznań', '61-627', 'Os. Kosmonaut&oacute;w', '11', '10', 3, 0, 0),
(36, 'adam.malysz@adm.pl', '$2y$10$pGlNlb38ovCgpL4/827KwOfENWZPVC/fegyJeoawlrWe9X/Nl1m9W', 'Adam', 'Małysz', '1977-12-03', 77120329775, '997998999', 'Ł&oacute;dź', '70-445', 'Wodna', '15', '', 1, 1, 0),
(37, '24788c767fe54c77df4b918cc9495586fdfe6ff8ae91b4057621ba6e13879bb9', '', 'f1aaae739b432f096bb97e7c5ad0dbb96464fdc143123144aa', 'cbfda0b7ba2c5c1383702bcfaf0ec82ee4cee2fbd69bda593c', '0000-00-00', 0, 'f50c08f72a3ce5e', 'ba92af684cdbc267f3f361cdea02bd8d1e8a3576668866a338', 'd819aef8d1d7b9c2a6ad4653385f14b5fdcffd2f3442fb1feb', 'e217e7f029a1d069116451cac51c41e910214be0145fc359a3', '73475cb40a568e8da8a045ced110137e159f890ac4da883b6b', 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca4', 4, 0, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `license_plate` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `typ` enum('Auto','Hulajnoga') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Dostępny','Wypożyczony','W serwisie','Rozładowany') COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` year(4) NOT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci DEFAULT 'images/vehicles/vehicle-default.png',
  `maintenance_needed` tinyint(1) NOT NULL,
  `maintenance_last` date NOT NULL,
  `battery_level` tinyint(4) NOT NULL,
  `battery_degradation` tinyint(4) NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `license_plate`, `brand`, `model`, `typ`, `status`, `year`, `image`, `maintenance_needed`, `maintenance_last`, `battery_level`, `battery_degradation`, `deleted`) VALUES
(11, 'PO14AABX', 'Tesla', 'Model 3', 'Auto', 'Wypożyczony', '2019', 'images/vehicles/pexels-auto-records-10549262.jpg', 0, '2024-01-02', 97, 89, 0),
(12, 'PO59OME9', 'Tesla', 'Model 3', 'Auto', 'Wypożyczony', '2018', 'images/vehicles/pexels-photo-11139552.png', 0, '2023-11-17', 100, 98, 0),
(13, 'PY087KEK', 'Tesla', 'Model X', 'Auto', 'Dostępny', '2022', 'images/vehicles/pexels-photo-12122691.png', 0, '2024-01-27', 78, 81, 0),
(14, '', 'Motus', 'Pro 10', 'Hulajnoga', 'W serwisie', '2022', 'images/vehicles/pexels-markus-spiske-3671151.jpg', 0, '0000-00-00', 100, 100, 0),
(15, 'PY101ERY', 'Hyundai', 'Kona Electric', 'Auto', 'W serwisie', '2020', 'images/vehicles/pexels-hyundai-motor-group-17318232.jpg', 0, '2024-01-29', 100, 100, 0),
(16, '', 'Xiaomi', 'Mi Electric Scooter M365', 'Hulajnoga', 'W serwisie', '2019', 'images/vehicles/pexels-dominika-roseclay-2727413.jpg', 0, '2024-01-28', 100, 72, 0);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `prices`
--
ALTER TABLE `prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`) USING BTREE;

--
-- Indeksy dla tabeli `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`,`client_id`,`user_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indeksy dla tabeli `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `prices`
--
ALTER TABLE `prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `maintenance_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `maintenance_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `prices`
--
ALTER TABLE `prices`
  ADD CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
