-- =======================================================
-- Database: hidden_places_explorer
-- Web Application: Hidden Places Explorer
-- Description: Complete schema, relationships, and rich seed data
-- =======================================================

CREATE DATABASE IF NOT EXISTS `hidden_places_explorer` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hidden_places_explorer`;

-- Drop existing tables to allow clean recreation
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `favourites`;
DROP TABLE IF EXISTS `destinations`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `contacts`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admins`;
SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------
-- Table 1: admins
-- -------------------------------------------------------
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) DEFAULT 'Super Admin',
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table 2: users
-- -------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default_avatar.png',
  `bio` TEXT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table 3: categories
-- -------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `icon` VARCHAR(50) DEFAULT 'compass',
  `image_url` VARCHAR(500) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table 4: destinations
-- -------------------------------------------------------
CREATE TABLE `destinations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `tagline` VARCHAR(255) NOT NULL,
  `description` LONGTEXT NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `country` VARCHAR(100) NOT NULL,
  `difficulty` ENUM('Easy', 'Moderate', 'Challenging', 'Expert') DEFAULT 'Moderate',
  `best_season` VARCHAR(100) NOT NULL,
  `estimated_duration` VARCHAR(100) DEFAULT '1-2 Days',
  `image_url` VARCHAR(500) NOT NULL,
  `gallery` TEXT NULL,
  `map_embed_url` TEXT NOT NULL,
  `map_link` VARCHAR(500) NOT NULL,
  `latitude` DECIMAL(10, 7) NULL,
  `longitude` DECIMAL(10, 7) NULL,
  `travel_tips` TEXT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `views_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_destination_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table 5: favourites
-- -------------------------------------------------------
CREATE TABLE `favourites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `destination_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_destination` (`user_id`, `destination_id`),
  CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fav_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table 6: reviews
-- -------------------------------------------------------
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `destination_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `title` VARCHAR(150) NULL,
  `comment` TEXT NOT NULL,
  `status` ENUM('approved', 'pending') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_review_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Table 7: contacts
