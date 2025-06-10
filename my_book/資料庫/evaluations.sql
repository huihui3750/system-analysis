-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-06-07 15:58:40
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `second_hand_book`
--

-- --------------------------------------------------------

--
-- 資料表結構 `evaluations`
--

CREATE TABLE `evaluations` (
  `evaluation_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `rater_id` int(11) NOT NULL COMMENT '評價人的ID (買家或賣家)',
  `rated_user_id` int(11) NOT NULL COMMENT '被評價人的ID (買家或賣家)',
  `rated_user_role` varchar(50) NOT NULL COMMENT '被評價人的角色 (buyer 或 seller)',
  `rating` int(11) NOT NULL COMMENT '星級評價 (1-5星)',
  `comment` text DEFAULT NULL COMMENT '評價留言',
  `evaluation_date` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '評價時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `evaluations`
--

INSERT INTO `evaluations` (`evaluation_id`, `transaction_id`, `rater_id`, `rated_user_id`, `rated_user_role`, `rating`, `comment`, `evaluation_date`) VALUES
(2, 6, 30000006, 20000006, 'seller', 4, '快速', '2025-06-07 12:06:20');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`evaluation_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
