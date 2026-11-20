-- MySQL dump 10.13  Distrib 5.7.39, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: cinema_db
-- ------------------------------------------------------
-- Server version	5.7.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Драмы','drama'),(2,'Комедии','comedy'),(3,'Ужасы','horror'),(4,'Боевики','action'),(5,'Фэнтези','fantasy'),(6,'Триллеры','thriller');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('movie','series') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `poster` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `backdrop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trailer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT '0.0',
  `duration` int(11) DEFAULT '0',
  `category_id` int(11) DEFAULT NULL,
  `is_new` tinyint(1) DEFAULT '0',
  `is_popular` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `content_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content`
--

LOCK TABLES `content` WRITE;
/*!40000 ALTER TABLE `content` DISABLE KEYS */;
INSERT INTO `content` VALUES (1,'Довод','movie','Оперативник без имени осваивает инверсию времени, чтобы предотвратить войну будущего с настоящим.','assets/img/posters/tenet.jpg','assets/img/posters/tenet.jpg','https://rutube.ru/play/embed/6f9f1cac4310f08af84dafb08774bc69',2020,7.4,150,6,1,1,'2026-10-23 07:35:49'),(2,'Оппенгеймер','movie','История создателя атомной бомбы: наука, власть и цена изобретения, изменившего мир.','assets/img/posters/oppenheimer.jpg','assets/img/posters/oppenheimer.jpg','https://rutube.ru/play/embed/4cd542b00631ada0bfab82a40bfc3eb1',2023,8.6,180,1,1,1,'2026-10-23 07:35:49'),(3,'Барби','movie','Барби покидает идеальный Барбиленд и открывает реальный мир, где всё сложнее и интереснее.','assets/img/posters/barbie.jpg','assets/img/posters/barbie.jpg','https://rutube.ru/play/embed/948618ab3231d9333f8396d37f27601b',2023,7.0,114,2,0,1,'2026-10-23 07:35:49'),(4,'Сайлент Хилл','movie','Женщина ищет пропавшую дочь в городе, который скрыт туманом и собственными кошмарами.','assets/img/posters/silent-hill.jpg','assets/img/posters/silent-hill.jpg','https://rutube.ru/play/embed/3ce027f892293ead75ace0b3041f2f3f',2006,6.6,125,3,1,0,'2026-10-23 07:35:49'),(5,'Дом Дракона','series','За двести лет до «Игры престолов» дом Таргариенов борется за трон — и раскалывается изнутри.','assets/img/posters/house-dragon.jpg','assets/img/posters/house-dragon.jpg','https://rutube.ru/play/embed/acc893f460b1711c0f27dc1c076040f9',2022,8.4,0,5,1,1,'2026-10-23 07:35:49'),(6,'Мора','movie','Фолк-хоррор о деревне, где древние обряды будят то, что лучше не тревожить.','assets/img/posters/mora.jpg','assets/img/posters/mora.jpg','https://rutube.ru/play/embed/d2181c0210d9f84a80c05d5065f3f1c7',2025,6.4,100,3,0,1,'2026-10-23 07:35:49'),(7,'Дракула','series','Английский юрист приезжает в замок графа — и оказывается в центре истории о бессмертии и крови.','assets/img/posters/dracula.jpg','assets/img/posters/dracula.jpg','https://rutube.ru/play/embed/07ee0bf06d2b08953adc30f93aff1f01',2020,7.2,0,3,1,0,'2026-10-23 07:35:49'),(8,'Джентльмены','movie','Американский босс марихуановой империи решает выйти из бизнеса — и запускает цепь нелепых и опасных событий.','assets/img/posters/gentlemen.jpg','assets/img/posters/gentlemen.jpg','https://rutube.ru/play/embed/c429b3eb60d2d22dca7865914beb939f',2019,7.8,113,2,0,1,'2026-10-23 07:35:49'),(9,'Дюна: Часть вторая','movie','Пол Атрейдес объединяется с фременами, чтобы отомстить за семью и изменить судьбу Арракиса.','assets/img/posters/dune2.jpg','assets/img/posters/dune2.jpg','https://rutube.ru/play/embed/16e135ea08b4419d1ce0ba33ccc011cb',2024,8.5,166,5,1,1,'2026-10-23 07:35:49'),(10,'Человек-паук: Нет пути домой','movie','Питер Паркер просит доктора Стрэнджа стереть память о себе — и открывает двери в мультивселенную.','assets/img/posters/spiderman.jpg','assets/img/posters/spiderman.jpg','https://rutube.ru/play/embed/53c7e32f5262e18dd4c45e7e7636bce0',2021,8.2,148,4,1,1,'2026-10-23 07:35:49'),(11,'Майнкрафт: Фильм','movie','Четверо друзей попадают в кубический мир, где всё строится и разрушается, а дом надо защищать.','assets/img/posters/minecraft.jpg','assets/img/posters/minecraft.jpg','https://rutube.ru/play/embed/2757f46c8110daf449a9bc75b3de594f',2025,6.2,101,2,0,1,'2026-10-23 07:35:49'),(12,'Годзилла против Конга','movie','Два титана сталкиваются, пока люди пытаются понять, кто на самом деле угрожает Земле.','assets/img/posters/godzilla-kong.jpg','assets/img/posters/godzilla-kong.jpg','https://rutube.ru/play/embed/19cfd93f88c78e07b87379861b092369',2021,6.4,113,4,0,1,'2026-10-23 07:35:49'),(13,'Мир Юрского периода: Господство','movie','Динозавры живут рядом с людьми, а новая угроза и борьба за будущее планеты снова сводят героев вместе.','assets/img/posters/jurasicworld.jpg','assets/img/posters/jurasicworld.jpg','https://rutube.ru/play/embed/0bb0a9474e078558ba405d5855eed826',2022,5.6,147,5,1,0,'2026-10-23 07:35:49'),(14,'Аватар: Путь воды','movie','Джейк Салли и его семья уходят к океану, чтобы найти убежище — и снова вступают в войну.','assets/img/posters/avatar2.jpg','assets/img/posters/avatar2.jpg','https://rutube.ru/play/embed/ad47cc4cb648b7afa6ce39284152e525',2022,7.6,192,5,0,1,'2026-10-23 07:35:49');
/*!40000 ALTER TABLE `content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content_people`
--

DROP TABLE IF EXISTS `content_people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content_people` (
  `content_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`content_id`,`person_id`),
  KEY `person_id` (`person_id`),
  CONSTRAINT `content_people_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE,
  CONSTRAINT `content_people_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content_people`
--

LOCK TABLES `content_people` WRITE;
/*!40000 ALTER TABLE `content_people` DISABLE KEYS */;
INSERT INTO `content_people` VALUES (1,1),(5,1),(1,2),(5,2),(2,3),(6,3),(2,4),(6,4),(3,5),(10,5),(3,6),(1,7),(6,7),(2,8),(5,8),(3,9),(10,10),(11,11),(11,12);
/*!40000 ALTER TABLE `content_people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `episodes`
--

DROP TABLE IF EXISTS `episodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `episodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `season` int(11) NOT NULL,
  `episode` int(11) NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  CONSTRAINT `episodes_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `episodes`
--

LOCK TABLES `episodes` WRITE;
/*!40000 ALTER TABLE `episodes` DISABLE KEYS */;
INSERT INTO `episodes` VALUES (20,5,1,1,'Наследники дракона',66),(21,5,1,2,'Принц-изгой',59),(22,5,1,3,'Второй по рождению',63),(23,5,1,4,'Король Узкого моря',60),(24,5,2,1,'Сын за сына',58),(25,5,2,2,'Красный дракон и золотой',62),(26,7,1,1,'Правила зверя',90),(27,7,1,2,'Кровавый корабль',90),(28,7,1,3,'Тёмный компас',90);
/*!40000 ALTER TABLE `episodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favorites` (
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`content_id`),
  KEY `content_id` (`content_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
INSERT INTO `favorites` VALUES (2,5),(3,5),(3,10);
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `watched_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_content` (`user_id`,`content_id`),
  KEY `user_id` (`user_id`),
  KEY `content_id` (`content_id`),
  CONSTRAINT `history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `history_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history`
--

LOCK TABLES `history` WRITE;
/*!40000 ALTER TABLE `history` DISABLE KEYS */;
INSERT INTO `history` VALUES (3,3,5,'2026-11-20 11:22:50'),(4,3,1,'2026-11-20 11:22:50'),(5,2,10,'2026-11-20 11:22:50'),(6,3,10,'2026-11-20 11:22:50');
/*!40000 ALTER TABLE `history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','canceled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_ref` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'test_gateway',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_ref` (`payment_ref`),
  KEY `user_id` (`user_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('actor','director') COLLATE utf8mb4_unicode_ci DEFAULT 'actor',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `people`
--

LOCK TABLES `people` WRITE;
/*!40000 ALTER TABLE `people` DISABLE KEYS */;
INSERT INTO `people` VALUES (1,'Иван Петров','actor','assets/img/no-avatar.svg'),(2,'Анна Смирнова','actor','assets/img/no-avatar.svg'),(3,'Михаил Орлов','actor','assets/img/no-avatar.svg'),(4,'Екатерина Волкова','actor','assets/img/no-avatar.svg'),(5,'Дмитрий Соколов','actor','assets/img/no-avatar.svg'),(6,'Ольга Кузнецова','actor','assets/img/no-avatar.svg'),(7,'Сергей Морозов','director','assets/img/no-avatar.svg'),(8,'Алексей Новиков','director','assets/img/no-avatar.svg'),(9,'Мария Лебедева','director','assets/img/no-avatar.svg'),(10,'Павел Зайцев','actor','assets/img/no-avatar.svg'),(11,'Ирина Павлова','actor','assets/img/no-avatar.svg'),(12,'Артём Козлов','actor','assets/img/no-avatar.svg');
/*!40000 ALTER TABLE `people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL DEFAULT '30',
  `features` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (1,'basic','Базовый',299.00,30,'[\"Качество HD 720p\",\"Один экран\",\"Полный каталог фильмов и сериалов\",\"Без рекламы\"]',0,1,1),(2,'standard','Стандарт',499.00,30,'[\"Качество Full HD 1080p\",\"Два экрана одновременно\",\"Скачивание для просмотра офлайн\",\"Все категории и подборки\",\"Без рекламы\"]',1,1,2),(3,'premium','Премиум',799.00,30,'[\"Качество 4K Ultra HD\",\"Четыре экрана одновременно\",\"Ранний доступ к новинкам\",\"Скачивание без ограничений\",\"Без рекламы\",\"Приоритетная поддержка\"]',0,1,3),(4,'year','Год без границ',4990.00,365,'[\"Всё из тарифа «Премиум»\",\"Оплата один раз в год\",\"Экономия 46 %\",\"4K Ultra HD и четыре экрана\"]',0,1,4);
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `content_id` (`content_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,2,5,8,'Второй сезон держит планку, хотя первые серии медленные.','2026-11-20 11:22:50'),(2,3,5,8,'Второй сезон чуть слабее первого, но всё равно держит.','2026-11-20 11:22:50'),(3,2,10,9,'Мультивселенная получилась эмоциональной, а не просто фансервисом.','2026-11-20 11:22:50'),(4,3,1,7,'Сложно, но затягивает: второй просмотр раскрывает детали.','2026-11-20 11:22:50');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','subscriber','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `subscription_until` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@cinemax.ru','$2y$10$Kcc0kL5H0DPXDBngBYkcHOeSiLDs72y5Gkg3CPemtWbJRxFPLW8yG','admin',NULL,'2026-10-23 07:35:49'),(2,'anna_s','anna@example.com','$2y$10$H/Hu/wEWMXg/lYS9VoAR6OuQDfNaRr4fqb6Uwc/.TQ8vGOibr8DUO','subscriber','2026-12-15 14:22:50','2026-11-20 11:22:50'),(3,'dmitry_k','dmitry@example.com','$2y$10$R5RGx1CQsWKdeg9INUt5Gu6pioFgypPyvT.kGPIDDNqz4QOHQYGd2','user',NULL,'2026-11-20 11:22:50');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-20 16:54:54
