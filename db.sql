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
INSERT INTO `content` VALUES (1,'Тёмный горизонт','movie','Бывший агент вынужден вернуться в игру, чтобы остановить международную сеть наёмников.','assets/img/posters/poster-1.svg','assets/img/backdrops/backdrop-1.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2025,8.4,128,4,1,1,'2026-10-23 07:35:49'),(2,'Последний рассвет','movie','История двух братьев, чьи пути разошлись на десятилетия, но судьба свела их вновь.','assets/img/posters/poster-2.svg','assets/img/backdrops/backdrop-2.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2024,7.9,112,1,1,1,'2026-10-23 07:35:49'),(3,'Смех сквозь слёзы','movie','Стендап-комик теряет голос за день до главного выступления в жизни.','assets/img/posters/poster-3.svg','assets/img/backdrops/backdrop-3.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2024,7.2,98,2,0,1,'2026-10-23 07:35:49'),(4,'Красная комната','movie','Пятеро незнакомцев просыпаются в комнате без дверей. У каждого есть лишь один шанс выйти.','assets/img/posters/poster-4.svg','assets/img/backdrops/backdrop-4.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2025,7.5,105,3,1,0,'2026-10-23 07:35:49'),(5,'Хроники Севера','series','Молодая волшебница отправляется на поиски древнего артефакта, способного изменить ход времён.','assets/img/posters/poster-5.svg','assets/img/backdrops/backdrop-5.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2025,9.1,0,5,1,1,'2026-10-23 07:35:49'),(6,'Ночной патруль','series','Детектив с бессонницей расследует серию убийств, связанных с его собственным прошлым.','assets/img/posters/poster-6.svg','assets/img/backdrops/backdrop-6.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2024,8.6,0,6,0,1,'2026-10-23 07:35:49'),(7,'Городские легенды','series','Каждый эпизод — новая городская легенда, оживающая в наши дни.','assets/img/posters/poster-7.svg','assets/img/backdrops/backdrop-7.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2025,7.8,0,3,1,0,'2026-10-23 07:35:49'),(8,'Аляска','movie','Врач из мегаполиса переезжает в крошечный городок на краю света и находит там себя.','assets/img/posters/poster-8.svg','assets/img/backdrops/backdrop-8.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2023,8.0,124,1,0,1,'2026-10-23 07:35:49'),(9,'Небо над нами','movie','Девушка находит в старом доме карту, ведущую в параллельный мир.','assets/img/posters/poster-9.svg','assets/img/backdrops/backdrop-9.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2024,8.2,117,5,1,1,'2026-10-23 07:35:49'),(10,'Код 404','series','Хакер случайно стирает собственное имя из всех баз данных мира — и начинает исчезать.','assets/img/posters/poster-10.svg','assets/img/backdrops/backdrop-10.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2025,8.9,0,6,1,1,'2026-10-23 07:35:49'),(11,'Бар Улыбка','series','Ситком о друзьях, которые держат бар на окраине города.','assets/img/posters/poster-11.svg','assets/img/backdrops/backdrop-11.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2024,7.4,0,2,0,1,'2026-10-23 07:35:49'),(12,'Стальной кулак','movie','Легендарный боец MMA возвращается на ринг ради дочери.','assets/img/posters/poster-12.svg','assets/img/backdrops/backdrop-12.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2023,7.7,131,4,0,1,'2026-10-23 07:35:49'),(13,'Тихий океан','series','Семейная сага о рыбаках на острове, который медленно уходит под воду.','assets/img/posters/poster-13.svg','assets/img/backdrops/backdrop-13.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2025,8.7,0,1,1,0,'2026-10-23 07:35:49'),(14,'Охота на тень','movie','Профайлер ФБР ищет маньяка, который всегда на шаг впереди.','assets/img/posters/poster-14.svg','assets/img/backdrops/backdrop-14.svg','https://www.youtube.com/embed/dQw4w9WgXcQ',2024,8.1,119,6,0,1,'2026-10-23 07:35:49');
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `episodes`
--

LOCK TABLES `episodes` WRITE;
/*!40000 ALTER TABLE `episodes` DISABLE KEYS */;
INSERT INTO `episodes` VALUES (1,5,1,1,'Пророчество',52),(2,5,1,2,'Лес тысячи теней',48),(3,5,1,3,'Ледяной трон',50),(4,5,2,1,'Новая кровь',54),(5,5,2,2,'Крик ворона',49),(6,6,1,1,'Первая жертва',45),(7,6,1,2,'Свидетель',47),(8,6,1,3,'Бессонница',44),(9,7,1,1,'Человек с крюком',38),(10,7,1,2,'Зеркало',41),(11,7,1,3,'Красная машина',40),(12,10,1,1,'Стирание',50),(13,10,1,2,'Призрак в сети',48),(14,10,1,3,'Код 404',52),(15,11,1,1,'Открытие',24),(16,11,1,2,'Первый гость',23),(17,11,1,3,'Караоке',25),(18,13,1,1,'Прилив',55),(19,13,1,2,'Отлив',53);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,3,2,499.00,'paid','PAY-20261120-e9bd3d4ed0c8','test_gateway','2026-11-20 11:27:26','2026-11-20 14:27:26');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,2,5,9,'Отличный сериал: атмосфера, музыка, финал. Смотрится на одном дыхании.','2026-11-20 11:22:50'),(2,3,5,8,'Второй сезон чуть слабее первого, но всё равно держит.','2026-11-20 11:22:50'),(3,2,10,9,'Редкий случай, когда хакерские сцены не вызывают смеха.','2026-11-20 11:22:50'),(4,3,1,7,'Крепкий боевик на вечер, сюжет простой, но динамичный.','2026-11-20 11:22:50');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@cinemax.ru','$2y$10$Kcc0kL5H0DPXDBngBYkcHOeSiLDs72y5Gkg3CPemtWbJRxFPLW8yG','admin',NULL,'2026-10-23 07:35:49'),(2,'anna_s','anna@example.com','$2y$10$H/Hu/wEWMXg/lYS9VoAR6OuQDfNaRr4fqb6Uwc/.TQ8vGOibr8DUO','subscriber','2026-12-15 14:22:50','2026-11-20 11:22:50'),(3,'dmitry_k','dmitry@example.com','$2y$10$R5RGx1CQsWKdeg9INUt5Gu6pioFgypPyvT.kGPIDDNqz4QOHQYGd2','subscriber','2026-12-20 14:27:26','2026-11-20 11:22:50'),(4,'test_3812','t9633@mail.ru','$2y$10$3k3VT9sGkmuC1GxBRD1oAe5GP1mBu.DBKA2MOiDx0EgINZl1AG3qC','user',NULL,'2026-11-20 11:24:29');
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

-- Dump completed on 2026-11-20 16:32:54
