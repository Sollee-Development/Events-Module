
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) DEFAULT NULL,
  `location` varchar(191) DEFAULT NULL,
  `description` varchar(191) DEFAULT NULL,
  `start_date` DATE DEFAULT NULL,
  `start_time` varchar(191) DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `end_time` varchar(191) DEFAULT NULL,
  `repeat_id` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `start_date asc` (`start_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `repeat_events`
--

DROP TABLE IF EXISTS `repeat_events`;
CREATE TABLE IF NOT EXISTS `repeat_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `freq` varchar(191) DEFAULT NULL,
  `interval_num` int(191) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `freq_id_interval_num` (`freq`,`id`,`interval_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