-- -------------------------------------------------------
CREATE TABLE `contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('unread', 'read', 'replied') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- SEED DATA
-- -------------------------------------------------------

-- Admins (Default password: Admin@12345)
INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Lead Explorer Admin', 'admin@hiddenplaces.com', '$2y$10$54hsEygDenRmyS1W7SqdJug3tJW.1ssb99JCD1QwXYTaMr/bPhyjy', 'Super Admin');

-- Users (Default password: User@12345)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `avatar`, `bio`, `status`) VALUES
(1, 'Elena Rostova', 'elena@example.com', '$2y$10$cLI4/dnPd.bHPv.RMTKAKO9I/VRxzSQUZLJlMVdbSKcCZJ3nhFdse', 'default_avatar.png', 'Wilderness trekker, aerial photographer, and avid hidden valley enthusiast.', 'active'),
(2, 'Marcus Vance', 'marcus@example.com', '$2y$10$cLI4/dnPd.bHPv.RMTKAKO9I/VRxzSQUZLJlMVdbSKcCZJ3nhFdse', 'default_avatar.png', 'Speleologist and remote geography explorer documenting forgotten caves.', 'active'),
(3, 'Aria Chen', 'aria@example.com', '$2y$10$cLI4/dnPd.bHPv.RMTKAKO9I/VRxzSQUZLJlMVdbSKcCZJ3nhFdse', 'default_avatar.png', 'Solitary hiker seeking quiet coastlines and misty highland ridges.', 'active'),
(4, 'David Thorne', 'david@example.com', '$2y$10$cLI4/dnPd.bHPv.RMTKAKO9I/VRxzSQUZLJlMVdbSKcCZJ3nhFdse', 'default_avatar.png', 'Archaeology researcher and ancient architectural ruins documentarian.', 'active');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `image_url`) VALUES
(1, 'Secret Waterfalls & Cascades', 'secret-waterfalls', 'Hidden multi-tiered emerald pools, secluded canyon cascades, and roaring cliff waterfalls tucked deep within primordial valleys.', 'fa-water', 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80'),
(2, 'Hidden Caves & Underground', 'hidden-caves', 'Subterranean crystal chambers, prehistoric cavern networks, glowworm grottos, and majestic sunken cenotes.', 'fa-mountain', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80'),
(3, 'Mystical Forests & Peaks', 'mystical-forests', 'Fog-shrouded ancient canopies, dragon blood groves, moss-covered woodland sanctums, and towering limestone spires.', 'fa-tree', 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80'),
(4, 'Secluded Coasts & Fjords', 'secluded-coasts', 'Remote cliffside fishing hamlets, wild Atlantic sea arches, untouched turquoise coves, and mystical fjord passages.', 'fa-compass', 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=800&q=80'),
(5, 'Ancient Ruins & Lost Sanctuaries', 'ancient-ruins', 'Overgrown temple ruins, silent desert fortress walls, prehistoric megaliths, and forgotten mountain citadel relics.', 'fa-landmark', 'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=800&q=80'),
(6, 'Forgotten Canyons & Oases', 'forgotten-canyons', 'Sculpted sandstone labyrinths, turquoise desert water pools, subterranean salt cathedrals, and otherworldly rock formations.', 'fa-sun', 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=800&q=80');

-- Destinations
INSERT INTO `destinations` (`id`, `category_id`, `title`, `slug`, `tagline`, `description`, `location`, `country`, `difficulty`, `best_season`, `estimated_duration`, `image_url`, `gallery`, `map_embed_url`, `map_link`, `latitude`, `longitude`, `travel_tips`, `is_featured`, `views_count`) VALUES
(
  1,
  3,
  'Dragon\'s Blood Forest of Socotra',
  'dragons-blood-forest-socotra',
  'An alien plateau of umbrella-shaped prehistoric trees rising over Arabian waters',
  'Socotra is often termed the most alien-looking place on Earth. Isolated for over 18 million years, a third of its plant life is found nowhere else on the planet. The crowning jewel of the island is the Dixam Plateau, where thousands of dense Dracaena cinnabari (Dragon\'s Blood Trees) sprout like colossal flying saucers. The crimson resin exuded by their bark has been prized since antiquity for medicinal, artistic, and ritual purposes. Hiking across the limestone cliffs as evening mist rolls in from the Indian Ocean delivers an awe-inspiring sensation of stepping into Earth\'s prehistoric past.',
  'Dixam Plateau, Socotra Island',
  'Yemen',
  'Challenging',
  'October to April',
  '3-4 Days',
  'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=1200&q=80',
  '["https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=800&q=80"]',
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d497491.5647573033!2d53.60624936357413!3d12.483981881729048!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e01869e5d48297d%3A0x67399f939e6022e0!2sSocotra!5e0!3m2!1sen!2sus!4v1700000000000!5m2!1sen!2sus',
  'https://maps.google.com/?q=Socotra+Island+Yemen',
  12.4634200,
  53.8237300,
  'Arrange certified local bedouin guides well in advance. Carry solar power banks and hydration bladders since infrastructure on the plateau is completely wild and off-grid. Respect indigenous flora and leave no trace.',
  1,
  1420
),
(
  2,
  2,
  'Melissani Sunken Cave Lake',
  'melissani-sunken-cave-lake',
  'A neon-blue subterranean lagoon hidden beneath a collapsed limestone vault',
  'Tucked away near Sami on the Greek island of Cephalonia lies Melissani Cave, known in classical mythology as the Grotto of the Nymphs. Centuries ago, a severe earthquake caused the ceiling of the cavern to collapse, opening a massive vertical skylight directly into the heart of an underground lake. When the midday Mediterranean sun strikes directly overhead, the brackish water glows with an otherworldly, hyper-saturated electric blue, making traditional wooden rowboats appear as though they are floating weightlessly in midair.',
  'Karavomylos, Sami, Cephalonia',
  'Greece',
  'Easy',
  'May to October',
  'Half Day',
  'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1200&q=80',
  '["https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80"]',
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12530.147289523282!2d20.613388755419922!3d38.25686030000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x135dce39c4c1a59f%3A0xe54124376c9ad847!2sMelissani%20Cave!5e0!3m2!1sen!2sus!4v1700000000001!5m2!1sen!2sus',
  'https://maps.google.com/?q=Melissani+Cave+Cephalonia+Greece',
  38.2575200,
  20.6234100,
  'Visit exactly between 11:30 AM and 1:30 PM on sunny days to experience the full vertical solar light shaft illuminating the lake bottom. Take the small wooden rowboats rowed by local boatmen.',
  1,
  1890
),
(
  3,
  1,
  'Havasu Canyon Secret Turquoise Falls',
  'havasu-canyon-secret-turquoise-falls',
  'Vibrant mineral-rich waters cascading over red sandstone amphitheaters',
  'Deep inside the remote Havasupai Indian Reservation, within a hidden tributary of the Grand Canyon, flows Havasu Creek. The water is saturated with high concentrations of calcium carbonate and magnesium, giving the plunge pools a mesmerizing, surreal Caribbean turquoise hue that contrasts brilliantly against fiery red sandstone cliffs. Cascading down over 100 vertical feet, Havasu Falls and its surrounding Mooney Falls create a paradisiacal oasis accessible only to those willing to hike 10 grueling desert miles into the canyon.',
  'Supai, Havasupai Reservation, Arizona',
  'United States',
  'Challenging',
  'March to May, September to November',
  '3 Days',
  'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1200&q=80',
  '["https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80"]',
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d25838.358249842186!2d-112.71536302488737!3d36.25528090748111!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80cd807ca2fca6eb%3A0xbcf04616bb1cf226!2sHavasu%20Falls!5e0!3m2!1sen!2sus!4v1700000000002!5m2!1sen!2sus',
  'https://maps.google.com/?q=Havasu+Falls+Arizona',
  36.2552800,
  -112.6979100,
  'Tribal camping permits are strictly required and sell out within minutes every February. Never attempt the canyon descent during summer peak heat without 4+ liters of water. Sturdy water shoes with traction are essential.',
  1,
  2310
),
(
  4,
  4,
  'Gásadalur and Mulafossur Waterfall',
  'gasadalur-mulafossur-waterfall',
  'A fairy-tale Atlantic cliff cascade crashing directly into tempestuous northern seas',
  'For generations, the tiny village of Gásadalur on Vágar Island was one of the most isolated settlements in Europe, reachable only via a strenuous mountain pass climb or helicopter. Just beside this clutch of grass-roofed Scandinavian cottages, the glacial stream of Mulafossur plunges dramatically over basalt cliffs directly into the pounding waves of the North Atlantic Ocean. Framed by emerald green slopes and jagged sea stacks, it stands as one of the most sublime coastal views on the planet.',
  'Gásadalur, Vágar Island',
  'Faroe Islands',
  'Moderate',
  'May to September',
  '1 Day',
  'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=80',
  '["https://images.unsplash.com/photo-1517411032315-54ef2cb783bb?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80"]',
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14197.803874605996!2d-7.447543419842528!3d62.10986111166432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x48bc15510f27471d%3A0xe7585090f77243c!2sM%C3%BAlafossur%20Waterfall!5e0!3m2!1sen!2sus!4v1700000000003!5m2!1sen!2sus',
  'https://maps.google.com/?q=Mulafossur+Waterfall+Faroe+Islands',
  62.1105100,
  -7.4367200,
  'Dress in windproof and waterproof layers; weather changes in minutes. The old postal hiking trail over the mountain offers supreme panoramic vistas if you enjoy trekking beyond the standard road viewpoint.',
  1,
  1640
),
(
  5,
  2,
  'Salina Turda Underground Cathedral',
  'salina-turda-underground-cathedral',
  'A futuristic sci-fi realm constructed 400 feet beneath an ancient Transylvanian salt mine',
  'First mined during the Roman Empire, Salina Turda in Romania has been transformed into an underground subterranean marvel. Located hundreds of meters beneath the earth, visitors ride glass elevators into cavernous salt halls illuminated by halo lamps. Down inside the Rudolf and Terezia mines, you will find an amphitheater, a subterranean bowling alley, minigolf, and even a natural underground salt lake where you can rent paddleboats in silence beneath millions of tons of salt formations.',
  'Turda, Cluj County',
  'Romania',
  'Easy',
  'All Year (Year-round subterranean 11°C)',
  '1 Day',
  'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=1200&q=80',
  '["https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80"]',
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d11019.29953842345!2d23.78018314541992!3d46.58784110000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4749666878bdf1b3%3A0xc48adba8f22fa13b!2sSalina%20Turda!5e0!3m2!1sen!2sus!4v1700000000004!5m2!1sen!2sus',
  'https://maps.google.com/?q=Salina+Turda+Romania',
  46.5878400,
  23.7872100,
  'The mine stays at a constant 11-12°C with high humidity year-round, so always bring a warm fleece jacket even in summer. The saline microclimate has documented therapeutic respiratory benefits.',
  0,
  980
),
(
  6,
  6,
  'The Wave of Vermilion Cliffs',
  'the-wave-vermilion-cliffs',
  'Hypnotic undulating sandstone ribbons frozen in Jurassic red rock dunes',
  'Located in the Paria Canyon-Vermilion Cliffs Wilderness on the Arizona-Utah border, The Wave is a geological phenomenon formed by millions of years of wind and flash flood erosion. The swirling bands of orange, carmine, pink, and gold sandstone create optical illusions that twist depth and perspective. Because the fragile Navajo sandstone structures can easily erode under heavy foot traffic, the BLM limits access to only 64 visitors per day via a fiercely contested lottery system.',
  'Coyote Buttes North, Kanab Wilderness',
  'United States',
  'Challenging',
  'October to April',
  '1 Day',
  'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=1200&q=80',
  '["https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=800&q=80"]',
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12821.571477431766!2d-112.01509831984253!3d36.99612081166432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80caae2dc6293fd9%3A0x6b13ec85a21ff7c4!2sThe%20Wave!5e0!3m2!1sen!2sus!4v1700000000005!5m2!1sen!2sus',
  'https://maps.google.com/?q=The+Wave+Coyote+Buttes+Arizona',
  36.9961200,
  -112.0064100,
  'There is no marked trail. You must rely on BLM photo landmark orientation maps or offline GPS. Carry a minimum of 4 liters of water and prepare for extreme sun exposure without natural shade.',
  1,
  2150
),
(
  7,
  3,
  'Zhangjiajie Floating Avatar Pinnacles',
  'zhangjiajie-floating-avatar-pinnacles',
  'Three thousand sheer quartzite towers jutting through ocean clouds in Hunan',
  'Zhangjiajie National Forest Park is China\'s first UNESCO world heritage national forest. Over 3,000 quartzite sandstone pillars, many rising over 650 feet high, pierce through perpetual layers of dense highland mist. These towering pillars were the visual inspiration for the floating Hallelujah Mountains in James Cameron\'s film Avatar. Between the dizzying heights, lush subtropical ferns, and wild macaques roaming the misty canopy walkways, visiting this hidden valley feels like stepping onto an extraterrestrial biosphere.',
  'Wulingyuan Scenic Area, Zhangjiajie, Hunan',
  'China',
  'Moderate',
  'April to May, September to November',
  '2-3 Days',
  'https://images.unsplash.com/photo-1547981609-4b6bfe67ca0b?auto=format&fit=crop&w=1200&q=80',
  '["https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80"]',
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d11019.29953842345!2d110.5395!3d29.3524!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3699863dc366a683%3A0xd68037a346a0c0e5!2sZhangjiajie%20National%20Forest%20Park!5e0!3m2!1sen!2sus!4v1700000000006!5m2!1sen!2sus',
  'https://maps.google.com/?q=Zhangjiajie+National+Forest+Park+China',
  29.3524000,
  110.5482000,
  'Early mornings immediately after rain showers offer the most dramatic views of clouds floating below the pinnacle peaks. Stay in Wulingyuan village near the park gate to beat large coach tour crowds.',
  0,
  1750
),
(
  8,
  1,
  'Plitvice Upper Terraced Lakes & Falls',
  'plitvice-upper-terraced-lakes-and-falls',
  'Sixteen crystal cascade lakes interwoven by living mossy travertine barriers',
  'Deep in the forested Dinaric Alps of central Croatia, Plitvice Lakes is a living geological sanctuary. Sixteen cascaded lakes transition through vibrant shades of teal, azure, mint, and crystal grey depending on sunlight angle and mineral organisms. Natural dams formed by travertine moss and algae constantly evolve, causing waterfalls to shift positions over the centuries. Timber boardwalks snake directly over crystal-clear waters where schools of trout swim unperturbed below your feet.',
  'Plitvička Jezera National Park, Lika-Senj',
  'Croatia',
  'Easy',
  'May to June, September to October',
  '1-2 Days',
  'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
  '["https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=800&q=80"]',
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d11019.29953842345!2d15.5948!3d44.8654!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47615c441cb301cb%3A0xc34125b0fcf0088b!2sPlitvice%20Lakes%20National%20Park!5e0!3m2!1sen!2sus!4v1700000000007!5m2!1sen!2sus',
  'https://maps.google.com/?q=Plitvice+Lakes+National+Park+Croatia',
  44.8654000,
  15.6036000,
  'Book entrance tickets online days in advance since daily visitor quotas are strictly enforced. Enter at Gate 2 early at 7:00 AM to explore the quieter Upper Lakes before tour buses arrive.',
  1,
  1980
),
(
  9,
  2,
  'Skaftafell Crystal Ice Cave',
  'skaftafell-crystal-ice-cave',
  'A cathedral carved from compressed sapphire blue glacial ice beneath Vatnajökull',
  'Inside the massive Vatnajökull Glacier—the largest ice cap in Europe—seasonal meltwater carves out shimmering labyrinthine tunnels every autumn. As winter temperatures freeze the runoff, these conduits transform into cathedral-like sapphire tunnels. The extreme weight of centuries of compacted snow squeezes out all air bubbles, allowing the ice to absorb all red wavelengths of light and refract a pure, ethereal, radiant blue glow along ceiling curves.',
  'Skaftafell, Vatnajökull National Park',
  'Iceland',
  'Moderate',
  'November to March',
  'Half Day',
  'https://images.unsplash.com/photo-1517411032315-54ef2cb783bb?auto=format&fit=crop&w=1200&q=80',
  '["https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=800&q=80","https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80"]',
  'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d11019.29953842345!2d-16.975!3d64.016!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x48d03c3f59632eb5%3A0x6bfa3bb34015f8a0!2sSkaftafell!5e0!3m2!1sen!2sus!4v1700000000008!5m2!1sen!2sus',
  'https://maps.google.com/?q=Skaftafell+Vatnajokull+Iceland',
  64.0163000,
  -16.9662000,
  'Never attempt to enter glacial ice caves without a certified mountain guide equipped with helmets, crampons, and safety beacons. Caves evolve continuously each winter.',
  0,
  1340
);

-- Favourites (Sample relationships)
INSERT INTO `favourites` (`user_id`, `destination_id`) VALUES
(1, 1),
(1, 3),
(1, 4),
(2, 2),
(2, 5),
(3, 1),
(3, 8),
(4, 6),
(4, 7);

-- Reviews
INSERT INTO `reviews` (`destination_id`, `user_id`, `rating`, `title`, `comment`, `status`) VALUES
(1, 1, 5, 'Unbelievable otherworldly experience', 'Hiking under the Dragon Blood trees during twilight was easily the most sublime experience of my trekking life. The bedouin hospitality is unmatched. Bring good boots!', 'approved'),
(1, 2, 5, 'Like walking on another planet', 'Socotra defies imagination. Pictures do not do justice to the sheer scale of the Dixam Plateau. Truly a sacred hidden corner of our planet.', 'approved'),
(2, 2, 5, 'Electrifying blue waters', 'We arrived at exactly 12:15 PM and the sunlight column turned the entire cavern into pure liquid sapphire. The boat tour is brief but unforgettable.', 'approved'),
(2, 3, 4, 'Pure Mediterranean serenity', 'It gets a bit busy during midday peak, but watching the water glow from the cave tunnel is magical. Well worth the drive down from Sami.', 'approved'),
(3, 1, 5, 'The hardest 10-mile trek with the sweetest reward', 'The turquoise color of Havasu Creek is 100% real. Sitting beneath Mooney Falls after 5 hours in the desert heat is paradise found.', 'approved'),
(3, 4, 5, 'Spectacular canyon oasis', 'A sacred canyon landscape. Respect the indigenous Havasupai people and leave absolutely no waste behind. Best camping trip ever.', 'approved'),
(4, 3, 5, 'Poetry in landscape form', 'Mulafossur crashing straight into the wild northern sea gave me goosebumps. Fog rolled over the turf roofs as puffins flew overhead.', 'approved'),
(5, 4, 4, 'Bizarre sci-fi wonderland underground', 'Renting a wooden rowboat inside an underground salt mine cavern while looking up 100 meters at illuminated salt stalactites was surreal.', 'approved'),
(6, 1, 5, 'Winning the lottery was life changing', 'The Wave is fragile and delicate beyond words. Walking through the sandstone curves felt like walking through a Dali painting.', 'approved'),
(8, 3, 5, 'Mesmerizing living waterfalls', 'The wooden footpaths allow you to walk directly above the crystalline water. Arrive at dawn before the tour buses for total silence.', 'approved');

-- Contacts
INSERT INTO `contacts` (`name`, `email`, `subject`, `message`, `status`) VALUES
(
  'Julian Meyer',
  'julian@travelpulse.org',
  'Inquiry about Socotra Island expeditions',
  'Hello Hidden Places Explorer team, I am organizing an expedition for documentary filmmakers and would love advice on responsible local guiding partners for the Dixam Plateau. Thank you!',
  'unread'
),
(
  'Sophie Laurent',
  'sophie.l@naturetrek.fr',
  'Destination suggestion: Plougrescant Hidden Coast',
  'Greetings! I adore your platform and wanted to suggest the wild granite chasms of Castel Meur in Brittany, France. Would love to submit photos and details.',
  'read'
);
