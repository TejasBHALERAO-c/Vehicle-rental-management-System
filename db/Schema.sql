


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('Bikes','Scooters','Cars') NOT NULL,
  `is_premium` tinyint(1) DEFAULT 0,
  `base_fare` decimal(10,2) NOT NULL,
  `rate_per_km` decimal(10,2) NOT NULL,
  `availability` tinyint(1) DEFAULT 1,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `km_distance` int(11) NOT NULL,
  `estimated_price` decimal(10,2) NOT NULL,
  `days` int(10) NOT NULL,
  `pickup_type` enum('pickup','delivery') NOT NULL,
  `delivery_address` varchar(255) DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;





INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `is_admin`, `created_at`) VALUES
(1, 'Admin User', 'admin@vehiclerental.com', '$2y$10$xZkPJSU2bsHl7P.M0L.fquOVfW2rQ6V2zPqZH0jXZU2J0.1FJE1mO', '1234567890', 1, '2025-11-23 06:29:46'),
(2, 'John Doe', 'john@example.com', '$2y$10$xZkPJSU2bsHl7P.M0L.fquOVfW2rQ6V2zPqZH0jXZU2J0.1FJE1mO', '9876543210', 0, '2025-11-23 06:29:46'),
(3, 'Jane Smith', 'jane@example.com', '$2y$10$xZkPJSU2bsHl7P.M0L.fquOVfW2rQ6V2zPqZH0jXZU2J0.1FJE1mO', '9123456789', 0, '2025-11-23 06:29:46'),
(4, 'abc', 'abc@12gamil.com', '$2y$10$GgfoLS2bsGdbFXbwq6g.2eU87GWBNJSc3IaR18Kre1A3TJ6aSTs9C', '999292992', 0, '2025-11-23 06:33:02'),
(5, 'tejas BHALERAO', 'ayushbhalero05@gmail.com', '$2y$10$Ur9Su5Z6AcozLFKctsQ2l.6cDHxS7kDn.kBIIN/kl3HGbEp.LTpgO', '08329889416', 0, '2026-01-31 06:51:57'),
(6, 'tanamy', 'tanmay2@gmail.com', '$2y$10$7g4ayx60l9ZNr3qI3lEcFeFeCMEpa8Ws6c/TTiZQAxYdnRY97rdma', '3554455465', 0, '2026-02-10 09:57:59'),
(7, 'KJ', 'KJ123@gmail.com.COM', '$2y$10$5KLumYTl8avJ4V4SFislfuimhG81jiGNmZIwTcU1jmsUXF4HLDkvy', '8794561230', 0, '2026-02-10 12:05:36'),
(8, 'Rahul Arjit Pawar', 'rahul45@gmail.com', '$2y$10$Y7qPIFndUWVdyV0zJhp8juyzvY6EsXA1afJVIdZhP49WE/uYeRzJu', '9878987898', 0, '2026-03-12 06:12:23');


