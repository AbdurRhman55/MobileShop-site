-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: lala_mobiles
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (3,'admin','$2y$10$I2IePqgsoBYmQtMO.qhpyeteFaXN7vgQFk610msHyMtcLaj0O40H2','Store Admin','2026-10-21 15:17:32');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_posts`
--

DROP TABLE IF EXISTS `blog_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blog_posts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` mediumtext COLLATE utf8mb4_unicode_ci,
  `author_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_blog_slug` (`slug`),
  KEY `idx_blog_status` (`status`,`published_at`),
  KEY `idx_blog_updated` (`updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_posts`
--

LOCK TABLES `blog_posts` WRITE;
/*!40000 ALTER TABLE `blog_posts` DISABLE KEYS */;
INSERT INTO `blog_posts` VALUES (4,'best-phones-under-50000-pakistan-2026','Best Phones Under Rs 50,000 in Pakistan (2026 Guide)','Our pick of the best smartphones you can buy in Pakistan under Rs 50,000, with honest notes on where each one shines.','<p>Finding a capable smartphone under Rs 50,000 in Pakistan is easier than ever, but the spec sheets can be misleading. Here is what actually matters at this price: battery size, display quality and after-sales service.</p><h2>1. Xiaomi Redmi 14C — Best overall under Rs 40,000</h2><p>The Redmi 14C pairs a 6.88-inch 120Hz display with a 5160mAh battery. If your budget is tight, this is the one to beat.</p><h2>2. Tecno Camon 30 5G — Best camera under Rs 70,000</h2><p>When budgets stretch to Rs 70,000, the Camon 30’s 50MP selfie shooter and 120Hz AMOLED display pull ahead.</p><h2>What to check before buying</h2><p>Always confirm PTA approval, verify the IMEI before payment, and buy from a seller who offers a written warranty. At Lala Mobiles, every unit is tested and documented before it ships.</p>','Lala Mobiles Team',NULL,'published','2026-09-14 07:00:00','2026-10-21 15:17:34','2026-10-21 15:17:34'),(5,'refurbished-vs-used-phones-pakistan','Refurbished vs Used Phones in Pakistan: What’s the Real Difference?','Grades, battery health, warranties — a practical guide to buying refurbished or used phones in Pakistan without surprises.','<p>“Used” and “refurbished” get used interchangeably in Pakistani markets, but they describe very different products. Understanding the difference can save you thousands of rupees.</p><h2>What refurbished actually means</h2><p>A refurbished phone has had faulty parts replaced — typically the battery or display — and passed a full functional test. At Lala Mobiles, refurbished units carry a 3-month shop warranty and a minimum 85% battery health guarantee.</p><h2>Grades to expect</h2><p><strong>Grade A:</strong> light cosmetic wear, no functional faults. <strong>Grade B:</strong> visible scratches, fully functional. Anything with a cracked display or dead pixel should be priced (and labelled) much lower.</p><h2>Red flags when buying used</h2><p>Ask for the original box and PTA approval slip, check battery health in settings, and test Face ID or the fingerprint sensor in person. Never pay in full before checking the IMEI against the PTA database.</p>','Lala Mobiles Team',NULL,'published','2026-08-30 07:00:00','2026-10-21 15:17:34','2026-10-21 15:17:34'),(6,'iphone-pta-approval-guide-pakistan','iPhone PTA Approval in Pakistan (2026): Complete Guide','How PTA approval works for iPhones, how to check IMEI status, and what happens to unapproved phones.','<p>Every phone used on Pakistani networks must be PTA approved. Here is how the process works in 2026 and what it means when you buy an iPhone.</p><h2>Checking IMEI status</h2><p>Dial *#06# to find your IMEI, then check it on the PTA DIRBS portal before paying any advance. An approved device shows the approval status immediately.</p><h2>Costs and timelines</h2><p>PTA duty is calculated on the device value and varies by model. New phones sold through official channels have duty included in the price — always ask for the invoice.</p><h2>Unapproved phones</h2><p>Unapproved IMEIs are blocked from cellular networks after the grace period. Wi-Fi still works, but a phone without cellular service is not much of a phone. Every iPhone at Lala Mobiles is PTA approved before it reaches you.</p>','Lala Mobiles Team',NULL,'published','2026-08-05 07:00:00','2026-10-21 15:17:34','2026-10-21 15:17:34');
/*!40000 ALTER TABLE `blog_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intro` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','hidden') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_brand_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (21,'samsung','Samsung','https://www.samsung.com/pk/',NULL,'South Korean electronics giant, maker of the Galaxy series.','active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(22,'apple','Apple','https://www.apple.com/',NULL,'American maker of iPhone, iPad and Mac.','active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(23,'xiaomi','Xiaomi','https://www.mi.com/pk/',NULL,'Chinese brand known for value flagship phones.','active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(24,'infinix','Infinix','https://www.infinixmobile.com/',NULL,'Popular budget brand backed by Transsion.','active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(25,'tecno','Tecno','https://www.tecno-mobile.com/',NULL,'Transsion brand focused on emerging markets.','active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(26,'oppo','Oppo','https://www.oppo.com/pk/',NULL,'Chinese brand strong on camera phones.','active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(27,'vivo','Vivo','https://www.vivo.com/pk/',NULL,'Chinese brand with wide service network in Pakistan.','active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(28,'realme','Realme','https://www.realme.com/pk/',NULL,'Young brand under BBK with aggressive pricing.','active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(29,'anker','Anker','https://www.anker.com/',NULL,'Accessories brand known for power banks and chargers.','active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(30,'dodocool','Baseus','https://www.baseus.com/',NULL,'Accessories brand for cables, chargers and cases.','active','2026-10-21 15:17:32','2026-10-21 15:17:32');
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned DEFAULT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intro` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `status` enum('active','hidden') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_category_slug` (`slug`),
  KEY `idx_category_parent` (`parent_id`),
  CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (21,NULL,'smartphones','Smartphones','Explore new, used and refurbished smartphones from every major brand, backed by warranty and Cash on Delivery across Pakistan.',NULL,0,'active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(22,21,'samsung-phones','Samsung Phones','Latest Samsung Galaxy phones in Pakistan — S series flagships, A series mid-rangers and M series value phones with official warranty.',NULL,0,'active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(23,21,'iphone','iPhone','Brand-new and certified refurbished iPhones in Pakistan, from the latest iPhone 16 lineup to proven favourites like iPhone 13.',NULL,0,'active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(24,21,'xiaomi-phones','Xiaomi Phones','Xiaomi and POCO phones in Pakistan with powerful specs at competitive prices.',NULL,0,'active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(25,21,'budget-phones','Budget Phones','Best budget smartphones under Rs 40,000 in Pakistan — great performance without breaking the bank.',NULL,0,'active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(26,NULL,'used-phones','Used Phones','Tested and graded used phones in Pakistan. Every device is inspected for battery health and display quality before sale.',NULL,0,'active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(27,26,'refurbished-phones','Refurbished Phones','Certified refurbished phones with replacement-grade parts and warranty — premium devices at reduced prices.',NULL,0,'active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(28,NULL,'accessories','Mobile Accessories','Mobile accessories in Pakistan — power banks, chargers, cables, earbuds and cases from trusted brands.',NULL,0,'active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(29,28,'power-banks','Power Banks','Anker and Baseus power banks with fast charging support, available with warranty in Pakistan.',NULL,0,'active','2026-10-21 15:17:32','2026-10-21 15:17:32'),(30,NULL,'repair-services','Repair Services','Professional mobile repair services in Pakistan — screen replacement, battery replacement and water damage repair with genuine parts.',NULL,0,'active','2026-10-21 15:17:32','2026-10-21 15:17:32');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('fixed','percent') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(12,2) NOT NULL,
  `min_subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `max_uses` int unsigned DEFAULT NULL,
  `used_count` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_coupon_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_sessions`
--

DROP TABLE IF EXISTS `customer_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_sessions` (
  `id` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int unsigned NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_csession_customer` (`customer_id`),
  CONSTRAINT `fk_csession_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_sessions`
--

