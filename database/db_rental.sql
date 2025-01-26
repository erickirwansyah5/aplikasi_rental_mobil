-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Jan 2025 pada 12.19
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_rental`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `license_plate` varchar(20) NOT NULL,
  `color` varchar(30) NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'default.jpg',
  `features` text DEFAULT NULL,
  `transmission` enum('Manual','Automatic') NOT NULL,
  `fuel_type` enum('Petrol','Diesel','Electric','Hybrid') NOT NULL,
  `seats` int(11) NOT NULL,
  `status` enum('available','rented','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `cars`
--

INSERT INTO `cars` (`id`, `brand`, `model`, `year`, `license_plate`, `color`, `daily_rate`, `description`, `image`, `features`, `transmission`, `fuel_type`, `seats`, `status`, `created_at`) VALUES
(1, 'Toyota', 'Avanza', 2022, 'B 1234 ABC', 'Silver', 350000.00, 'The Toyota Avanza is a versatile MPV perfect for family trips. Featuring spacious interiors and reliable performance.', 'avanza.jpg', 'Air Conditioning,Power Steering,USB Port,Bluetooth,Rear Parking Camera', 'Automatic', 'Petrol', 7, 'available', '2025-01-26 06:17:40'),
(2, 'Honda', 'CR-V', 2023, 'B 2345 DEF', 'Black', 500000.00, 'Experience luxury and comfort with the Honda CR-V. This SUV combines style with advanced safety features.', 'crv.jpg', 'Leather Seats,Sunroof,Navigation System,360 Camera,Smart Entry', 'Automatic', 'Petrol', 5, 'available', '2025-01-26 06:17:40'),
(3, 'Suzuki', 'Ertiga', 2021, 'B 3456 GHI', 'White', 400000.00, 'The Suzuki Ertiga offers exceptional fuel efficiency and comfortable seating for the whole family.', 'ertiga.jpg', 'Air Conditioning,Power Windows,Rear AC Vents,USB Charging,Touchscreen Audio', 'Manual', 'Petrol', 7, 'rented', '2025-01-26 06:17:40'),
(4, 'Toyota', 'Innova', 2022, 'B 4567 JKL', 'Gray', 450000.00, 'The Toyota Innova is the perfect blend of comfort and power. Ideal for both business and family use.', 'innova.jpg', 'Captain Seats,Cruise Control,Premium Audio,Rear Entertainment,Climate Control', 'Automatic', 'Diesel', 8, 'available', '2025-01-26 06:17:40'),
(5, 'Honda', 'Civic', 2023, 'B 5678 MNO', 'Red', 600000.00, 'The all-new Honda Civic combines sporty performance with elegant design. Features the latest Honda Sensing technology.', 'civic.jpg', 'Sport Mode,Honda Sensing,Wireless Charging,Apple CarPlay,Android Auto', 'Automatic', 'Petrol', 5, 'maintenance', '2025-01-26 06:17:40'),
(6, 'Mitsubishi', 'Xpander', 2022, 'B 6789 PQR', 'Silver', 400000.00, 'The Mitsubishi Xpander offers dynamic styling and comfortable space for urban adventures.', 'xpander.jpg', 'Keyless Entry,Push Start,Fold-Flat Seats,LED Headlamps,Stability Control', 'Automatic', 'Petrol', 7, 'available', '2025-01-26 06:17:40'),
(7, 'Daihatsu', 'Xenia', 2021, 'B 7890 STU', 'White', 350000.00, 'The Daihatsu Xenia is an economical family car with great fuel efficiency and reliable performance.', 'xenia.jpg', 'Dual Airbags,ABS,EBD,Air Conditioning,Power Windows', 'Manual', 'Petrol', 7, 'rented', '2025-01-26 06:17:40'),
(8, 'Toyota', 'Fortuner', 2023, 'B 8901 VWX', 'Black', 800000.00, 'Experience premium SUV driving with the Toyota Fortuner. Perfect for both city and off-road adventures.', 'fortuner.jpg', 'Leather Interior,4x4,Hill Assist,Paddle Shifters,Premium Sound System', 'Automatic', 'Diesel', 7, 'available', '2025-01-26 06:17:40'),
(9, 'Honda', 'Brio', 2022, 'B 9012 YZA', 'Blue', 300000.00, 'The Honda Brio is a compact and agile city car. Perfect for urban driving and easy parking.', 'brio.jpg', 'Touch Screen,Bluetooth,USB Port,Power Windows,Rear Parking Sensors', 'Automatic', 'Petrol', 5, 'available', '2025-01-26 06:17:40'),
(10, 'Suzuki', 'XL7', 2022, 'B 0123 BCD', 'Red', 450000.00, 'The Suzuki XL7 offers SUV styling with MPV practicality. Great for family trips and daily commutes.', 'xl7.jpg', 'Smart Entry,Push Start,Rear AC,Roof Rails,Touchscreen Head Unit', 'Automatic', 'Petrol', 7, 'available', '2025-01-26 06:17:40'),
(13, 'iyitaEE', '99003', 3005, 'b 423 ca', 'blues', 900000.00, NULL, '', NULL, 'Manual', 'Petrol', 0, 'maintenance', '2025-01-26 10:54:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `id_card_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `name`, `phone`, `email`, `address`, `id_card_number`, `created_at`) VALUES
(11, 14, 'John Doe', '081234567890', 'john.doe@email.com', 'Jl. Sudirman No. 123, Jakarta', '3171234567890001', '2025-01-26 06:17:40'),
(12, 15, 'Jane Smith', '082345678901', 'jane.smith@email.com', 'Jl. Thamrin No. 456, Jakarta', '3171234567890002', '2025-01-26 06:17:40'),
(13, 16, 'Ahmad Ibrahim', '083456789012', 'ahmad.ibrahim@email.com', 'Jl. Gatot Subroto No. 789, Jakarta', '3171234567890003', '2025-01-26 06:17:40'),
(14, 17, 'Sarah Wilson', '084567890123', 'sarah.wilson@email.com', 'Jl. Kuningan No. 321, Jakarta', '3171234567890004', '2025-01-26 06:17:40'),
(15, 18, 'Michael Chen', '085678901234', 'michael.chen@email.com', 'Jl. Rasuna Said No. 654, Jakarta', '3171234567890005', '2025-01-26 06:17:40'),
(16, 19, 'Linda Susanto', '086789012345', 'linda.susanto@email.com', 'Jl. Casablanca No. 987, Jakarta', '3171234567890006', '2025-01-26 06:17:40'),
(17, 20, 'Budi Santoso', '087890123456', 'budi.santoso@email.com', 'Jl. Satrio No. 246, Jakarta', '3171234567890007', '2025-01-26 06:17:40'),
(18, 21, 'Maria Garcia', '088901234567', 'maria.garcia@email.com', 'Jl. Tendean No. 135, Jakarta', '3171234567890008', '2025-01-26 06:17:40'),
(19, 22, 'David Wong', '089012345678', 'david.wong@email.com', 'Jl. Senopati No. 864, Jakarta', '3171234567890009', '2025-01-26 06:17:40'),
(20, 23, 'Siti Rahayu', '089123456789', 'siti.rahayu@email.com', 'Jl. Wijaya No. 975, Jakarta', '3171234567890010', '2025-01-26 06:17:40'),
(21, 24, 'Meyki Ardiansyah', '085809933656', 'erickirwansyah5a@gmail.com', 'jl. bengkulu-tais m.32\r\nlubuk sahung', '08678678', '2025-01-26 11:15:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `payment_proof` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `rental_id`, `amount`, `payment_status`, `payment_proof`, `created_at`, `updated_at`) VALUES
(1, 19, 900000.00, 'confirmed', 'payment_19_1737883629.jpg', '2025-01-26 08:58:16', '2025-01-26 10:40:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pickup_locations`
--

CREATE TABLE `pickup_locations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pickup_locations`
--

INSERT INTO `pickup_locations` (`id`, `name`, `address`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(1, 'CarRent Kemang', 'Jl. Kemang Raya No. 10, Kemang, Jakarta Selatan', -6.26060000, 106.81630000, '2025-01-26 09:22:18', '2025-01-26 09:22:18'),
(2, 'CarRent Kelapa Gading', 'Jl. Boulevard Raya Kelapa Gading Blok M, Jakarta Utara', -6.15710000, 106.90600000, '2025-01-26 09:22:18', '2025-01-26 09:22:18'),
(3, 'CarRent Kuningan', 'Jl. HR. Rasuna Said Kav. C-22, Kuningan, Jakarta Selatan', -6.21850000, 106.83200000, '2025-01-26 09:22:18', '2025-01-26 09:22:18'),
(4, 'CarRent Pondok Indah', 'Jl. Metro Pondok Indah Kav. IV, Pondok Indah, Jakarta Selatan', -6.28660000, 106.78250000, '2025-01-26 09:22:18', '2025-01-26 09:22:18'),
(5, 'CarRent Sunter', 'Jl. Danau Sunter Utara Blok G-7, Sunter, Jakarta Utara', -6.13870000, 106.86690000, '2025-01-26 09:22:18', '2025-01-26 09:22:18'),
(6, 'CarRent Kemang', 'Jl. Kemang Raya No. 10, Kemang, Jakarta Selatan', -6.26060000, 106.81630000, '2025-01-26 09:22:43', '2025-01-26 09:22:43'),
(7, 'CarRent Kelapa Gading', 'Jl. Boulevard Raya Kelapa Gading Blok M, Jakarta Utara', -6.15710000, 106.90600000, '2025-01-26 09:22:43', '2025-01-26 09:22:43'),
(8, 'CarRent Kuningan', 'Jl. HR. Rasuna Said Kav. C-22, Kuningan, Jakarta Selatan', -6.21850000, 106.83200000, '2025-01-26 09:22:43', '2025-01-26 09:22:43'),
(9, 'CarRent Pondok Indah', 'Jl. Metro Pondok Indah Kav. IV, Pondok Indah, Jakarta Selatan', -6.28660000, 106.78250000, '2025-01-26 09:22:43', '2025-01-26 09:22:43'),
(10, 'CarRent Sunter', 'Jl. Danau Sunter Utara Blok G-7, Sunter, Jakarta Utara', -6.13870000, 106.86690000, '2025-01-26 09:22:43', '2025-01-26 09:22:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `pickup_location_id` int(11) NOT NULL,
  `status` enum('pending','active','completed','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `rentals`
--

INSERT INTO `rentals` (`id`, `customer_id`, `car_id`, `start_date`, `end_date`, `total_amount`, `pickup_location_id`, `status`, `created_at`) VALUES
(1, 2, 3, '2025-01-20', '2025-01-27', 2800000.00, 0, 'active', '2025-01-26 06:17:40'),
(2, 3, 7, '2025-01-22', '2025-01-29', 2450000.00, 0, 'active', '2025-01-26 06:17:40'),
(3, 4, 1, '2025-01-01', '2025-01-05', 1400000.00, 0, 'completed', '2025-01-26 06:17:40'),
(4, 5, 2, '2025-01-03', '2025-01-08', 2500000.00, 0, 'completed', '2025-01-26 06:17:40'),
(5, 6, 4, '2025-01-05', '2025-01-10', 2250000.00, 0, 'completed', '2025-01-26 06:17:40'),
(6, 7, 6, '2025-01-07', '2025-01-12', 2000000.00, 0, 'completed', '2025-01-26 06:17:40'),
(7, 8, 8, '2025-01-09', '2025-01-14', 4000000.00, 0, 'completed', '2025-01-26 06:17:40'),
(8, 9, 9, '2025-01-15', '2025-01-18', 900000.00, 0, 'cancelled', '2025-01-26 06:17:40'),
(9, 10, 10, '2025-01-17', '2025-01-20', 1350000.00, 0, 'cancelled', '2025-01-26 06:17:40'),
(11, 2, 2, '2024-12-27', '2025-01-01', 2500000.00, 0, 'completed', '2025-01-26 06:17:40'),
(12, 3, 4, '2024-12-28', '2025-01-02', 2250000.00, 0, 'completed', '2025-01-26 06:17:40'),
(13, 4, 6, '2024-12-29', '2025-01-03', 2000000.00, 0, 'completed', '2025-01-26 06:17:40'),
(14, 5, 8, '2024-12-30', '2025-01-04', 4000000.00, 0, 'completed', '2025-01-26 06:17:40'),
(15, 6, 9, '2025-01-02', '2025-01-06', 1200000.00, 0, 'completed', '2025-01-26 06:17:40'),
(19, 14, 9, '2025-01-28', '2025-01-30', 900000.00, 5, 'active', '2025-01-26 08:58:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(13, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-01-26 06:17:11'),
(14, 'john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2025-01-26 06:17:11'),
(15, 'jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2025-01-26 06:17:11'),
(16, 'ahmad_ibrahim', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2025-01-26 06:17:11'),
(17, 'sarah_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2025-01-26 06:17:11'),
(18, 'michael_chen', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2025-01-26 06:17:11'),
(19, 'linda_susanto', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2025-01-26 06:17:11'),
(20, 'budi_santoso', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2025-01-26 06:17:11'),
(21, 'maria_garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2025-01-26 06:17:11'),
(22, 'david_wong', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2025-01-26 06:17:11'),
(23, 'siti_rahayu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '2025-01-26 06:17:11'),
(24, 'wijaya@gmail.com', '$2y$10$6nYS8Revb.BWXfZ1ea4EQ.ZceYgZ89Mq7MC6A8tMaHvXYJMYiIvzu', 'customer', '2025-01-26 11:15:57');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_card_number` (`id_card_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indeks untuk tabel `pickup_locations`
--
ALTER TABLE `pickup_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pickup_locations`
--
ALTER TABLE `pickup_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`);

--
-- Ketidakleluasaan untuk tabel `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
