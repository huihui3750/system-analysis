-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-06-07 15:58:46
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
-- 資料表結構 `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL COMMENT '訊息ID',
  `sender_id` int(15) NOT NULL COMMENT '發送者ID (買家或賣家)',
  `sender_id_role` varchar(10) NOT NULL COMMENT '發送者角色 (buyer 或 seller)',
  `receiver_id` int(15) NOT NULL COMMENT '接收者ID (買家或賣家)',
  `receiver_id_role` varchar(10) NOT NULL COMMENT '接收者角色 (buyer 或 seller)',
  `book_id` int(10) DEFAULT NULL COMMENT '相關書籍ID (可選)',
  `message_content` text NOT NULL COMMENT '訊息內容',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '發送時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `sender_id_role`, `receiver_id`, `receiver_id_role`, `book_id`, `message_content`, `timestamp`) VALUES
(1, 30000006, 'buyer', 20000006, 'seller', 2025015, '你好', '2025-06-06 13:31:52'),
(2, 30000006, 'buyer', 20000006, 'seller', 2025016, '我想要購買這本書', '2025-06-06 13:35:24'),
(3, 30000006, 'buyer', 20000006, 'seller', NULL, '你好', '2025-06-07 10:08:47'),
(4, 30000006, 'buyer', 20000006, 'seller', 2025018, '我想要這本書', '2025-06-07 11:22:40'),
(5, 20000006, 'seller', 30000006, 'buyer', NULL, '直接下單就好了', '2025-06-07 11:22:52');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_sender_receiver` (`sender_id`,`receiver_id`),
  ADD KEY `idx_receiver_sender` (`receiver_id`,`sender_id`),
  ADD KEY `idx_book_id` (`book_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '訊息ID', AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
