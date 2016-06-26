-- Initial database setup

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `users` (
  `ut_id` int(11) NOT NULL AUTO_INCREMENT,
  `bc_id` mediumint(8) unsigned NOT NULL,
  `bc_account` mediumint(9) unsigned NOT NULL,
  `first_name` varchar(50) CHARACTER SET latin1 NOT NULL,
  `last_name` varchar(50) CHARACTER SET latin1 NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 NOT NULL,
  `bc_token` varchar(1500) CHARACTER SET latin1 NOT NULL,
  `pro` tinyint(1) NOT NULL DEFAULT '0',
  `timezone` varchar(100) NOT NULL DEFAULT 'Australia/Sydney',
  `stripe_customer_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ut_id`),
  UNIQUE KEY `bc_id` (`bc_id`),
  FULLTEXT KEY `name` (`first_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
