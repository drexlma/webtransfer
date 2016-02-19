

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `se_webtransfer`
--

-- --------------------------------------------------------

--
-- Table structure for table `absender`
--

CREATE TABLE IF NOT EXISTS `absender` (
  `absender_id` mediumint(6) unsigned NOT NULL,
  `file_id` mediumint(6) unsigned NOT NULL,
  `adddate` int(11) unsigned NOT NULL,
  `mail` varchar(200) NOT NULL,
  `remote_addr` varchar(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `empfanger`
--

CREATE TABLE IF NOT EXISTS `empfanger` (
  `empfanger_id` mediumint(6) unsigned NOT NULL,
  `file_id` mediumint(6) unsigned NOT NULL,
  `adddate` int(11) unsigned NOT NULL,
  `mail` varchar(200) NOT NULL,
  `remote_addr` varchar(32) NOT NULL,
  `download_date` int(11) unsigned NOT NULL,
  `versendet` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `file`
--

CREATE TABLE IF NOT EXISTS `file` (
  `file_id` mediumint(6) unsigned NOT NULL,
  `path` varchar(300) NOT NULL,
  `name` varchar(100) NOT NULL,
  `adddate` int(11) unsigned NOT NULL,
  `ip` varchar(32) NOT NULL,
  `accesscode` char(64) NOT NULL,
  `password` char(32) NOT NULL,
  `anz_downloads` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `haltbarkeit` mediumint(8) unsigned NOT NULL DEFAULT '30'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absender`
--
ALTER TABLE `absender`
  ADD PRIMARY KEY (`absender_id`),
  ADD UNIQUE KEY `file_id` (`file_id`);

--
-- Indexes for table `empfanger`
--
ALTER TABLE `empfanger`
  ADD PRIMARY KEY (`empfanger_id`);

--
-- Indexes for table `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`file_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absender`
--
ALTER TABLE `absender`
  MODIFY `absender_id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `empfanger`
--
ALTER TABLE `empfanger`
  MODIFY `empfanger_id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `file`
--
ALTER TABLE `file`
  MODIFY `file_id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