INSERT INTO `vehicles` (`id`, `name`, `category`, `is_premium`, `base_fare`, `rate_per_km`, `availability`, `image_url`, `description`, `created_at`) VALUES
(2, 'Yamaha MT-07', 'Bikes', 0, 715.00, 5.94, 1, 'public/Yamaha_MT-07.jpg', 'Naked street bike with aggressive styling and excellent handling. Great for urban commuting.', '2025-11-17 00:57:54'),
(3, 'Kawasaki Ninja 650', 'Bikes', 0, 715.00, 5.94, 1, 'public/Kawasaki_Ninja_650.jpg', 'Sport touring motorcycle with comfortable ergonomics and sporty performance.', '2025-11-17 00:57:54'),
(4, 'Harley Davidson Sportster', 'Bikes', 0, 715.00, 5.94, 1, 'public/Harley_Davidson_Sportster.jpg', 'Iconic American cruiser with V-twin engine and classic styling. Premium cruiser.', '2025-11-17 00:57:54'),
(5, 'Vespa LX 125', 'Scooters', 0, 330.00, 2.81, 1, 'public/Vespa_LX_125.jpg', 'Classic Italian scooter with timeless design and reliable performance. Perfect for city commuting.', '2025-11-17 00:57:54'),
(7, 'Piaggio Vespa Primavera', 'Scooters', 0, 300.00, 2.01, 1, 'public/Piaggio_Vespa_Primavera.jpg', 'Modern Vespa with classic styling and contemporary technology. Elegant city scooter.', '2025-11-17 00:57:54'),
(8, 'Vespa GTS 300', 'Scooters', 0, 330.00, 2.81, 1, 'public/Vespa_GTS_300.jpg', 'Powerful premium Vespa with larger engine and premium build quality. Premium scooter.', '2025-11-17 00:57:54'),
(9, 'Toyota Camry', 'Cars', 0, 2750.00, 25.92, 1, 'public/Toyota_Camry.jpg', 'Comfortable and reliable mid-size sedan perfect for city and highway driving. Excellent fuel economy.', '2025-11-17 00:57:54'),
(10, 'Honda Accord', 'Cars', 0, 2750.00, 25.92, 1, 'public/Honda_Accord.jpg', 'Premium mid-size sedan with advanced safety features and refined interior. Great for long drives.', '2025-11-17 00:57:54'),
(11, 'BMW 3 Series', 'Cars', 1, 3250.00, 28.80, 1, 'public/BMW_3_Series.jpg', 'Luxury sports sedan with exceptional driving dynamics and premium features. Premium vehicle.', '2025-11-17 00:57:54'),
(12, 'Mercedes-Benz C-Class', 'Cars', 1, 3250.00, 28.80, 1, 'public/Mercedes-Benz_C-Class.jpg', 'Premium luxury sedan with elegant design and advanced technology. Premium luxury.', '2025-11-17 00:57:54'),
(13, 'Honda CB500F', 'Bikes', 0, 715.00, 5.94, 1, 'public/Honda_CBR600RR.jpg', 'Sleek and powerful sports bike perfect for city and highway riding. Features advanced ABS and fuel injection.', '2025-11-17 00:58:32'),
(16, 'Honda CBR600RR', 'Bikes', 0, 715.00, 5.94, 1, 'public/Honda_CBR600RR.jpg', 'High-performance supersport bike with race-inspired technology and aerodynamics.', '2025-11-17 00:58:32'),
(17, 'Yamaha R6', 'Bikes', 0, 715.00, 5.94, 1, 'public/Yamaha_R6.jpg', 'Track-focused sportbike with exceptional cornering capabilities and power delivery.', '2025-11-17 00:58:32'),
(18, 'Suzuki GSX-R600', 'Bikes', 0, 715.00, 5.94, 1, 'public/kawasaki-motorcycle.jpg', 'Legendary sportbike with proven track record and reliable performance.', '2025-11-17 00:58:32'),
(19, 'Ducati Monster 696', 'Bikes', 1, 845.00, 6.60, 1, 'public/Ducati_Monster_696.jpg', 'Italian naked bike with iconic design and premium build quality. Premium vehicle.', '2025-11-17 00:58:32'),
(20, 'Triumph Street Triple', 'Bikes', 1, 845.00, 6.60, 1, 'public/Triumph_Street_Triple.jpg', 'British triple-cylinder naked bike with character and performance. Premium option.', '2025-11-17 00:58:32'),
(21, 'KTM Duke 390', 'Bikes', 0, 715.00, 5.94, 1, 'public/KTM_Duke_390.jpg', 'Compact and agile city bike with excellent power-to-weight ratio.', '2025-11-17 00:58:32'),
(22, 'Royal Enfield Classic 350', 'Bikes', 0, 715.00, 5.94, 1, 'public/Royal_Enfield_Classic_350.jpg', 'Classic retro-styled cruiser with timeless appeal and comfortable ride.', '2025-11-17 00:58:32'),
(24, 'BMW G310R', 'Bikes', 1, 845.00, 6.60, 1, 'public/BMW_G310R.jpg', 'Entry-level premium bike with BMW quality and German engineering.', '2025-11-17 00:58:32'),
(25, 'Kawasaki Z650', 'Bikes', 0, 715.00, 5.94, 1, 'public/Kawasaki_Z650.jpg', 'Versatile naked bike perfect for daily commuting and weekend rides.', '2025-11-17 00:58:32'),
(26, 'Honda CB650R', 'Bikes', 0, 715.00, 5.94, 1, 'public/Honda_CB650R.jpg', 'Modern neo-sports café with four-cylinder engine and premium features.', '2025-11-17 00:58:32'),
(27, 'Yamaha FZ-07', 'Bikes', 0, 715.00, 5.94, 1, 'public/Yamaha_FZ-07.jpg', 'Torque-rich parallel twin perfect for city streets and twisty roads.', '2025-11-17 00:58:32'),
(28, 'Ducati Scrambler', 'Bikes', 1, 845.00, 6.60, 1, 'public/Ducati_Scrambler.jpg', 'Retro-modern scrambler with Italian flair and premium components. Premium bike.', '2025-11-17 00:58:32'),
(29, 'Triumph Bonneville T100', 'Bikes', 1, 845.00, 6.60, 1, 'public/Triumph_Bonneville_T100.jpg', 'Classic British twin with modern reliability and timeless style. Premium classic.', '2025-11-17 00:58:32'),
(30, 'Kawasaki Versys 650', 'Bikes', 0, 715.00, 5.94, 1, 'public/Kawasaki_Versys_650.jpg', 'Adventure-touring bike with upright ergonomics and versatile performance.', '2025-11-17 00:58:32'),
(31, 'Honda Africa Twin', 'Bikes', 0, 715.00, 5.94, 1, 'public/honda-motorcycle.jpg', 'Adventure bike built for both on-road and off-road exploration. Premium adventure.', '2025-11-17 00:58:32'),
(32, 'Yamaha MT-09', 'Bikes', 0, 715.00, 5.94, 1, 'public/Yamaha_MT-09.jpg', 'Powerful triple-cylinder naked bike with advanced electronics and aggressive styling.', '2025-11-17 00:58:32'),
(34, 'Honda Activa 6G', 'Scooters', 0, 330.00, 2.81, 1, 'public/Honda_Activa_6G.jpg', 'Indias most trusted scooter with proven reliability and excellent fuel economy.', '2025-11-17 00:58:32'),
(36, 'Yamaha Fascino 125', 'Scooters', 0, 330.00, 2.81, 1, 'public/Yamaha_Fascino_125.jpg', 'Stylish and fuel-efficient scooter with modern features and comfortable ride.', '2025-11-17 00:58:32'),
(37, 'TVS Jupiter', 'Scooters', 0, 270.00, 2.39, 1, 'public/TVS_Jupiter.jpg', 'Practical and reliable scooter with good storage space and smooth performance.', '2025-11-17 00:58:32'),
(38, 'Suzuki Access 125', 'Scooters', 0, 330.00, 2.81, 1, 'public/Suzuki_Access_125.jpg', 'Feature-rich scooter with excellent build quality and comfortable ergonomics.', '2025-11-17 00:58:32'),
(39, 'Honda Dio', 'Scooters', 0, 330.00, 2.81, 1, 'public/Honda_Dio.jpg', 'Compact and agile scooter perfect for navigating through city traffic.', '2025-11-17 00:58:32'),
(40, 'Aprilia SR 150', 'Scooters', 0, 330.00, 2.81, 1, 'public/Honda_Grazia.jpg', 'Sporty scooter with racing DNA and premium features. Premium sport scooter.', '2025-11-17 00:58:32'),
(42, 'Honda PCX 150', 'Scooters', 0, 330.00, 2.81, 1, 'public/honda-scooter.jpg', 'Maxi-scooter with excellent storage and comfortable long-distance capability.', '2025-11-17 00:58:32'),
(43, 'Yamaha Ray ZR', 'Scooters', 0, 330.00, 2.81, 1, 'public/Yamaha_Fascino_125.jpg', 'Sporty scooter with aggressive styling and peppy performance.', '2025-11-17 00:58:32'),
(44, 'Hero Pleasure Plus', 'Scooters', 0, 270.00, 2.39, 1, 'public/Suzuki_Access_125.jpg', 'Affordable and reliable scooter designed for urban women riders.', '2025-11-17 00:58:32'),
(45, 'TVS Ntorq 125', 'Scooters', 0, 270.00, 2.39, 1, 'public/TVS_Ntorq_125.jpg', 'Sporty scooter with racing-inspired design and performance-oriented features.', '2025-11-17 00:58:32'),
(46, 'Honda Grazia', 'Scooters', 0, 330.00, 2.81, 1, 'public/Honda_Grazia.jpg', 'Stylish scooter with modern design and advanced features for urban commuting.', '2025-11-17 00:58:32'),
(47, 'Piaggio Vespa SXL', 'Scooters', 0, 330.00, 2.81, 1, 'public/Piaggio_Vespa_SXL.jpg', 'Larger Vespa with more power and premium styling elements.', '2025-11-17 00:58:32'),
(48, 'Yamaha Aerox 155', 'Scooters', 0, 330.00, 2.81, 1, 'public/Yamaha_Aerox_155.jpg', 'Racing-inspired scooter with powerful engine and sporty design. Premium sport scooter.', '2025-11-17 00:58:32'),
(49, 'Honda activa 6g', 'Scooters', 0, 330.00, 2.81, 1, 'public/Honda_Activa_6G.jpg', 'Premium maxi-scooter with touring capabilities and advanced features. Premium touring.', '2025-11-17 00:58:32'),
(50, 'Vespa Elettrica', 'Scooters', 0, 330.00, 2.81, 1, 'public/Vespa_Elettrica.jpg', 'Electric Vespa with zero emissions and premium Italian design. Premium eco scooter.', '2025-11-17 00:58:32'),
(51, 'Kymco Like 150i', 'Scooters', 0, 270.00, 2.39, 1, 'public/Kymco_Like_150i.jpg', 'Reliable Taiwanese scooter with good build quality and practical features.', '2025-11-17 00:58:32'),
(52, 'Sym Fiddle III', 'Scooters', 0, 270.00, 2.39, 1, 'public/Sym_Fiddle_III.jpg', 'Vespa-inspired scooter with retro styling and modern reliability.', '2025-11-17 00:58:32'),
(55, 'Toyota Corolla', 'Cars', 0, 2750.00, 25.92, 1, 'public/Toyota_Corolla.jpg', 'Compact sedan known for reliability and excellent resale value. Perfect daily driver.', '2025-11-17 00:58:32'),
(56, 'Honda City', 'Cars', 0, 2750.00, 25.92, 1, 'public/Honda_City.jpg', 'Comfortable sedan for city driving with spacious interior and modern features.', '2025-11-17 00:58:32'),
(57, 'Hyundai Elantra', 'Cars', 0, 2750.00, 25.92, 1, 'public/Hyundai_Elantra.jpg', 'Stylish compact sedan with feature-rich interior and smooth ride quality.', '2025-11-17 00:58:32'),
(58, 'Nissan Altima', 'Cars', 0, 2750.00, 25.92, 1, 'public/Nissan_Altima.jpg', 'Comfortable family sedan with excellent fuel economy and spacious cabin.', '2025-11-17 00:58:32'),
(59, 'Mazda 6', 'Cars', 0, 2750.00, 25.92, 1, 'public/Mazda_6.jpg', 'Sporty mid-size sedan with engaging driving dynamics and premium feel.', '2025-11-17 00:58:32'),
(60, 'Subaru Legacy', 'Cars', 0, 2750.00, 25.92, 1, 'public/Subaru_Legacy.jpg', 'All-wheel-drive sedan with excellent safety ratings and reliable performance.', '2025-11-17 00:58:32'),
(61, 'Volkswagen Jetta', 'Cars', 0, 2750.00, 25.92, 1, 'public/Volkswagen_Jetta.jpg', 'German-engineered compact sedan with solid build quality and efficient engines.', '2025-11-17 00:58:32'),
(62, 'Ford Fusion', 'Cars', 0, 2750.00, 25.92, 1, 'public/Ford_Fusion.jpg', 'Comfortable mid-size sedan with good technology features and smooth ride.', '2025-11-17 00:58:32'),
(65, 'Audi A4', 'Cars', 1, 3250.00, 28.80, 1, 'public/Audi_A4.jpg', 'Sophisticated luxury sedan with quattro all-wheel drive and premium interior. Premium German.', '2025-11-17 00:58:32'),
(66, 'Lexus ES 350', 'Cars', 0, 2750.00, 25.92, 1, 'public/Lexus_ES_350.jpg', 'Luxury sedan with exceptional comfort and reliability. Premium Japanese luxury.', '2025-11-17 00:58:32'),
(67, 'BMW 5 Series', 'Cars', 1, 3250.00, 28.80, 1, 'public/BMW_5_Series.jpg', 'Executive luxury sedan with powerful engines and cutting-edge technology. Premium executive.', '2025-11-17 00:58:32'),
(68, 'Mercedes-Benz E-Class', 'Cars', 1, 3250.00, 28.80, 1, 'public/Mercedes-Benz_E-Class.jpg', 'Premium mid-size luxury sedan with sophisticated design and advanced features. Premium luxury.', '2025-11-17 00:58:32'),
(69, 'Audi A6', 'Cars', 1, 3250.00, 28.80, 1, 'public/Audi_A6.jpg', 'Executive sedan with quattro technology and premium craftsmanship. Premium executive.', '2025-11-17 00:58:32'),
(70, 'Porsche 911', 'Cars', 0, 2750.00, 25.92, 1, 'public/Porsche_911.jpg', 'Iconic sports car with legendary performance and engineering excellence. Premium sports car.', '2025-11-17 00:58:32'),
(71, 'Tesla Model S', 'Cars', 1, 3250.00, 28.80, 1, 'public/Tesla_Model_S.jpg', 'Electric luxury sedan with cutting-edge technology and instant acceleration. Premium electric.', '2025-11-17 00:58:32'),
(72, 'Jaguar XF', 'Cars', 0, 2750.00, 25.92, 1, 'public/Jaguar_XF.jpg', 'British luxury sedan with elegant design and refined performance. Premium British luxury.', '2025-11-17 00:58:32'),
(73, 'Maruti Swift', 'Cars', 0, 2250.00, 22.08, 1, 'public/Maruti_Swift.jpg', 'Popular compact hatchback, fuel-efficient, ideal for city rides.', '2025-11-17 01:00:00'),
(74, 'Hyundai i20', 'Cars', 0, 2750.00, 25.92, 1, 'public/Hyundai_i20.jpg', 'Premium hatchback with modern design and comfortable interiors.', '2025-11-17 01:00:00'),
(75, 'Tata Tiago', 'Cars', 0, 2250.00, 22.08, 1, 'public/Tata_Tiago.jpg', 'Affordable hatchback with good mileage and compact size.', '2025-11-17 01:00:00'),
(76, 'Maruti Wagon R', 'Cars', 0, 2250.00, 22.08, 1, 'public/Maruti_Wagon_R.jpg', 'Tall hatchback, very practical and spacious for city driving.', '2025-11-17 01:00:00'),
(77, 'Renault Kwid', 'Cars', 0, 2750.00, 25.92, 1, 'public/Renault_Kwid.jpg', 'Small hatchback with SUV-like styling and low running cost.', '2025-11-17 01:00:00'),
(78, 'Honda Jazz', 'Cars', 0, 2750.00, 25.92, 1, 'public/Honda_Jazz.jpg', 'Premium hatchback with spacious boot and responsive engine.', '2025-11-17 01:00:00'),
(80, 'Hyundai Verna', 'Cars', 0, 2750.00, 25.92, 1, 'public/Hyundai_Verna.jpg', 'Sporty mid-size sedan with modern features and comfort.', '2025-11-17 01:00:00'),
(81, 'Maruti Dzire', 'Cars', 0, 2250.00, 22.08, 1, 'public/Maruti_Dzire.jpg', 'Compact sedan with good fuel efficiency and a comfortable ride.', '2025-11-17 01:00:00'),
(82, 'Toyota Yaris', 'Cars', 0, 2750.00, 25.92, 1, 'public/Toyota_Yaris.jpg', 'Stylish sedan with smooth driving dynamics and reliable performance.', '2025-11-17 01:00:00'),
(83, 'Skoda Rapid', 'Cars', 0, 2750.00, 25.92, 1, 'public/Skoda_Rapid.jpg', 'European-styled sedan with good boot space and stable handling.', '2025-11-17 01:00:00'),
(84, 'Volkswagen Vento', 'Cars', 0, 2750.00, 25.92, 1, 'public/Volkswagen_Vento.jpg', 'German sedan with strong build quality and refined ride.', '2025-11-17 01:00:00'),
(85, 'Mercedes C Class', 'Cars', 1, 3250.00, 28.80, 1, 'public/Mercedes_C_Class.jpg', 'Luxury sedan with premium comfort, great for business & leisure.', '2025-11-17 01:00:00'),
(87, 'Hyundai Creta', 'Cars', 0, 2750.00, 25.92, 1, 'public/Hyundai_Creta.jpg', 'Compact SUV with modern features and good ground clearance.', '2025-11-17 01:00:00'),
(88, 'Tata Nexon', 'Cars', 0, 2250.00, 22.08, 1, 'public/Tata_Nexon.jpg', 'Safe and practical small SUV, very efficient.', '2025-11-17 01:00:00'),
(89, 'Mahindra XUV300', 'Cars', 0, 2750.00, 25.92, 1, 'public/Mahindra_XUV300.jpg', 'Premium compact SUV with strong performance.', '2025-11-17 01:00:00'),
(90, 'Kia Seltos', 'Cars', 0, 2750.00, 25.92, 1, 'public/Kia_Seltos.jpg', 'Popular SUV with dynamic styling and premium amenities.', '2025-11-17 01:00:00'),
(91, 'Hyundai Tucson', 'Cars', 0, 2750.00, 25.92, 1, 'public/Hyundai_Tucson.jpg', 'Mid-size SUV, perfect for city and occasional long drives.', '2025-11-17 01:00:00'),
(92, 'Mahindra XUV500', 'Cars', 0, 2750.00, 25.92, 1, 'public/Mahindra_XUV500.jpg', 'Spacious SUV for family trips and comfort.', '2025-11-17 01:00:00'),
(93, 'Toyota Innova Crysta', 'Cars', 0, 2750.00, 25.92, 1, 'public/Toyota_Innova_Crysta.jpg', 'MPV-like SUV, great for group travel and luggage.', '2025-11-17 01:00:00'),
(94, 'Ford Endeavour', 'Cars', 0, 2750.00, 25.92, 1, 'public/Ford_Endeavour.jpg', 'Large premium SUV, powerful and rugged.', '2025-11-17 01:00:00'),
(95, 'Toyota Fortuner', 'Cars', 0, 2750.00, 25.92, 1, 'public/Toyota_Fortuner.jpg', 'High-end, reliable SUV with off-road capability.', '2025-11-17 01:00:00'),
(96, 'Mahindra Thar', 'Cars', 0, 2750.00, 25.92, 1, 'public/Mahindra_Thar.jpg', 'Iconic off-road SUV, ideal for adventure trips.', '2025-11-17 01:00:00'),
(97, 'Maruti Ertiga', 'Cars', 0, 2250.00, 22.08, 1, 'public/Maruti_Ertiga.jpg', '7-seater MPV, ideal for family outings and group travel.', '2025-11-17 01:00:00'),
(98, 'Toyota Innova Hycross', 'Cars', 0, 2750.00, 25.92, 1, 'public/Toyota_Innova_Hycross.jpg', 'Modern hybrid MPV with premium interiors and comfort.', '2025-11-17 01:00:00'),
(99, 'Honda BR-V', 'Cars', 0, 2750.00, 25.92, 1, 'public/Honda_BR-V.jpg', 'Seven-seater crossover with efficient space usage.', '2025-11-17 01:00:00'),
(100, 'Tata Nexon EV', 'Cars', 0, 2250.00, 22.08, 1, 'public/Tata_Nexon_EV.jpg', 'Compact electric SUV, eco-friendly and smooth ride.', '2025-11-17 01:00:00'),
(101, 'MG ZS EV', 'Cars', 0, 2750.00, 25.92, 1, 'public/MG_ZS_EV.jpg', 'Electric SUV with spacious interiors and modern tech.', '2025-11-17 01:00:00'),
(102, 'Hyundai Kona EV', 'Cars', 0, 2750.00, 25.92, 1, 'public/Hyundai_Kona_EV.jpg', 'Compact electric SUV, great range & premium feel.', '2025-11-17 01:00:00'),
(103, 'Audi Q5', 'Cars', 1, 3250.00, 28.80, 1, 'public/Audi_Q5.jpg', 'Luxurious SUV with performance and premium utility.', '2025-11-17 01:00:00'),
(104, 'BMW X3', 'Cars', 1, 3250.00, 28.80, 1, 'public/BMW_X3.jpg', 'Mid-size luxury SUV with sporty appeal and class.', '2025-11-17 01:00:00'),
(105, 'Mercedes GLC', 'Cars', 1, 3250.00, 28.80, 1, 'public/Mercedes_GLC.jpg', 'Elegant luxury SUV with top-tier comfort.', '2025-11-17 01:00:00'),
(106, 'Toyota Innova Crysta ZX', 'Cars', 0, 2750.00, 25.92, 1, 'public/Toyota_Innova_Crysta_ZX.jpg', 'Top variant of Innova Crysta for premium family travel.', '2025-11-17 01:00:00'),
(107, 'Kia Sonet', 'Cars', 0, 2750.00, 25.92, 1, 'public/Kia_Sonet.jpg', 'Crossover with stylish design and good performance.', '2025-11-17 01:00:00'),
(108, 'Volkswagen Polo', 'Cars', 0, 2750.00, 25.92, 1, 'public/Volkswagen_Polo.jpg', 'Small hatchback with European build and solid handling.', '2025-11-17 01:00:00'),
(109, 'Skoda Slavia', 'Cars', 0, 2750.00, 25.92, 1, 'public/Skoda_Slavia.jpg', 'Stylish mid-size sedan with modern dynamics.', '2025-11-17 01:00:00'),
(110, 'Volkswagen Virtus', 'Cars', 0, 2750.00, 25.92, 1, 'public/Volkswagen_Virtus.jpg', 'Luxury feel with dependability and smooth ride.', '2025-11-17 01:00:00'),
(111, 'Mercedes V Class', 'Cars', 1, 3250.00, 28.80, 1, 'public/Mercedes_V_Class.jpg', 'Premium MPV for executive travel and group comfort.', '2025-11-17 01:00:00'),
(114, 'Tesla Model 3', 'Cars', 1, 3250.00, 28.80, 1, 'public/Tesla_Model_3.jpg', 'High-performance electric sedan with cutting-edge tech.', '2025-11-17 01:00:00'),
(115, 'Tesla Model Y', 'Cars', 1, 3250.00, 28.80, 1, 'public/Tesla_Model_Y.jpg', 'Electric compact SUV, premium quality and long range.', '2025-11-17 01:00:00'),
(116, 'Mahindra XUV700', 'Cars', 0, 2750.00, 25.92, 1, 'public/Mahindra_XUV700.jpg', 'Modern powerful SUV with advanced features.', '2025-11-17 01:00:00'),
(117, 'Tata Harrier', 'Cars', 0, 2250.00, 22.08, 1, 'public/Tata_Harrier.jpg', 'Stylish SUV with bold design and reliable power.', '2025-11-17 01:00:00'),
(118, 'Honda Amaze', 'Cars', 0, 2750.00, 25.92, 1, 'public/Honda_Amaze.jpg', 'Compact sedan, efficient for city and short trips.', '2025-11-17 01:00:00'),
(119, 'Skoda Superb', 'Cars', 0, 2750.00, 25.92, 1, 'public/Skoda_Superb.jpg', 'Premium large sedan with lots of space and luxury features.', '2025-11-17 01:00:00'),
(120, 'Jeep Compass', 'Cars', 0, 2750.00, 25.92, 1, 'public/Jeep_Compass.jpg', 'SUV with rugged looks and refined performance.', '2025-11-17 01:00:00'),
(121, 'Maruti Vitara Brezza', 'Cars', 0, 2250.00, 22.08, 1, 'public/Maruti_Vitara_Brezza.jpg', 'Popular compact SUV with good fuel economy.', '2025-11-17 01:00:00'),
(122, 'Ford EcoSport', 'Cars', 0, 2750.00, 25.92, 1, 'public/Ford_EcoSport.jpg', 'Small SUV ideal for city use and occasional drives.', '2025-11-17 01:00:00'),
(123, 'Tata Tigor EV', 'Cars', 0, 2250.00, 22.08, 1, 'public/Tata_Tigor_EV.jpg', 'Compact electric sedan for city rides.', '2025-11-17 01:00:00'),
(124, 'MG Comet EV', 'Cars', 0, 2750.00, 25.92, 1, 'public/MG_Comet_EV.jpg', 'Small EV, perfect for short urban trips.', '2025-11-17 01:00:00'),
(125, 'Mahindra Bolero', 'Cars', 0, 2750.00, 25.92, 1, 'public/Mahindra_Bolero.jpg', 'Rugged utility SUV, good for rougher roads.', '2025-11-17 01:00:00'),
(126, 'Tata Safari', 'Cars', 0, 2250.00, 22.08, 1, 'public/Tata_Safari.jpg', 'Spacious SUV with powerful engine and comfortable ride.', '2025-11-17 01:00:00'),
(128, 'Skoda Kodiaq', 'Cars', 0, 2750.00, 25.92, 1, 'public/Skoda_Kodiaq.jpg', 'Large 7-seater SUV with European build and refinement.', '2025-11-17 01:00:00'),
(129, 'Honda CR-V', 'Cars', 0, 2750.00, 25.92, 1, 'public/Honda_CR-V.jpg', 'Comfortable SUV with reliable engine and safety features.', '2025-11-17 01:00:00');



ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);



ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);



ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_premium` (`is_premium`);



ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`vehicle_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `idx_user` (`user_id`);




ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;



ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;



ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;



ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;



ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;


ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;
COMMIT;