LOCK TABLES `customer_sessions` WRITE;
/*!40000 ALTER TABLE `customer_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customer_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filter_pages`
--

DROP TABLE IF EXISTS `filter_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filter_pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `brand_id` int unsigned DEFAULT NULL,
  `filters` json DEFAULT NULL,
  `intro` text COLLATE utf8mb4_unicode_ci,
  `is_indexable` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_filter_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filter_pages`
--

LOCK TABLES `filter_pages` WRITE;
/*!40000 ALTER TABLE `filter_pages` DISABLE KEYS */;
INSERT INTO `filter_pages` VALUES (4,'samsung-phones-under-150000','Samsung Phones Under Rs 150,000',21,21,'{\"condition\": \"new\", \"max_price\": 150000}','<p>Every new Samsung phone currently under Rs 150,000 at Lala Mobiles, from the A-series workhorses to last-generation flagships — all with official warranty and Cash on Delivery.</p>',1,'published','2026-10-21 15:17:34','2026-10-21 15:17:34'),(5,'refurbished-iphones','Refurbished iPhones in Pakistan',27,22,'{\"condition\": \"refurbished\"}','<p>Certified refurbished iPhones with minimum 85% battery health and a 3-month shop warranty. Each unit is individually tested and photographed.</p>',1,'published','2026-10-21 15:17:34','2026-10-21 15:17:34'),(6,'best-power-banks-pakistan','Best Power Banks in Pakistan',29,NULL,'{\"condition\": \"new\"}','<p>Anker and Baseus power banks with genuine capacity, fast charging support and local warranty — no fake mAh claims, just tested batteries.</p>',1,'published','2026-10-21 15:17:34','2026-10-21 15:17:34');
/*!40000 ALTER TABLE `filter_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flash_sale_items`
--

DROP TABLE IF EXISTS `flash_sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flash_sale_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `flash_sale_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `campaign_price` decimal(12,2) DEFAULT NULL,
  `discount_pct` tinyint unsigned DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_flash_item` (`flash_sale_id`,`product_id`),
  KEY `fk_flash_item_product` (`product_id`),
  CONSTRAINT `fk_flash_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_flash_item_sale` FOREIGN KEY (`flash_sale_id`) REFERENCES `flash_sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash_sale_items`
--

LOCK TABLES `flash_sale_items` WRITE;
/*!40000 ALTER TABLE `flash_sale_items` DISABLE KEYS */;
INSERT INTO `flash_sale_items` VALUES (1,1,25,84999.00,NULL,0),(2,1,36,33999.00,NULL,10),(3,1,34,14999.00,NULL,20),(4,1,37,11999.00,NULL,30),(5,1,30,94999.00,NULL,40),(6,1,21,134999.00,NULL,50);
/*!40000 ALTER TABLE `flash_sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flash_sales`
--

DROP TABLE IF EXISTS `flash_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flash_sales` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme` enum('flame','violet','electric','custom') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'flame',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_flash_window` (`is_active`,`starts_at`,`ends_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flash_sales`
--

LOCK TABLES `flash_sales` WRITE;
/*!40000 ALTER TABLE `flash_sales` DISABLE KEYS */;
INSERT INTO `flash_sales` VALUES (1,'This week\'s price drops','Genuine markdowns on in-stock devices — ends at the time shown.','flame','2026-10-20 17:06:33','2026-10-25 18:59:00',1,'2026-10-21 17:06:33','2026-10-21 17:06:33');
/*!40000 ALTER TABLE `flash_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hero_slides`
--

DROP TABLE IF EXISTS `hero_slides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hero_slides` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_mobile` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_label` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme` enum('tech','navy','flame','jade','custom') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tech',
  `overlay` tinyint(1) NOT NULL DEFAULT '1',
  `position` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_hero_active` (`is_active`,`position`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hero_slides`
--

LOCK TABLES `hero_slides` WRITE;
/*!40000 ALTER TABLE `hero_slides` DISABLE KEYS */;
INSERT INTO `hero_slides` VALUES (1,'The phones you actually want — in stock today','New, used and refurbished smartphones from Samsung, Apple, Xiaomi, Vivo and more. Tested, warranted, delivered.',NULL,NULL,'Pakistan\'s mobile marketplace','Shop smartphones','/category/smartphones','tech',1,0,1,NULL,NULL,'2026-10-21 17:06:33','2026-10-21 17:06:33'),(2,'Certified used & refurbished, priced fairly','Save on devices with checked battery health and a real warranty — no surprises when it arrives.',NULL,NULL,'Save more','Browse used phones','/category/used-phones','navy',1,10,1,NULL,NULL,'2026-10-21 17:06:33','2026-10-21 17:06:33'),(3,'Cracked screen? We fix it.','Certified repairs for iPhone, Samsung and Xiaomi — screen, battery and charging port replacements.',NULL,NULL,'Repair services','Book a repair','/repair/screen-replacement','custom',1,20,1,NULL,NULL,'2026-10-21 17:06:33','2026-10-21 17:06:33');
/*!40000 ALTER TABLE `hero_slides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homepage_sections`
--

DROP TABLE IF EXISTS `homepage_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `homepage_sections` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(48) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_section_key` (`section_key`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homepage_sections`
--

LOCK TABLES `homepage_sections` WRITE;
/*!40000 ALTER TABLE `homepage_sections` DISABLE KEYS */;
INSERT INTO `homepage_sections` VALUES (1,'hero',NULL,NULL,0,1),(2,'trust',NULL,NULL,10,1),(3,'categories',NULL,'Browse the full range — every listing links to a live category page.',20,1),(4,'flash',NULL,NULL,30,1),(5,'bestsellers',NULL,'Ranked by units sold in real orders.',40,1),(6,'new-arrivals',NULL,NULL,50,1),(7,'brands',NULL,'Only brands we actually stock.',60,1),(8,'banners',NULL,NULL,70,1),(9,'recommended',NULL,NULL,80,1),(10,'repairs',NULL,NULL,90,1),(11,'reviews',NULL,'Only approved, verified-purchase reviews are shown.',100,1),(12,'blog','Guides & Tips',NULL,105,1),(13,'newsletter',NULL,NULL,110,1);
/*!40000 ALTER TABLE `homepage_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_subscribers`
--

DROP TABLE IF EXISTS `newsletter_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter_subscribers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','subscribed','unsubscribed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'subscribed',
  `consented_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter_subscribers`
--

LOCK TABLES `newsletter_subscribers` WRITE;
/*!40000 ALTER TABLE `newsletter_subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `product_id` int unsigned DEFAULT NULL,
  `variant_id` int unsigned DEFAULT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_oitem_order` (`order_id`),
  CONSTRAINT `fk_oitem_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,37,NULL,'Anker Soundcore P40i Wireless Earbuds','ANKERSOUNDCOREP40I',12999.00,1),(2,2,30,NULL,'Vivo V50 Lite 5G','VIVOV50LITE',99999.00,1),(3,3,36,NULL,'Xiaomi Redmi 14C','XIAOMIREDMI14C',33999.00,1),(4,4,36,NULL,'Xiaomi Redmi 14C','XIAOMIREDMI14C',33999.00,1);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `shipping` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL,
  `payment_method` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cod',
  `status` enum('pending','confirmed','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_reference` (`reference`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'LM5B5881A0E9','Abdur Rahman','catoo557@gmail.com','+923302862323','KPK\r\nPeshawar','Peshawar',12999.00,250.00,13249.00,'cod','pending','2026-10-21 16:02:39'),(2,'LMB33BE3E7E0','Lala Mobiles','lalamobilespk@gmail.com','0329090909','asd','peshawar',99999.00,0.00,99999.00,'cod','pending','2026-10-21 17:16:03'),(3,'LM445E636D22','Ayesha Rahman','ayesha@example.com','0300-1234567','House 12, Street 4, Model Town','Lahore',33999.00,250.00,34249.00,'cod','pending','2026-10-22 03:40:24'),(4,'LM92B77BDF68','Ayesha Rahman','ayesha@example.com','0300-1234567','House 12, Street 4, Model Town','Lahore',33999.00,250.00,34249.00,'cod','pending','2026-10-22 03:40:52');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_type` enum('static','repair') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'static',
  `intro` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` mediumtext COLLATE utf8mb4_unicode_ci,
  `service_name` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_from` decimal(12,2) DEFAULT NULL,
  `price_to` decimal(12,2) DEFAULT NULL,
  `area_served` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_page_slug` (`slug`),
  KEY `idx_page_type_status` (`page_type`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (9,'about','About Lala Mobiles','static','Who we are and why thousands of customers across Pakistan trust Lala Mobiles.','<p>Lala Mobiles has been serving customers from Saddar, Rawalpindi since 2015. We specialise in new, used and refurbished smartphones, with every device tested before it leaves our shop.</p><h2>What we promise</h2><ul><li>Genuine devices with verified IMEI</li><li>Transparent grading for used and refurbished stock</li><li>Written warranty on every purchase</li><li>Cash on Delivery and nationwide shipping</li></ul><h2>Visit us</h2><p>Main Mobile Market, Saddar, Rawalpindi. Open daily from 11am to 9pm. Call us at +92-300-0000000 before visiting to confirm stock.</p>',NULL,NULL,NULL,NULL,'published','2026-10-21 15:17:34','2026-10-21 15:17:34'),(10,'contact','Contact Us','static','Questions about stock, warranty or delivery? Reach the Lala Mobiles team.','<p>We reply to WhatsApp and phone enquiries within business hours (11am–9pm, Pakistan time).</p><h2>Get in touch</h2><ul><li><strong>Phone / WhatsApp:</strong> +92-300-0000000</li><li><strong>Email:</strong> info@lalamobiles.pk</li><li><strong>Shop:</strong> Main Mobile Market, Saddar, Rawalpindi</li></ul><h2>Delivery and returns</h2><p>Orders ship nationwide via courier, usually within 24–48 hours. Returns are accepted within 7 days for sealed products in original packaging; refurbished and used units carry a shop warranty instead.</p>',NULL,NULL,NULL,NULL,'published','2026-10-21 15:17:34','2026-10-21 15:17:34'),(11,'warranty-return-policy','Warranty & Return Policy','static','Warranty coverage, return windows and refund timelines for every purchase at Lala Mobiles.','<h2>Warranty</h2><p>New phones carry the manufacturer’s official warranty where applicable. Used and refurbished phones carry a 1-month and 3-month Lala Mobiles shop warranty respectively, covering hardware faults.</p><h2>Returns</h2><p>Sealed products can be returned within 7 days in original packaging. Used and refurbished units are covered by warranty rather than returns, since each unit is individually tested and graded.</p><h2>Refunds</h2><p>Approved refunds are processed within 7 working days to the original payment method or via bank transfer for Cash on Delivery orders.</p>',NULL,NULL,NULL,NULL,'published','2026-10-21 15:17:34','2026-10-21 15:17:34'),(12,'delivery-information','Delivery Information','static','Shipping times, Cash on Delivery coverage and charges across Pakistan.','<h2>Nationwide delivery</h2><p>We deliver to all major cities in Pakistan, including Karachi, Lahore, Islamabad, Rawalpindi, Faisalabad, Multan and Peshawar. Orders placed before 2pm are usually dispatched the same day.</p><h2>Cash on Delivery</h2><p>Cash on Delivery is available nationwide. For orders above Rs 100,000 we may request a small advance to confirm the order.</p><h2>Charges</h2><p>Delivery is free above Rs 50,000. Below that, a flat Rs 250 courier charge applies.</p>',NULL,NULL,NULL,NULL,'published','2026-10-21 15:17:34','2026-10-21 15:17:34'),(13,'privacy-policy','Privacy Policy','static','How Lala Mobiles collects, uses and protects your personal information.','<p>This policy explains what information we collect when you order from Lala Mobiles and how we use it.</p><h2>What we collect</h2><p>Name, phone number, delivery address and order history. We do not store card details — online payments are handled by our payment partner.</p><h2>How we use it</h2><p>To process and deliver orders, handle warranty claims and answer your questions. We do not sell personal data to third parties.</p><h2>Cookies</h2><p>We use essential cookies for the shopping cart. Analytics cookies are only set after you give consent.</p>',NULL,NULL,NULL,NULL,'published','2026-10-21 15:17:34','2026-10-21 15:17:34'),(14,'screen-replacement','Mobile Screen Replacement Service','repair','Professional screen replacement for Samsung, iPhone, Xiaomi and more — genuine parts, same-day service.','<p>A cracked display is the most common phone fault we see. Our technicians replace screens using genuine or original-quality parts, with same-day turnaround for most models.</p><h2>What’s included</h2><ul><li>Free diagnostic check</li><li>Genuine or original-quality display</li><li>Water-seal reapplication where applicable</li><li>30-day warranty on the replaced part</li></ul><h2>How it works</h2><p>Walk in at our Saddar shop or call ahead to reserve parts for your model. Most screen replacements are completed within 2–3 hours.</p>','Screen Replacement',9500.00,45000.00,'Rawalpindi, Islamabad, Lahore, Karachi','published','2026-10-21 15:17:34','2026-10-21 15:17:34'),(15,'battery-replacement','Mobile Battery Replacement','repair','Same-day battery replacement with genuine-capacity cells and health report.','<p>If your phone barely survives half a day, a battery replacement is usually cheaper than a new phone. We stock genuine-capacity cells for all popular models.</p><h2>What’s included</h2><ul><li>Battery health test before and after</li><li>Genuine-capacity replacement cell</li><li>Adhesive and water-seal reapplication</li><li>30-day warranty on the battery</li></ul>','Battery Replacement',4500.00,15000.00,'Rawalpindi, Islamabad, Lahore, Karachi','published','2026-10-21 15:17:34','2026-10-21 15:17:34'),(16,'water-damage-repair','Water Damage Repair Service','repair','Ultrasonic cleaning and board-level diagnosis for liquid-damaged phones.','<p>Liquid damage responds best to fast action. Bring your phone in as soon as possible — we ultrasonically clean the board, replace corroded components where feasible, and provide an honest assessment of what is recoverable.</p><h2>Honest diagnosis</h2><p>Board-level repair is never guaranteed. We quote only after inspection and will tell you plainly when a repair is not worth the cost.</p>','Water Damage Repair',3500.00,25000.00,'Rawalpindi, Islamabad','published','2026-10-21 15:17:34','2026-10-21 15:17:34');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `path` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_pimage_product` (`product_id`),
  CONSTRAINT `fk_pimage_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (20,20,'img/products/samsung-galaxy-s25-ultra.svg','Samsung Galaxy S25 Ultra 5G in Titanium Grey',0),(21,21,'img/products/samsung-galaxy-a56.svg','Samsung Galaxy A56 5G in Awesome Graphite',0),(22,22,'img/products/apple-iphone-16.svg','Apple iPhone 16 in Ultramarine',0),(23,23,'img/products/apple-iphone-16-pro-max.svg','Apple iPhone 16 Pro Max in Natural Titanium',0),(24,24,'img/products/iphone-13-refurbished.svg','Certified refurbished Apple iPhone 13 in Midnight',0),(25,25,'img/products/xiaomi-redmi-note-14-pro.svg','Xiaomi Redmi Note 14 Pro 5G in Lavender Purple',0),(26,26,'img/products/xiaomi-poco-x7-pro.svg','POCO X7 Pro 5G in Green',0),(27,27,'img/products/infinix-note-50-pro.svg','Infinix Note 50 Pro+ in Titanium Grey',0),(28,28,'img/products/tecno-camon-30.svg','Tecno Camon 30 5G in Iceland',0),(29,29,'img/products/oppo-reno-13.svg','Oppo Reno 13 5G in Sky Blue',0),(30,30,'img/products/vivo-v50-lite.svg','Vivo V50 Lite 5G in Titanium Gold',0),(31,31,'img/products/realme-14-pro.svg','Realme 14 Pro 5G in Pearl White',0),(32,32,'img/products/samsung-galaxy-a16.svg','Samsung Galaxy A16 4G in Blue Black',0),(33,33,'img/products/iphone-12-used.svg','Used Apple iPhone 12 in White',0),(34,34,'img/products/anker-power-bank-20000.svg','Anker PowerCore 20000mAh power bank in black',0),(35,35,'img/products/baseus-65w-charger.svg','Baseus 65W GaN fast charger in white',0),(36,36,'img/products/xiaomi-redmi-14c.svg','Xiaomi Redmi 14C in Starry Blue',0),(37,37,'img/products/anker-soundcore-p40i.svg','Anker Soundcore P40i wireless earbuds in black',0);
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_specs`
--

DROP TABLE IF EXISTS `product_specs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_specs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `spec_group` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'General',
  `spec_label` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `spec_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_pspec_product` (`product_id`),
  CONSTRAINT `fk_pspec_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=300 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_specs`
--

LOCK TABLES `product_specs` WRITE;
/*!40000 ALTER TABLE `product_specs` DISABLE KEYS */;
INSERT INTO `product_specs` VALUES (156,20,'General','Brand','Samsung',0),(157,20,'General','Model','Galaxy S25 Ultra',1),(158,20,'General','Condition','New — Sealed',2),(159,20,'Display','Size','6.9 inches',3),(160,20,'Display','Type','Dynamic AMOLED 2X, 120Hz',4),(161,20,'Performance','Processor','Snapdragon 8 Elite',5),(162,20,'Performance','RAM','12GB',6),(163,20,'Performance','Storage','512GB',7),(164,20,'Camera','Main','200MP',8),(165,20,'Battery','Capacity','5000 mAh',9),(166,20,'Battery','Charging','45W Fast Charging',10),(167,21,'General','Brand','Samsung',0),(168,21,'General','Model','Galaxy A56 5G',1),(169,21,'General','Condition','New — Sealed',2),(170,21,'Display','Size','6.7 inches',3),(171,21,'Display','Type','Super AMOLED, 120Hz',4),(172,21,'Performance','Processor','Exynos 1580',5),(173,21,'Performance','RAM','8GB',6),(174,21,'Performance','Storage','256GB',7),(175,21,'Camera','Main','50MP OIS',8),(176,21,'Battery','Capacity','5000 mAh',9),(177,21,'Battery','Charging','45W Fast Charging',10),(178,22,'General','Brand','Apple',0),(179,22,'General','Model','iPhone 16',1),(180,22,'General','Condition','New — Sealed',2),(181,22,'Display','Size','6.1 inches',3),(182,22,'Display','Type','Super Retina XDR OLED',4),(183,22,'Performance','Processor','Apple A18',5),(184,22,'Performance','Storage','128GB',6),(185,22,'Camera','Main','48MP Fusion',7),(186,22,'Battery','Capacity','3561 mAh',8),(187,23,'General','Brand','Apple',0),(188,23,'General','Model','iPhone 16 Pro Max',1),(189,23,'General','Condition','New — Sealed',2),(190,23,'Display','Size','6.9 inches',3),(191,23,'Display','Type','Super Retina XDR OLED, 120Hz',4),(192,23,'Performance','Processor','Apple A18 Pro',5),(193,23,'Performance','Storage','256GB',6),(194,23,'Camera','Main','48MP Fusion + 5x Telephoto',7),(195,23,'Battery','Capacity','4685 mAh',8),(196,24,'General','Brand','Apple',0),(197,24,'General','Model','iPhone 13',1),(198,24,'General','Condition','Refurbished — Grade A',2),(199,24,'Display','Size','6.1 inches',3),(200,24,'Performance','Storage','128GB',4),(201,24,'Camera','Main','12MP Dual',5),(202,24,'Battery','Health','85%+ (replaced)',6),(203,25,'General','Brand','Xiaomi',0),(204,25,'General','Model','Redmi Note 14 Pro 5G',1),(205,25,'General','Condition','New — Sealed',2),(206,25,'Display','Size','6.67 inches',3),(207,25,'Display','Type','AMOLED, 120Hz',4),(208,25,'Performance','Processor','Dimensity 7300 Ultra',5),(209,25,'Performance','RAM','8GB',6),(210,25,'Performance','Storage','256GB',7),(211,25,'Camera','Main','200MP OIS',8),(212,25,'Battery','Capacity','5110 mAh',9),(213,26,'General','Brand','POCO',0),(214,26,'General','Model','X7 Pro 5G',1),(215,26,'General','Condition','New — Sealed',2),(216,26,'Display','Size','6.67 inches',3),(217,26,'Performance','Processor','Dimensity 8400 Ultra',4),(218,26,'Performance','RAM','12GB',5),(219,26,'Performance','Storage','512GB',6),(220,26,'Battery','Capacity','6000 mAh',7),(221,26,'Battery','Charging','90W HyperCharge',8),(222,27,'General','Brand','Infinix',0),(223,27,'General','Model','Note 50 Pro+',1),(224,27,'General','Condition','New — Sealed',2),(225,27,'Display','Size','6.78 inches',3),(226,27,'Display','Type','AMOLED, 144Hz',4),(227,27,'Performance','RAM','12GB',5),(228,27,'Performance','Storage','256GB',6),(229,27,'Camera','Main','108MP',7),(230,27,'Battery','Capacity','5200 mAh',8),(231,28,'General','Brand','Tecno',0),(232,28,'General','Model','Camon 30 5G',1),(233,28,'General','Condition','New — Sealed',2),(234,28,'Display','Size','6.78 inches',3),(235,28,'Performance','RAM','8GB',4),(236,28,'Performance','Storage','256GB',5),(237,28,'Battery','Capacity','5000 mAh',6),(238,29,'General','Brand','Oppo',0),(239,29,'General','Model','Reno 13 5G',1),(240,29,'General','Condition','New — Sealed',2),(241,29,'Display','Size','6.59 inches',3),(242,29,'Performance','Processor','Dimensity 8350',4),(243,29,'Performance','RAM','12GB',5),(244,29,'Performance','Storage','256GB',6),(245,29,'Camera','Main','50MP',7),(246,29,'Battery','Capacity','5600 mAh',8),(247,30,'General','Brand','Vivo',0),(248,30,'General','Model','V50 Lite 5G',1),(249,30,'General','Condition','New — Sealed',2),(250,30,'Display','Size','6.77 inches',3),(251,30,'Performance','RAM','8GB',4),(252,30,'Performance','Storage','256GB',5),(253,30,'Camera','Main','50MP Sony',6),(254,30,'Battery','Capacity','6500 mAh',7),(255,31,'General','Brand','Realme',0),(256,31,'General','Model','14 Pro 5G',1),(257,31,'General','Condition','New — Sealed',2),(258,31,'Display','Size','6.7 inches',3),(259,31,'Performance','Processor','Dimensity 7300 Energy',4),(260,31,'Performance','RAM','12GB',5),(261,31,'Performance','Storage','256GB',6),(262,31,'Battery','Capacity','5800 mAh',7),(263,32,'General','Brand','Samsung',0),(264,32,'General','Model','Galaxy A16 4G',1),(265,32,'General','Condition','New — Sealed',2),(266,32,'Display','Size','6.7 inches',3),(267,32,'Display','Type','Super AMOLED',4),(268,32,'Performance','RAM','6GB',5),(269,32,'Performance','Storage','128GB',6),(270,32,'Battery','Capacity','5000 mAh',7),(271,33,'General','Brand','Apple',0),(272,33,'General','Model','iPhone 12',1),(273,33,'General','Condition','Used — Tested',2),(274,33,'Display','Size','6.1 inches',3),(275,33,'Performance','Storage','64GB',4),(276,33,'Battery','Health','80%+',5),(277,34,'General','Brand','Anker',0),(278,34,'General','Condition','New',1),(279,34,'Capacity','Battery','20000 mAh',2),(280,34,'Ports','Output','USB-C 30W + USB-A',3),(281,34,'Warranty','Period','18 months',4),(282,35,'General','Brand','Baseus',0),(283,35,'General','Condition','New',1),(284,35,'Output','Power','65W',2),(285,35,'Ports','Output','2×USB-C + USB-A',3),(286,35,'Warranty','Period','12 months',4),(287,36,'General','Brand','Xiaomi',0),(288,36,'General','Model','Redmi 14C',1),(289,36,'General','Condition','New — Sealed',2),(290,36,'Display','Size','6.88 inches',3),(291,36,'Performance','Processor','Helio G81 Ultra',4),(292,36,'Performance','RAM','4GB',5),(293,36,'Performance','Storage','128GB',6),(294,36,'Battery','Capacity','5160 mAh',7),(295,37,'General','Brand','Anker Soundcore',0),(296,37,'General','Model','P40i',1),(297,37,'General','Condition','New',2),(298,37,'Battery','Playtime','60 hours (with case)',3),(299,37,'Features','ANC','Hybrid Active Noise Cancelling',4);
/*!40000 ALTER TABLE `product_specs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `sku` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `storage` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ram` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_delta` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stock` int NOT NULL DEFAULT '0',
  `gtin` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mpn` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_variant_product` (`product_id`),
  CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (5,20,NULL,'8GB / 256GB — Titanium Black','256GB','8GB','Titanium Black',0.00,10,NULL,NULL,0,1),(6,20,NULL,'12GB / 512GB — Titanium Grey','512GB','12GB','Titanium Grey',60000.00,10,NULL,NULL,1,1),(7,21,NULL,'8GB / 128GB — Awesome Graphite','128GB','8GB','Awesome Graphite',-10000.00,10,NULL,NULL,0,1),(8,21,NULL,'8GB / 256GB — Awesome Olive','256GB','8GB','Awesome Olive',0.00,10,NULL,NULL,1,1);
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_id` int unsigned DEFAULT NULL,
  `category_id` int unsigned DEFAULT NULL,
  `short_desc` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `condition` enum('new','used','refurbished') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `sku` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gtin` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mpn` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warranty` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_image` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating_avg` decimal(3,2) NOT NULL DEFAULT '0.00',
  `rating_count` int NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','discontinued','deleted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `redirect_to_id` int unsigned DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_slug` (`slug`),
  KEY `idx_product_brand` (`brand_id`),
  KEY `idx_product_category` (`category_id`),
  KEY `idx_product_status` (`status`),
  KEY `idx_product_updated` (`updated_at`),
  KEY `idx_product_slug_status` (`slug`,`status`),
  KEY `idx_product_price` (`price`),
  CONSTRAINT `fk_product_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=550149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (20,'samsung-galaxy-s25-ultra','Samsung Galaxy S25 Ultra 5G',21,22,'Galaxy S25 Ultra with Snapdragon 8 Elite, 200MP camera and S Pen — official Pakistani warranty.','<p>The Samsung Galaxy S25 Ultra is the most powerful Galaxy ever, pairing the Snapdragon 8 Elite chipset with a 200MP main camera and a built-in S Pen. The 6.9-inch QHD+ Dynamic AMOLED 2X display delivers buttery-smooth 120Hz scrolling, while the 5000mAh battery easily covers a full day of heavy use.</p><p>At Lala Mobiles, every S25 Ultra ships sealed with official Pakistani warranty and Samsung’s 15W+ fast charging support.</p>','new',549999.00,NULL,14,'SAMSUNGGALAXYS25ULTRA',NULL,NULL,'Official warranty','img/products/samsung-galaxy-s25-ultra.svg','Samsung Galaxy S25 Ultra 5G in Titanium Grey',0.00,0,1,'active',NULL,'2026-10-21 15:17:32','2026-10-21 15:17:32','2026-10-22 05:15:37'),(21,'samsung-galaxy-a56','Samsung Galaxy A56 5G',21,22,'Galaxy A56 5G with 50MP OIS camera, 6.7-inch Super AMOLED display and 5 years of updates.','<p>The Samsung Galaxy A56 5G brings flagship-grade features to the mid-range: a 6.7-inch 120Hz Super AMOLED display, a 50MP main camera with OIS and 45W fast charging over a 5000mAh battery.</p><p>Samsung promises five generations of OS and security updates, making the A56 one of the best long-term values in its class. Available now at Lala Mobiles with official warranty.</p>','new',144999.00,139999.00,32,'SAMSUNGGALAXYA56',NULL,NULL,'Official warranty','img/products/samsung-galaxy-a56.svg','Samsung Galaxy A56 5G in Awesome Graphite',4.67,3,1,'active',NULL,'2026-10-21 15:17:33','2026-10-21 15:17:33','2026-10-21 15:37:57'),(22,'apple-iphone-16','Apple iPhone 16 (128GB)',22,23,'iPhone 16 with A18 chip, 48MP Fusion camera and Camera Control button — PTA approved.','<p>The iPhone 16 pairs Apple’s A18 chip with a 48MP Fusion camera and the new Camera Control button, in a lighter aluminium design with Ceramic Shield protection.</p><p>Every iPhone sold by Lala Mobiles is PTA approved with full Apple service eligibility in Pakistan. Cash on Delivery and nationwide shipping available.</p>','new',384999.00,NULL,21,'APPLEIPHONE16',NULL,NULL,'Official warranty','img/products/apple-iphone-16.svg','Apple iPhone 16 in Ultramarine',5.00,2,1,'active',NULL,'2026-10-21 15:17:33','2026-10-21 15:17:33','2026-10-21 15:17:34'),(23,'apple-iphone-16-pro-max','Apple iPhone 16 Pro Max (256GB)',22,23,'iPhone 16 Pro Max with A18 Pro, 5x tetraprism zoom and 6.9-inch titanium design.','<p>The iPhone 16 Pro Max is Apple’s biggest phone yet: a 6.9-inch titanium body, A18 Pro chip, 48MP Fusion camera with 5x optical zoom and up to 33 hours of video playback.</p><p>PTA approved and sealed, sold with Lala Mobiles’ shop warranty in addition to Apple service coverage.</p>','new',544999.00,529999.00,9,'APPLEIPHONE16PROMAX',NULL,NULL,'Official warranty','img/products/apple-iphone-16-pro-max.svg','Apple iPhone 16 Pro Max in Natural Titanium',0.00,0,0,'active',NULL,'2026-10-21 15:17:33','2026-10-21 15:17:33','2026-10-21 15:17:33'),(24,'iphone-13-refurbished','Apple iPhone 13 (128GB) — Refurbished',22,27,'Certified refurbished iPhone 13 with new battery (85%+ health) and 3-month shop warranty.','<p>A certified refurbished iPhone 13 with a replaced battery (minimum 85% health), a grade-A display and a fresh body. Each unit passes a 32-point functional test covering Face ID, cameras, speakers and charging.</p><p>Covered by a 3-month Lala Mobiles warranty. Stock is limited and refreshed weekly — contact us to reserve a unit.</p>','refurbished',149999.00,NULL,6,'IPHONE13REFURBISHED',NULL,NULL,'Shop warranty','img/products/iphone-13-refurbished.svg','Certified refurbished Apple iPhone 13 in Midnight',4.00,1,0,'active',NULL,'2026-10-21 15:17:33','2026-10-21 15:17:33','2026-10-21 15:17:34'),(25,'xiaomi-redmi-note-14-pro','Xiaomi Redmi Note 14 Pro 5G',23,24,'Redmi Note 14 Pro 5G with 200MP camera, 120Hz AMOLED and 45W fast charging.','<p>The Xiaomi Redmi Note 14 Pro 5G features a 200MP main camera with OIS, a 6.67-inch 120Hz AMOLED display and 45W turbo charging for its 5110mAh battery.</p><p>Excellent value for money with HyperOS and two years of security updates. Available with warranty at Lala Mobiles.</p>','new',89999.00,84999.00,40,'XIAOMIREDMINOTE14PRO',NULL,NULL,'Official warranty','img/products/xiaomi-redmi-note-14-pro.svg','Xiaomi Redmi Note 14 Pro 5G in Lavender Purple',3.50,2,1,'active',NULL,'2026-10-21 15:17:33','2026-10-21 15:17:33','2026-10-21 15:17:34'),(26,'xiaomi-poco-x7-pro','POCO X7 Pro 5G',23,24,'POCO X7 Pro with Dimensity 8400 Ultra, 6000mAh battery and 90W HyperCharge.','<p>The POCO X7 Pro targets performance seekers with the Dimensity 8400 Ultra chipset, a 6.67-inch 120Hz AMOLED panel and a massive 6000mAh battery with 90W charging.</p><p>A great choice for gaming and heavy multitasking, backed by Lala Mobiles’ warranty.</p>','new',114999.00,NULL,18,'XIAOMIPOCOX7PRO',NULL,NULL,'Official warranty','img/products/xiaomi-poco-x7-pro.svg','POCO X7 Pro 5G in Green',0.00,0,0,'active',NULL,'2026-10-21 15:17:33','2026-10-21 15:17:33','2026-10-21 15:17:33'),(27,'infinix-note-50-pro','Infinix Note 50 Pro+',24,25,'Infinix Note 50 Pro+ with 108MP camera, 144Hz AMOLED and 90W charging.','<p>The Infinix Note 50 Pro+ combines a 108MP camera, a 6.78-inch 144Hz AMOLED display and 90W fast charging at a mid-range price that is hard to beat in Pakistan.</p><p>Runs XOS on Android 15 with a large vapour-chamber cooling system for sustained gaming performance.</p>','new',74999.00,69999.00,27,'INFINIXNOTE50PRO',NULL,NULL,'Official warranty','img/products/infinix-note-50-pro.svg','Infinix Note 50 Pro+ in Titanium Grey',0.00,0,0,'active',NULL,'2026-10-21 15:17:33','2026-10-21 15:17:33','2026-10-21 15:17:33'),(28,'tecno-camon-30','Tecno Camon 30 5G',25,25,'Tecno Camon 30 5G with 50MP selfie camera, 120Hz AMOLED and 7000mAh-class battery life.','<p>The Tecno Camon 30 5G is built around photography: a 50MP selfie shooter, a 50MP main camera and Tecno’s AI-enhanced imaging pipeline, on a 6.78-inch 120Hz AMOLED display.</p><p>Long battery life and a wide service network make it a sensible daily driver in Pakistan.</p>','new',64999.00,NULL,35,'TECNOCAMON30',NULL,NULL,'Official warranty','img/products/tecno-camon-30.svg','Tecno Camon 30 5G in Iceland',0.00,0,0,'active',NULL,'2026-10-21 15:17:33','2026-10-21 15:17:33','2026-10-21 15:17:33'),(29,'oppo-reno-13','Oppo Reno 13 5G',26,21,'Oppo Reno 13 5G with Dimensity 8350, IP69 rating and 50MP portrait camera.','<p>The Oppo Reno 13 5G brings an IP69 dust-and-water rating, a Dimensity 8350 chipset and a 50MP portrait-tuned camera system to the premium mid-range.</p><p>Sleek design, capable performance and Oppo’s after-sales network across Pakistan.</p>','new',134999.00,NULL,12,'OPPORENO13',NULL,NULL,'Official warranty','img/products/oppo-reno-13.svg','Oppo Reno 13 5G in Sky Blue',0.00,0,0,'active',NULL,'2026-10-21 15:17:33','2026-10-21 15:17:33','2026-10-21 15:17:33'),(30,'vivo-v50-lite','Vivo V50 Lite 5G',27,21,'Vivo V50 Lite 5G with 50MP Sony camera, 120Hz AMOLED and 6500mAh battery.','<p>The Vivo V50 Lite 5G pairs a 50MP Sony main sensor with a huge 6500mAh battery and 90W flash charging, wrapped in a slim 7.5mm body.</p><p>A reliable all-rounder with strong battery life — ideal for heavy social media use.</p>','new',109999.00,99999.00,21,'VIVOV50LITE',NULL,NULL,'Official warranty','img/products/vivo-v50-lite.svg','Vivo V50 Lite 5G in Titanium Gold',5.00,1,0,'active',NULL,'2026-10-21 15:17:34','2026-10-21 15:17:34','2026-10-22 04:27:54'),(31,'realme-14-pro','Realme 14 Pro 5G',28,21,'Realme 14 Pro 5G with 50MP IMX882 camera, curved AMOLED and 80W charging.','<p>The Realme 14 Pro 5G offers a curved 120Hz AMOLED display, a 50MP Sony IMX882 main camera and 80W SuperVOOC charging in a slim, pearl-finish body.</p><p>Solid performance for the price, available with warranty at Lala Mobiles.</p>','new',119999.00,NULL,15,'REALME14PRO',NULL,NULL,'Official warranty','img/products/realme-14-pro.svg','Realme 14 Pro 5G in Pearl White',0.00,0,0,'active',NULL,'2026-10-21 15:17:34','2026-10-21 15:17:34','2026-10-21 15:17:34'),(32,'samsung-galaxy-a16','Samsung Galaxy A16 4G',21,25,'Galaxy A16 4G with 6.7-inch Super AMOLED, 50MP camera and 6 years of updates.','<p>The Samsung Galaxy A16 4G is the dependable budget pick: a 6.7-inch Super AMOLED display, a 50MP camera and Samsung’s class-leading six-year update promise.</p><p>One of the safest budget purchases in Pakistan — sealed with official warranty at Lala Mobiles.</p>','new',64999.00,NULL,45,'SAMSUNGGALAXYA16',NULL,NULL,'Official warranty','img/products/samsung-galaxy-a16.svg','Samsung Galaxy A16 4G in Blue Black',0.00,0,0,'active',NULL,'2026-10-21 15:17:34','2026-10-21 15:17:34','2026-10-21 15:17:34'),(33,'iphone-12-used','Apple iPhone 12 (64GB) — Used',22,26,'Tested used iPhone 12 with 80%+ battery health and 1-month shop warranty.','<p>A carefully tested used iPhone 12 with battery health of 80% or better, no display defects and fully functional Face ID. Each unit is cleaned, factory-reset and PTA approved.</p><p>Backed by a 1-month Lala Mobiles warranty. Photos are of the exact unit you will receive — contact us for the current lot.</p>','used',99999.00,NULL,4,'IPHONE12USED',NULL,NULL,'Shop warranty','img/products/iphone-12-used.svg','Used Apple iPhone 12 in White',0.00,0,0,'active',NULL,'2026-10-21 15:17:34','2026-10-21 15:17:34','2026-10-21 15:17:34'),(34,'anker-power-bank-20000','Anker PowerCore 20000mAh Power Bank',29,29,'Anker 20000mAh power bank with 30W USB-C PD fast charging.','<p>The Anker PowerCore 20000mAh gives you multiple full charges for any modern smartphone. The 30W USB-C PD port can even charge laptops and tablets, while PowerIQ keeps every device charging safely.</p><p>18-month Anker warranty, available with Cash on Delivery across Pakistan.</p>','new',17999.00,15999.00,60,'ANKERPOWERBANK20000',NULL,NULL,'Official warranty','img/products/anker-power-bank-20000.svg','Anker PowerCore 20000mAh power bank in black',5.00,1,0,'active',NULL,'2026-10-21 15:17:34','2026-10-21 15:17:34','2026-10-21 15:17:34'),(35,'baseus-65w-charger','Baseus 65W GaN Fast Charger',30,28,'Baseus 65W GaN III charger with 2×USB-C + USB-A — charge laptop and phone together.','<p>This Baseus 65W GaN III charger packs laptop-class power into a pocketable body: two USB-C ports and one USB-A share 65W, enough for a MacBook Air plus a phone at once.</p><p>GaN technology means less heat and a smaller charger. Includes 12-month warranty.</p>','new',8999.00,NULL,80,'BASEUS65WCHARGER',NULL,NULL,'Official warranty','img/products/baseus-65w-charger.svg','Baseus 65W GaN fast charger in white',0.00,0,0,'active',NULL,'2026-10-21 15:17:34','2026-10-21 15:17:34','2026-10-21 15:17:34'),(36,'xiaomi-redmi-14c','Xiaomi Redmi 14C',23,25,'Redmi 14C with 6.88-inch 120Hz display and 5160mAh battery — best phone under Rs 40,000.','<p>The Xiaomi Redmi 14C is our top pick for budget buyers: a huge 6.88-inch 120Hz display, a 5160mAh battery and a capable Helio G81 chip, all under Rs 40,000.</p><p>Ideal as a first smartphone or a secondary device, with a clean HyperOS experience.</p>','new',37999.00,34999.00,53,'XIAOMIREDMI14C',NULL,NULL,'Official warranty','img/products/xiaomi-redmi-14c.svg','Xiaomi Redmi 14C in Starry Blue',0.00,0,0,'active',NULL,'2026-10-21 15:17:34','2026-10-21 15:17:34','2026-10-22 03:40:52'),(37,'anker-soundcore-p40i','Anker Soundcore P40i Wireless Earbuds',29,28,'Soundcore P40i earbuds with ANC, 60-hour battery life and IPX5 water resistance.','<p>The Soundcore P40i earbuds deliver hybrid active noise cancellation, punchy BassUp sound and up to 60 hours of total playtime with the case.</p><p>IPX5 rated for workouts, with wireless charging support. 12-month warranty included.</p>','new',14999.00,12999.00,47,'ANKERSOUNDCOREP40I',NULL,NULL,'Official warranty','img/products/anker-soundcore-p40i.svg','Anker Soundcore P40i wireless earbuds in black',0.00,0,0,'active',NULL,'2026-10-21 15:17:34','2026-10-21 15:17:34','2026-10-21 16:02:39');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promo_banners`
--

DROP TABLE IF EXISTS `promo_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promo_banners` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme` enum('navy','electric','violet','flame','jade','custom') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'electric',
  `location` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home',
  `position` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_banner_loc` (`location`,`is_active`,`position`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promo_banners`
--

LOCK TABLES `promo_banners` WRITE;
/*!40000 ALTER TABLE `promo_banners` DISABLE KEYS */;
INSERT INTO `promo_banners` VALUES (1,'Budget phones under Rs 40,000','Solid daily drivers that don\'t break the bank.',NULL,'/category/budget-phones','electric','home',0,1,NULL,NULL,'2026-10-21 17:06:33','2026-10-21 17:06:33'),(2,'Power banks & fast chargers','Keep going with Anker and Baseus accessories.',NULL,'/category/power-banks','violet','home',10,1,NULL,NULL,'2026-10-21 17:06:33','2026-10-21 17:06:33'),(3,'Trade up to a refurbished iPhone','Premium hardware at a fraction of the price.',NULL,'/category/refurbished-phones','navy','home',20,1,NULL,NULL,'2026-10-21 17:06:33','2026-10-21 17:06:33');
/*!40000 ALTER TABLE `promo_banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recently_viewed`
--

DROP TABLE IF EXISTS `recently_viewed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recently_viewed` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `token` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int unsigned NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_recent` (`token`,`product_id`),
  KEY `idx_recent_token` (`token`,`viewed_at`),
  KEY `fk_recent_product` (`product_id`),
  CONSTRAINT `fk_recent_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recently_viewed`
--

LOCK TABLES `recently_viewed` WRITE;
/*!40000 ALTER TABLE `recently_viewed` DISABLE KEYS */;
INSERT INTO `recently_viewed` VALUES (1,'28f556688765c73335c761dc189c5dba93f523d87be29d4bf82b9d9b61e7c89b',30,'2026-10-21 17:15:37'),(2,'7e3d8e38af475dc864bbc1c202f305f5dd2afaa34b39d3b71e05f6ce39b90e28',22,'2026-10-21 17:18:23'),(3,'11c3f7be7871b3988f919bc56f882901ed72ce091ca0fdeba0512ee42a5b1c5e',21,'2026-10-21 17:18:45'),(4,'921731beabb18cf200126f6c9c9aeb505ad71bf2b72466a5a5bdcbf23b6b9ffa',22,'2026-10-21 17:19:53'),(5,'1caf149e12301d32448896c5b78c665ffd53b7ce08e40504b3d50053082042cc',21,'2026-10-21 17:20:52'),(6,'1eec2803bac03f9e2b3aac3cab96def340a8583f6392d90f75026ebbdbf3c18d',21,'2026-10-22 03:23:29'),(7,'3483665c65da6bf9f17f71ded069975ddba5fdd334df4abe697073c7e0d409c9',21,'2026-10-22 03:23:42'),(8,'a9b660135589524ea054f156deed53b5c86525853d9fe96f3b6613817a62c2a9',21,'2026-10-22 03:25:32'),(9,'84a5d889321d7fd410a07f007a0298ea8cea86dd34bc88f866ad1ffb1fcc8ef5',21,'2026-10-22 03:25:41'),(10,'921731beabb18cf200126f6c9c9aeb505ad71bf2b72466a5a5bdcbf23b6b9ffa',21,'2026-10-22 03:36:07'),(11,'9ca5bf1aa6e812bea4e8a7d98ffe2f9728c2e320d1fd17c82d1d4066bd36a96f',20,'2026-10-22 03:33:47'),(12,'a63f8e0902a0daf391d69288d722e0008a8c773ec9b321fbdf0bb080403529d0',20,'2026-10-22 03:33:47'),(13,'b367f84a5e3ca7f3e5af8d06c15888b51fc293a065baa70ef544752ea8ef3a62',20,'2026-10-22 03:33:48'),(14,'5dc13d93dab9369c00b0b0c0e763ccb6bc5e655892dc21fc6ea73981a70f671d',20,'2026-10-22 03:33:48'),(15,'852eea8649ef610a431e966a3b9a44b8487d1e823cc74323459c72e53533dd4d',20,'2026-10-22 03:33:48'),(16,'6ef2adced521a690fc896a72b3808915bbac688650ed3475b9ab37dce67a267d',20,'2026-10-22 03:33:48'),(17,'1ad747448d8c0d1ce6cd82fda4772b73213155f69bfe3decf155a60e6a9dd9a6',20,'2026-10-22 03:33:49'),(18,'2a2f06e5c66ff026253901f3b64582ed4b66ad76e95a3f3dc0b1651e6d2eec4a',21,'2026-10-22 03:33:49'),(19,'d8b6ba6ca7f22fe57930889932e5e5254b74bcc819bdabed354e5a7fff864dfa',21,'2026-10-22 03:33:49'),(20,'0f5b21dae6c219616e323ceed1f5f4a1d7474bc6f016dd9cd78f64fcb3ec2155',20,'2026-10-22 03:33:49'),(21,'7841b493d4bb7d1002ed0542c04dd753c4cc6c891df176463d936709dc0675d5',20,'2026-10-22 03:33:49'),(24,'43e3c6636811eaf386368c1543a0482e937c4d996937dc5f7613368b5a9e6e33',21,'2026-10-22 03:46:06'),(25,'aba5b8aea3d73997cac7166b9f4659ce45088bcebeac4da03205ae1f276a1720',21,'2026-10-22 03:51:37'),(26,'26908ab67b24b29dfee28af9132d823fb445cf464d4eae2785c3eb9c3197f619',22,'2026-10-22 03:51:37'),(27,'92d0de42d97a8d45b318d4b5d9ce32d30c26cff0ec90c0900ab8a9d8e1a8bfa5',25,'2026-10-22 03:51:38'),(28,'38e29bfc554811dd655468c8cbfcffd25834e3cc7ca5a37933170d57fe9c492e',36,'2026-10-22 03:51:38'),(29,'5bf14030b1ac637ed93d9c38a42f05f6ab2fc474208f002977ee1441ec8afd57',34,'2026-10-22 03:51:38'),(30,'b3cfdddbac2e76a0be96bef5f3e6da29567f8c0b531311aaf5cab392ba061ce8',37,'2026-10-22 03:51:38'),(31,'b9e9c614251d77d5bda03a627f94e37eb9f38eec438fc001bf8b44d7df8c597f',30,'2026-10-22 03:51:38'),(32,'bf3f03ec77474a4aa574beee016dd2dec2243043e772acf826fcc3232e424dc0',35,'2026-10-22 03:51:38'),(33,'4f26d9a761f7c73eaf2f63cb3a6496716185cea6408c8d0cae3d27aaab10bc89',33,'2026-10-22 03:51:38'),(34,'fcbd227317f8cf4cdaa6f457724db09ff2de9981bf7936d486b3bdfbe3b3f3f5',32,'2026-10-22 03:51:38'),(35,'7a680524ecef69d5479e00105609cf1cc661b76e32d03cd7d0f16cec10e7ccdd',31,'2026-10-22 03:51:38'),(36,'f324bbc2287d347d17e5a74c8cf813dbe07a15c8ca31565d91f53385783d7736',20,'2026-10-22 03:51:39'),(37,'ac5748e466c1320ebb7628c23e7fe8ba2a99a3d3623252c4137a2aa67cbea187',24,'2026-10-22 03:51:39'),(38,'3564dfb714b264a95a0a9284723b325053a0c78b4f2f32c773ffe5c7675a53b1',29,'2026-10-22 03:51:39'),(39,'8dde22da124259b978e5820180ae5a30062ee34479c22d7b4fef50bf927b35f0',28,'2026-10-22 03:51:39'),(40,'c4a77a539db42c5689277019f3e75ed0c5a93e941424217963caa45807d1a17f',27,'2026-10-22 03:51:39'),(41,'baf4bc459f14d0d38bb7abadbebabe92ca0a278175c7d77c2e7f3a334d48093d',23,'2026-10-22 03:51:39'),(42,'550b30f053abbc6dd2ba03c955876e2068740ba704067560b6b8d92cfdae1c5c',26,'2026-10-22 03:51:39'),(43,'b0c618a804052e3ea0229227c6ac0a67f118990769066fb21ddf38bcac629022',21,'2026-10-22 03:58:56'),(44,'6c5a06bcef5a81607256d6b6bd6a0f9abd71f1a1fca2117bc53d4ef906d98d0b',22,'2026-10-22 03:58:56'),(45,'6924703798b53e38b21822ba4708074b9bd1af387999abe456bcddb2e93e6262',25,'2026-10-22 03:58:56'),(46,'4fddcac957b83f766e4c1be0adfd508c6c56adce54e7dfe95067a4d9241b2893',36,'2026-10-22 03:58:56'),(47,'f09d5608808a248c058753e5ebe68dcf954aa846ad1e8534dec3fa47a1e069fc',34,'2026-10-22 03:58:56'),(48,'a49dc0ea73f5ce11f398f72112a2fc58f82180e91212462c4b41e93c762da79e',37,'2026-10-22 03:58:56'),(49,'76de590b1203fddc62e5dffc72d609fe25f67d25d268c375a022800a46a458d4',30,'2026-10-22 03:58:56'),(50,'f5ea4761f7185f4d21effd15b93c06f61600b0895d6d01fbc4a05b1e20c42df9',35,'2026-10-22 03:58:56'),(51,'3f57b2b77ea571d4814f502f6ae5d5441211018dfb6115e7b4371b2470386dda',33,'2026-10-22 03:58:57'),(52,'59f19b972a85f04d6f2aff36067109313a78cb89f81bb61dec3bd21161f7651d',32,'2026-10-22 03:58:57'),(53,'8f6e1ca2f33b035b6f8fc347bd282799259e8d5c5aa81f508c49dbb282245ea1',31,'2026-10-22 03:58:57'),(54,'f2bd4b722692c37cb2260d0b593ff9f838c1a1d34a0b38a74370fdeb24a041ee',20,'2026-10-22 03:58:57'),(55,'53e823c3350bd2e843f13feb0208d650948f193049f973e222c3427e8b7bbf1a',24,'2026-10-22 03:58:57'),(56,'3456bfbe16923d6efa579844dec5967439c39813381cd5c58359f3a7e04f0c98',29,'2026-10-22 03:58:57'),(57,'f404eab0a23fb725ed345cfa23428a507011352dd9dec55904d9eb6ff7ecc142',28,'2026-10-22 03:58:57'),(58,'5a6f7c1c4da54402ebed9cf197880b3540702313603f1072b669e3808e8ec586',27,'2026-10-22 03:58:57'),(59,'3c98330f586182f935fd93e0541799c47092de4f24ab0b97694f597cff58a718',23,'2026-10-22 03:58:58'),(60,'03a63babeb8efc01d194809364be01d11a2015a9d20ef7b9cd6d174c6f8463d3',26,'2026-10-22 03:58:58'),(61,'f0cc60d20054ec447e206646792a2a7402f450bf9b5d4c60a4301dda4998bf9c',21,'2026-10-22 03:59:31'),(62,'c71e043901ac691d30b062d6900bbfbc0455e48ad471579e034604cbc548a067',22,'2026-10-22 03:59:31'),(63,'a59f7f61de61fdcafe34a187adc25651f234fa5dd68a065ffb6e5bea68ac597a',25,'2026-10-22 03:59:32'),(64,'1ae32b6f5e454971879d5732ba31ad870c9804e2a37f0864abeb008fcb770f04',36,'2026-10-22 03:59:32'),(65,'61d353052c3b0addc30b71083e7bb94106d884b9e677deb5241c614cb2c621e8',34,'2026-10-22 03:59:32'),(66,'ecaebcc43ba024a166fa6a7ceca6298fd128717aa4ad034f0462045e82dcd7f3',37,'2026-10-22 03:59:32'),(67,'804ace48e63d6f126b1dba614f54cea28ca257721c7b1c3edc216de452c30eb0',30,'2026-10-22 03:59:32'),(68,'901ae75da8eddd4cba1d715819d8eba46d676d2418e6c85c9c3b3a701a121691',35,'2026-10-22 03:59:32'),(69,'cddf30af4b484e0fcd2fe5704232e1c8d698f4b7d56b2aa66773e20b231353f7',33,'2026-10-22 03:59:32'),(70,'507f9fbcb614549ae126e332bedeec36a5d14da98ffbd92c240b1a9845f531d6',32,'2026-10-22 03:59:32'),(71,'504ed30fa396f3f393c6ae06aef0787429406be1de7733d13a990d93d827b94c',31,'2026-10-22 03:59:32'),(72,'7588f8e635f755883fbafb7bffa83b352ff713746e44c676bd0167a5198b46c9',20,'2026-10-22 03:59:33'),(73,'4691dfdc1a978a31eba17c03ed3cc53d7ac5332e714914e4fcb03431b7fb4e01',24,'2026-10-22 03:59:33'),(74,'10ed49280db7ea1ac819e1bd0b9cab4185b671fe3ac308ed12c90d608467cae5',29,'2026-10-22 03:59:33'),(75,'b928bba088bc5c1a1dea8d891c5c8f6384dcc9f3d95a8b4dd0897a7da52f679f',28,'2026-10-22 03:59:33'),(76,'35046b0cea6b5afe406bcce6db64d7713bfa1455ad8a8a79e7ce3ff3e69f05aa',27,'2026-10-22 03:59:33'),(77,'182b099e15dab0f4a9d1e236ad58b6a8d0ab2da21721cbcf3f6bfbdd00e3f5e1',23,'2026-10-22 03:59:33'),(78,'1b86365dded079da89c4da8e00320407d994d0f4011f5553dcd120555e44b1d1',26,'2026-10-22 03:59:33'),(79,'3c0c53ddca2370be96b71bd14129707c153257c490eac00c6de78f0ae9445c22',20,'2026-10-22 03:59:44'),(80,'77d011e16bfaa19b9f56d896b1b9e324a87cd1ee72243a06be7e142762d3d92b',20,'2026-10-22 03:59:45'),(81,'774307baab3fa5020ffa076a4588f49ceac0287483be365efcbe01cba18840b0',20,'2026-10-22 03:59:46'),(82,'9529471d05df7e1e58a3fa63cf97a76954dfe6a678943a2e0c0c1ef79e5ac34c',20,'2026-10-22 03:59:46'),(83,'10961f8bf8df8234152a5fe9002f38ad7ffc7c655d386d9f08691794e8122a91',20,'2026-10-22 03:59:46'),(84,'b192b16ef81399a8ed7e9a0ee2d1959e6b1725b4bb4d0d578271d7f6cf5c886c',20,'2026-10-22 03:59:46'),(85,'6458790164c271967ab9cad77da0a6eab7d983c2b327dc37d6142d4f97f7718b',20,'2026-10-22 03:59:46'),(86,'0f6dd9b2a0cc1ff7d463cb0e43e962717ee91bcf1d29a886a816e17298274ae2',21,'2026-10-22 03:59:47'),(87,'a190d155eb811d33989e4cd381cbcd2e93b6f68921f4106b8000cb2d7ba718c8',21,'2026-10-22 03:59:47'),(88,'d583ee7abe67bfa9bacfc293438b10932ee5b9d78699712a643c728d6affc15e',20,'2026-10-22 03:59:47'),(89,'75995135f690b361fee20659c8ae8d0c4259f00ab49899cf75ba9c852aaae661',20,'2026-10-22 03:59:47'),(90,'6e055ac1645053b4244c92c275615d21703b1c8e43effd21d9781c8a35f2bb73',20,'2026-10-22 04:00:08'),(92,'6af928d36ef9b51c43bd300006dabdc9ddc3f272a161a8d7b77c171661c4108f',20,'2026-10-22 04:49:36'),(93,'208ca6a8c7da17afb778616bc020bb02c7619308e3ba7a786438870ee19d0418',20,'2026-10-22 04:49:36'),(94,'617f71727b84dfc49b75c8f7b2390dc308190704c0e6c19088c8e8fb2a384dc6',20,'2026-10-22 04:49:45'),(95,'0de6b4ceb445b30b3a6279269dda5c54adaccaf5860d1a16c6e2ad8bef6837ae',20,'2026-10-22 04:49:57'),(96,'68f732590d2e90a885fd6e1a0d64a0eefc98ca4979c2d8d66fa4accfda422e37',20,'2026-10-22 04:49:57'),(97,'577d63ecbee332a8da3ef84c8364a301d98d3ea2623c4534b896de153e3181fa',20,'2026-10-22 04:49:57'),(98,'305d44cd30ce6d8a72990579169f492009f6ee923738b24275f92ccb17c86d5f',20,'2026-10-22 04:49:57'),(99,'3944c9a8ba1edd06d24ba8b8164d679b61abef36dc76a8a7b91e598337eb452c',20,'2026-10-22 04:49:58'),(100,'7fb3fd97fffc859a2cabf6bd309e7a1d4188f56ff6db007c05d793fbf2bbff88',21,'2026-10-22 04:49:58'),(101,'6fb318dfd36232788768a9b7a82ea8136125dd58acf0962b06ec73dd50f4df95',21,'2026-10-22 04:49:58'),(102,'b04d173951b3470d2b58cddf05e55ca1f38e4c96dd187e0f132effe7471f8038',20,'2026-10-22 04:49:58'),(103,'ddabdbc5d4f1cedb129c0bc4143f064dd6555865f01d29be29ca20831f2bcf19',20,'2026-10-22 04:49:58'),(104,'1a084c62fbdb838ce2083ea0cec0797eeb9311eb033a6afa79d04b7fd74f5cdf',21,'2026-10-22 04:50:39'),(105,'9fbf83f023284990be43fdfe26f73f20afacfe7055c63aa24df8d8700f08354e',22,'2026-10-22 04:50:39'),(106,'33195d276db20d034d05bd056d825ed2f02881a622e098db3322da760dd078ad',25,'2026-10-22 04:50:40'),(107,'0766126f359a1be24ea3f11f41b05d5c6a850644ad52b4f8cb4612aa5eda41ed',36,'2026-10-22 04:50:40'),(108,'e9996b31a8bcce68979b35909d7db5a1656d622f49fdf9c8374557d4209544ff',34,'2026-10-22 04:50:40'),(109,'32e69c6bc129743fc9af78d47db51a8e2166cf5af43c788c94dbadebbdcdab74',37,'2026-10-22 04:50:40'),(110,'aa7867f70c257a6600e43c3f2b41d0d6fb88949a33cb61e7aa80daf1b97e3201',30,'2026-10-22 04:50:40'),(111,'3e56e5b699d110e8bc045d80e5a2205d0eabe60bac2c7524a5701fdecb576591',35,'2026-10-22 04:50:40'),(112,'a90eac31b8d36f3c896d526eb9214e4112031bb1d8ee2e3e637b110426ffcebc',33,'2026-10-22 04:50:40'),(113,'700a28689e65ed3ae825e037bb2c29e52341c3c281ff81220b1bc8e2742a151d',32,'2026-10-22 04:50:40'),(114,'5a8eaf7bb34fdbf20c71be332293c2e009eb21929086c710008f665106f62be5',31,'2026-10-22 04:50:40'),(115,'c7e216e54f56944903a22ddcbc7ae590b4abd457a80786d3862bfd14a31114e1',20,'2026-10-22 04:50:41'),(116,'685588e98cbc181941be0ee3ca1c5910efe0ea54d79ccb3696b72b694a8f8e31',24,'2026-10-22 04:50:41'),(117,'9de18b40a3144c4c124a0951334bc2bc4564f7ebd6ec7594fe72a53a2ce9cddb',29,'2026-10-22 04:50:41'),(118,'336c8dbff8162e8f5d7610f94c9709f6926643f01148f6100e5050d80c9302c4',28,'2026-10-22 04:50:41'),(119,'03b7fd13b0873db1aeb83c5d37efa0089e18de36551431302118c78449c7544f',27,'2026-10-22 04:50:41'),(120,'442993421af110e3dea4ffee72100bb0403abff58285d9e2df67dbb48a6391be',23,'2026-10-22 04:50:41'),(121,'44b7010162eefc5f5bdc4e1af624a6dd8d4f76e8d4733f89d8daaa0e07f61888',26,'2026-10-22 04:50:41'),(122,'bd6f7a6865eb30f8751b91ed7db5cab14fa23956bac9bd42041aedb6c93507b0',21,'2026-10-22 04:52:21'),(123,'d9c32ef603645efd0b7d8a308b00581082bbc415f53cabd22e634eb3ed8f8195',20,'2026-10-22 05:10:54'),(124,'31bd220e3ee82104455d7aed429d76ae596353ae9b950472740dce47db32cc92',20,'2026-10-22 05:10:54'),(125,'a1b9d71df05b7a20c72021bcc90c0e61fb250129f1dbd4494aef3bff46c26f2b',20,'2026-10-22 05:10:57'),(126,'1b4dc91647bee7bdd64b7fdd8395ed3db765da333bb1617dda8f06607660e551',20,'2026-10-22 05:10:58'),(127,'d4138bae5ca7b723fd9ae6f60f9549a0792cd21b971fdc9fcca1f7cb4af4f89d',20,'2026-10-22 05:10:58'),(128,'145e2bfda3622a6cfecc54bd5d7eb7564076b4abaa0ebaf427e787267f6ba567',20,'2026-10-22 05:10:58'),(129,'43dd1b02de1df1c83a840e8b1281a96480c288362e38662435ab40662823732e',20,'2026-10-22 05:10:59'),(130,'487da7fd1082f3b72bb8da94acfb41292b3a7fd6b2acabfb7dadff6c0ac9e3bb',20,'2026-10-22 05:10:59'),(131,'ac58ca30ec90417feb3d387f0f0e10f9307d15bce8fee4447b855dc30d470759',21,'2026-10-22 05:10:59'),(132,'86b7806a183c4ca4e3aa89587b9e510bc4129818babade457021f07764b97646',21,'2026-10-22 05:10:59'),(133,'807b6d4b5799119f6d6905cd80c6151fab5ac6d10367419472e2beb0491d8786',20,'2026-10-22 05:10:59'),(134,'bd44f31d52731b67270e8f0e6f9ef92730e0e6ff575dcf846f8daba6f9f58535',20,'2026-10-22 05:11:00'),(135,'dafe7cd111ff2414aaf6647234a4ef2b82f11134729fe776fd45babadcd8a666',25,'2026-10-22 05:14:04'),(136,'efec56a380267c2ff8b629f7284917beee4498ef89c39f459e4489f0a780cc19',36,'2026-10-22 05:14:05'),(137,'11922134064a75339168dff7847cedd006e307c5d1fd591201cd20fa831c2bd6',34,'2026-10-22 05:14:05'),(138,'e57652651158e46985abbb99e93c5ac23007585e4439f88ccc4af443e99331e5',37,'2026-10-22 05:14:05'),(139,'79c1e8e24bbe5aecc5015b989e8145cfba10ef446bd223526e950feed23acb14',30,'2026-10-22 05:14:05'),(140,'20e2c1fd474b092c5c023f81cfdec274ed2eb962b52f45061aa8a89f7f90f0da',21,'2026-10-22 05:14:05'),(141,'fa25fca3f006520b03a4cbc3e9b7ab8a4e1f95628eba201edd7f1ad1ebb7badd',35,'2026-10-22 05:14:05'),(142,'735a2826529b30ae4b04e62a860478f79b2b183a0c350df86ffba50cfa1392ea',33,'2026-10-22 05:14:05'),(143,'bfabed66c69dcbfd66a006710855dc2e2d2cced6a0983563ca85396b7814e636',32,'2026-10-22 05:14:05'),(144,'206ce8a89573e3178d8fd485c7d1b98e6d314be18f9a98ab02826c007033a86c',31,'2026-10-22 05:14:05'),(145,'33f7e01c465688f21649f14e571e783b42d34a57ab5f3b0fb5310b0ef4054708',22,'2026-10-22 05:14:06'),(146,'54f8cf21ea1d7ed0093bc6481a5057a72d229197842e09e6049d884685622ee6',20,'2026-10-22 05:14:06'),(147,'ceb25da78865217c95d43d85fe4f4e2fe4e620e8e9e948d33834773e317d637a',24,'2026-10-22 05:14:06'),(148,'14636d4c62e807a0d88524ac9cfe11759cb3bec2a3e265fe24174083c0a885f7',29,'2026-10-22 05:14:06'),(149,'eef5d10a6cdd5cfb7e1c5c3edbba890096a0de0af945d1428795d87ee8dba235',28,'2026-10-22 05:14:06'),(150,'855ded537af71925398ccd69ca61d66e760705b8c915dad9fe89a883fb2f69ae',27,'2026-10-22 05:14:06'),(151,'8c0007c990c9a0ff54656c0662d0dc1decbb307d57c839c1648e30a11a1e2ece',23,'2026-10-22 05:14:06'),(152,'15ca1a3fab50755051fd29a4426a6706a1dff178b0d06cb3c529aa2b319fc9b3',26,'2026-10-22 05:14:06'),(153,'a5c0a412aad27ac1dd1f8daaf35e8a46f86c1b4cea9973432c3099a1cd975709',20,'2026-10-22 05:14:51'),(154,'778e5f2168da49ab78002a787ebd0269816c358f2ebb0a65d03f1f55f25acd0b',20,'2026-10-22 05:14:51'),(155,'cb0d5e4d441d00c843e67d5d75c3453e6dec1b83db0856401bad379e11d4965d',20,'2026-10-22 05:14:54'),(156,'b7050fbc17883aa5fe0575ed736868ae6e2266dc929761e6b5aed5062be3c501',20,'2026-10-22 05:14:55'),(157,'2740f0eda6cf537c56aeeadf7ec72806e8fb8a3c1f2c4d7d7e6f18fa17ef0bc8',20,'2026-10-22 05:14:55'),(158,'51a89c528fa73121ed486a50bdb7f1bcc05e0db5bc71726cdc72a3a7b6062ed5',20,'2026-10-22 05:14:55'),(159,'a2a8161e3568e4c5d3748dc6df068a04b81ef293da4b3fbce683b1dab6556192',20,'2026-10-22 05:14:55'),(160,'c0491b0fff5d751faf70740a3ca2b967809faf763e57c40deb1ec9fcd593980c',20,'2026-10-22 05:14:56'),(161,'5e6edb7853b856a858bb35a49087cc1fcd442fc383938063db8744fd278b8e7f',21,'2026-10-22 05:14:56'),(162,'d4f05415f33d927995714a846a26dd0cd0c90e05c62e44b922ee31b5c6985659',21,'2026-10-22 05:14:56'),(163,'eca6339b75e0e95557e188639a94b01bf389e877c117a1669f3bf83e149a8e60',20,'2026-10-22 05:14:56'),(164,'60ddb93bb4f87e0d42f452f6f026fb67ae9612e294ff7d194d52c73e8e7a1181',20,'2026-10-22 05:14:56'),(165,'9a548e82b677f491c2c23d1004dccff88a02d2b68aa547c2cf3d05ac973ced1b',20,'2026-10-22 05:15:32'),(166,'42a37f24ad2a8e6156587b8cf0f5f9c4171abe4976b9af631ecd12a52e4eaba8',20,'2026-10-22 05:15:32'),(167,'f3326751d19ba2e67b1314dba0f30a43badab958a91acccf27eb173b1ba66352',20,'2026-10-22 05:15:35'),(168,'bb32be8b6d790a9b00d9c669559d02522a9b6e2ec2e63f4e2de5d2bb8746200a',20,'2026-10-22 05:15:36'),(169,'28c48614dd9a6b87ea299c1b5dae5d6ee549a962f788739af602919feb1d3526',20,'2026-10-22 05:15:37'),(170,'4b221c4ccf4ad3ce3a47e5129a8ac5a1d3f7d4487e7207f6db7e13aab3c93c9c',20,'2026-10-22 05:15:37'),(171,'d6fb2620fb8f007e51465cd9b5d51611c935d3f36f6f0dee36310ba6b608c8a5',20,'2026-10-22 05:15:37'),(172,'2691b5638554fa7f7ad17e8f2fdbd6b7591a64c7702bd086cebfb14e06966fe5',20,'2026-10-22 05:15:37'),(173,'318c1370faefb542052249312012cb9f7be29ca7d2d48397a8f0f94a86089d69',21,'2026-10-22 05:15:38'),(174,'90ac0394e9605060cfc97ae86911fa1127ac7eca2323c817d52605c6784c853e',21,'2026-10-22 05:15:38'),(175,'cd385303d0792d420903f53aab0e26e2c4c9a53e12015b3d7602c7a278bc2066',20,'2026-10-22 05:15:38'),(176,'8affa12e4525ea4be089c97a6d19251e414bcf7ce51cb769810b1e20973cea92',20,'2026-10-22 05:15:38');
/*!40000 ALTER TABLE `recently_viewed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redirects`
--

DROP TABLE IF EXISTS `redirects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redirects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `from_path` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_path` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_code` smallint NOT NULL DEFAULT '301',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_redirect_from` (`from_path`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `redirects`
--

LOCK TABLES `redirects` WRITE;
/*!40000 ALTER TABLE `redirects` DISABLE KEYS */;
/*!40000 ALTER TABLE `redirects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repair_bookings`
--

DROP TABLE IF EXISTS `repair_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `repair_bookings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('new','contacted','scheduled','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_booking_reference` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repair_bookings`
--

LOCK TABLES `repair_bookings` WRITE;
/*!40000 ALTER TABLE `repair_bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `repair_bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int unsigned NOT NULL,
  `author` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint NOT NULL,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `verified_purchase` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_review_product` (`product_id`,`approved`),
  CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_review_rating` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (11,21,'Ahmed R.',5,'Excellent value','Bought the A56 last month. Battery easily lasts a full day and the camera is surprisingly good at night. Official warranty card was included.',1,1,'2026-10-21 15:17:34'),(12,21,'Sana K.',4,'Great phone, fast delivery','Delivery to Lahore took two days, packaging was secure. Phone is smooth, only wish it had a faster charger in the box.',1,1,'2026-10-21 15:17:34'),(13,21,'Bilal H.',5,'Recommended seller','Genuine sealed unit, IMEI verified before payment. Will buy from Lala Mobiles again.',1,1,'2026-10-21 15:17:34'),(14,22,'Hassan M.',5,'Sealed and PTA approved','Sealed box, PTA approved, Face ID working perfectly. Best price I found in Rawalpindi.',1,1,'2026-10-21 15:17:34'),(15,22,'Ayesha T.',5,'Worth every rupee','Switched from Android and very happy. The Camera Control button is actually useful.',1,1,'2026-10-21 15:17:34'),(16,24,'Usman S.',4,'Battery like new','Refurbished unit came with 91% battery health, better than promised. Minor scuff on the frame but display is flawless.',1,1,'2026-10-21 15:17:34'),(17,34,'Faisal A.',5,'Charges my laptop too','The 30W USB-C port charged my MacBook Air without issues. Genuine Anker product with warranty card.',1,1,'2026-10-21 15:17:34'),(18,25,'Zara L.',4,'Camera is the highlight','200MP camera captures great detail in daylight. HyperOS takes some getting used to.',1,1,'2026-10-21 15:17:34'),(19,25,'Imran D.',3,'Good but heats up','Phone is fine overall but gets warm while gaming. Charger in the box is slower than advertised speed.',0,1,'2026-10-21 15:17:34'),(20,30,'Nida Q.',5,'Battery champion','Two days on a single charge with moderate use. Very happy with this purchase.',0,1,'2026-10-21 15:17:34');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schema_migrations`
--

DROP TABLE IF EXISTS `schema_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schema_migrations` (
  `version` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schema_migrations`
--

LOCK TABLES `schema_migrations` WRITE;
/*!40000 ALTER TABLE `schema_migrations` DISABLE KEYS */;
INSERT INTO `schema_migrations` VALUES ('002_redesign','2026-10-21 16:26:17'),('20261021_initial_performance_indexes','2026-10-21 11:11:33'),('baseline_schema','2026-10-21 11:11:33');
/*!40000 ALTER TABLE `schema_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seo_meta`
--

DROP TABLE IF EXISTS `seo_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seo_meta` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `entity_type` enum('home','product','category','brand','blog','page','filter') COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` int unsigned NOT NULL DEFAULT '0',
  `seo_title` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonical_path` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `robots` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_image` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_card` enum('summary','summary_large_image') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_title` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_description` varchar(320) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_image` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_head` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seo_entity` (`entity_type`,`entity_id`),
  KEY `idx_seo_updated` (`updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seo_meta`
--

LOCK TABLES `seo_meta` WRITE;
/*!40000 ALTER TABLE `seo_meta` DISABLE KEYS */;
/*!40000 ALTER TABLE `seo_meta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `setting_key` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('analytics_consent_required','1','2026-10-21 15:17:32'),('announcement_items','Free nationwide shipping on orders over Rs 5,000|/page/delivery-information\nCash on Delivery available across Pakistan\nWarranty & 7-day returns on every device|/page/warranty-return-policy','2026-10-22 03:53:51'),('bing_webmaster_verification','','2026-10-21 15:17:32'),('blog_enabled','1','2026-10-21 15:17:32'),('canonical_domain','https://www.lalamobiles.pk','2026-10-21 15:17:32'),('default_seo_description','Buy new, used and refurbished mobile phones in Pakistan at best prices. Samsung, iPhone, Xiaomi & more with warranty, Cash on Delivery and nationwide shipping.','2026-10-21 15:17:32'),('default_seo_title','Lala Mobiles — New, Used & Refurbished Phones in Pakistan','2026-10-21 15:17:32'),('feed_enabled','1','2026-10-21 15:17:32'),('feed_free_shipping_over','5000.00 PKR','2026-10-21 15:17:32'),('feed_shipping_country','PK','2026-10-21 15:17:32'),('feed_shipping_price','0.00 PKR','2026-10-21 15:17:32'),('feed_shipping_region','Punjab','2026-10-21 15:17:32'),('feed_shipping_service','Standard','2026-10-21 15:17:32'),('footer_note','Your trusted destination for new, used and refurbished smartphones in Pakistan — every device tested, warranted and delivered to your door.','2026-10-21 17:06:33'),('ga4_measurement_id',NULL,'2026-10-22 05:15:36'),('geo_latitude','33.5942','2026-10-21 15:17:32'),('geo_longitude','73.0551','2026-10-21 15:17:32'),('google_search_console_verification','abc123_test','2026-10-21 15:23:19'),('google_site_verification_method','meta','2026-10-21 15:17:32'),('gtm_container_id','','2026-10-21 15:17:32'),('homepage_seo_description',NULL,'2026-10-22 05:15:36'),('homepage_seo_title',NULL,'2026-10-22 05:15:36'),('merchant_center_id','','2026-10-21 15:17:32'),('og_default_image','img/og-default.svg','2026-10-21 15:17:32'),('opening_hours','Mo-Su 11:00-21:00','2026-10-21 15:23:19'),('organization_address','Main Mobile Market, Saddar','2026-10-21 15:23:19'),('organization_city','Rawalpindi','2026-10-21 15:17:32'),('organization_country','PK','2026-10-21 15:17:32'),('organization_email','info@lalamobiles.pk','2026-10-21 15:17:32'),('organization_logo','img/logo.svg','2026-10-21 15:17:32'),('organization_name','Lala Mobiles','2026-10-21 15:17:32'),('organization_phone','+92-300-0000000','2026-10-21 15:17:32'),('organization_postal','46000','2026-10-21 15:17:32'),('organization_region','Punjab','2026-10-21 15:17:32'),('payment_methods','Cash on Delivery\nBank Transfer\nEasypaisa / JazzCash','2026-10-21 17:06:33'),('pinterest_verification','','2026-10-21 15:17:32'),('review_min_for_aggregate','3','2026-10-22 05:15:38'),('robots_index_site','1','2026-10-22 05:15:37'),('robots_txt_extra',NULL,'2026-10-22 05:15:36'),('site_name','Lala Mobiles','2026-10-21 15:17:32'),('site_tagline','Phones in Pakistan','2026-10-21 15:23:19'),('sitemap_enabled','1','2026-10-21 15:17:32'),('social_facebook','https://www.facebook.com/lalamobiles','2026-10-21 15:17:32'),('social_instagram','https://www.instagram.com/lalamobiles','2026-10-21 15:17:32'),('social_tiktok','','2026-10-21 15:17:32'),('social_youtube','','2026-10-21 15:17:32'),('twitter_default_card','summary_large_image','2026-10-21 15:17:32');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trust_indicators`
--

DROP TABLE IF EXISTS `trust_indicators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trust_indicators` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` varchar(220) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'check',
  `position` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_trust_active` (`is_active`,`position`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trust_indicators`
--

LOCK TABLES `trust_indicators` WRITE;
/*!40000 ALTER TABLE `trust_indicators` DISABLE KEYS */;
INSERT INTO `trust_indicators` VALUES (1,'Warranty included','Every device ships with a written warranty.','shield',0,1,'2026-10-21 17:06:33'),(2,'Nationwide delivery','Cash on Delivery and courier shipping across Pakistan.','truck',10,1,'2026-10-21 17:06:33'),(3,'7-day returns','Changed your mind? Send it back in original condition.','return',20,1,'2026-10-21 17:06:33'),(4,'Tested before dispatch','Battery health, screen and cameras checked by a technician.','genuine',30,1,'2026-10-21 17:06:33'),(5,'Real human support','Call or WhatsApp us before and after you buy.','support',40,1,'2026-10-21 17:06:33');
/*!40000 ALTER TABLE `trust_indicators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlists` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `token` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wishlist` (`token`,`product_id`),
  KEY `idx_wishlist_token` (`token`),
  KEY `fk_wishlist_product` (`product_id`),
  CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlists`
--

LOCK TABLES `wishlists` WRITE;
/*!40000 ALTER TABLE `wishlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'lala_mobiles'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-22 12:38:43
