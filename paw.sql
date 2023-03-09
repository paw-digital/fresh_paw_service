SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Table structure for table `fresh_deposits`
--

CREATE TABLE `fresh_deposits` (
  `id` int NOT NULL,
  `mixin` smallint NOT NULL DEFAULT '5',
  `mixin_result` smallint NOT NULL,
  `deposited_amount` varchar(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `revert_on_expire` tinyint NOT NULL DEFAULT '0',
  `deposit_address` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `private_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `to_address` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `hash` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `completed` tinyint NOT NULL DEFAULT '0',
  `sent_back` tinyint(1) NOT NULL DEFAULT '0',
  `time_created` int NOT NULL,
  `time_deposited` int NOT NULL DEFAULT '0',
  `time_completed` int NOT NULL DEFAULT '0',
  `time_collected` int NOT NULL DEFAULT '0',
  `time_expiry` int NOT NULL COMMENT 'time PAW will be sent back if it couldn''t be completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Indexes for table `fresh_deposits`
--
ALTER TABLE `fresh_deposits`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `fresh_deposits`
--
ALTER TABLE `fresh_deposits`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;