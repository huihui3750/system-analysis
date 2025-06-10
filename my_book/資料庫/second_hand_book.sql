-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-06-07 15:58:19
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
-- 資料表結構 `book`
--

CREATE TABLE `book` (
  `Book_ID` int(10) NOT NULL COMMENT '書本ID',
  `Book_title` varchar(50) NOT NULL COMMENT '書名',
  `Book_author` varchar(10) NOT NULL COMMENT '書本作者',
  `Book_version` varchar(10) NOT NULL COMMENT '書籍版本',
  `Book_department` varchar(50) NOT NULL COMMENT '書本使用科系',
  `Book_price` int(5) NOT NULL COMMENT '價格',
  `Book_status` varchar(50) NOT NULL COMMENT '書況',
  `Book_image_path` varchar(255) NOT NULL COMMENT '書籍封面圖片',
  `Book_remark` varchar(50) NOT NULL COMMENT '備註',
  `Transaction_status` varchar(30) NOT NULL DEFAULT '未售出' COMMENT '書籍交易狀況',
  `S_ID` int(15) NOT NULL COMMENT '賣家ID'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `book`
--

INSERT INTO `book` (`Book_ID`, `Book_title`, `Book_author`, `Book_version`, `Book_department`, `Book_price`, `Book_status`, `Book_image_path`, `Book_remark`, `Transaction_status`, `S_ID`) VALUES
(2025015, '統計學', '吳俊文', '第一版', '資訊管理系', 450, '全新', 'uploads/6842d6b7331c8.png', '無', '已售出', 20000006),
(2025016, '多益', '蔡心鈴', '第五版', '其他', 550, '可使用', 'uploads/6842ee81a3c4d.png', '書角有輕微破損', '已售出', 20000006),
(2025017, '心理學', '張伊亦', '第四版', '心理系', 500, '全新', 'uploads/6843f5337825d.png', '昨天才拿到這本書，價格可議', '未售出', 20000006),
(2025018, '影像處理', '黃文敬', '第三版', '資料處理系', 500, '普通', 'uploads/68440f132d0c0.png', '書中有少量的破損', '未售出', 20000006);

-- --------------------------------------------------------

--
-- 資料表結構 `buyer`
--

CREATE TABLE `buyer` (
  `B_ID` int(10) NOT NULL COMMENT '買家ID',
  `B_name` varchar(15) NOT NULL COMMENT '買家名稱',
  `B_account` varchar(20) NOT NULL COMMENT '買家帳號',
  `B_password` varchar(255) NOT NULL COMMENT '買家密碼',
  `B_email` varchar(20) NOT NULL COMMENT '買家電子郵件',
  `B_department` varchar(20) NOT NULL COMMENT '買家科系',
  `B_telephone` int(15) NOT NULL COMMENT '買家電話',
  `B_personal_profile` varchar(50) NOT NULL COMMENT '買家個人簡介',
  `B_evaluate` int(11) NOT NULL COMMENT '買家評價'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `buyer`
--

INSERT INTO `buyer` (`B_ID`, `B_name`, `B_account`, `B_password`, `B_email`, `B_department`, `B_telephone`, `B_personal_profile`, `B_evaluate`) VALUES
(30000001, '王小明', 'xmwang01', 'password123', 'xmwang01@example.com', '資訊工程系', 912345678, '喜歡寫程式與閱讀技術書籍', 5),
(30000002, '李佳芳', 'jfang02', 'mysecurepwd', 'jfang02@example.com', '應用外語系', 922123456, '熱愛旅遊與語言學習', 4),
(30000003, '張育誠', 'ycchang03', 'pass45678', 'ycchang03@example.co', '會計學系', 933123456, '數字控，有良好交易信用', 5),
(30000004, '陳彥廷', 'ytchen04', 'chenpass123', 'ytchen04@example.com', '電機工程系', 987123456, '喜愛科技產品與買賣交流', 4),
(30000005, '林彥君', 'yjlin05', 'ilovecats789', 'yjlin05@example.com', '心理系', 977123456, '樂於助人、常參加校內活動', 5),
(30000006, '球', 'yabe0572', 'yabe0572', 'yabe0572@gmail.com', '資管', 958741145, 'yaaa歡迎來買我的書', 5);

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

-- --------------------------------------------------------

--
-- 資料表結構 `seller`
--

CREATE TABLE `seller` (
  `S_ID` int(15) NOT NULL COMMENT '賣家ID',
  `S_name` varchar(20) NOT NULL COMMENT '賣家名稱',
  `S_account` varchar(20) NOT NULL COMMENT '賣家帳號',
  `S_password` varchar(255) NOT NULL COMMENT '賣家密碼',
  `S_email` varchar(20) NOT NULL COMMENT '賣家電子郵件',
  `S_department` varchar(10) NOT NULL COMMENT '賣家科系',
  `S_telephone` int(15) NOT NULL COMMENT '賣家電話',
  `S_personal_profile` varchar(50) NOT NULL COMMENT '賣家個人簡介',
  `S_evaluate` int(10) NOT NULL COMMENT '賣家評價'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `seller`
--

INSERT INTO `seller` (`S_ID`, `S_name`, `S_account`, `S_password`, `S_email`, `S_department`, `S_telephone`, `S_personal_profile`, `S_evaluate`) VALUES
(20000001, '李宜臻', 'yichen01', 'securepass1', 'yichen01@example.com', '資訊管理系', 911111111, '擅長書籍整理與溝通', 5),
(20000002, '陳彥廷', 'ychen02', 'mypassword2', 'ychen02@example.com', '電機工程系', 922222222, '喜歡科技產品及買賣交易', 4),
(20000003, '吳俊廷', 'jtwu03', 'pass3456', 'jtwu03@example.com', '電機工程系', 933333333, '負責商品檢查，態度親切', 5),
(20000004, '林彥君', 'yjlin04', 'linpass789', 'yjlin04@example.com', '心理系', 944444444, '熱心助人，售後服務佳', 5),
(20000005, '王小明', 'xmwang05', 'wangpass123', 'xmwang05@example.com', '資訊工程系', 955555555, '喜歡分享與推薦好書', 4),
(20000006, '羽YA', 'yuee5842', 'yuee5842', 'yuee5842@gmail.com', '獸醫系', 987514462, '無', 5);

-- --------------------------------------------------------

--
-- 資料表結構 `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `book_id`, `buyer_id`, `seller_id`, `price`, `status`, `timestamp`) VALUES
(5, 2025015, 30000006, 20000006, 450.00, 'cancelled', '2025-06-07 18:56:46'),
(6, 2025015, 30000006, 20000006, 450.00, 'completed', '2025-06-07 19:23:01'),
(7, 2025016, 30000006, 20000006, 550.00, 'completed', '2025-06-07 20:40:19');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`Book_ID`);

--
-- 資料表索引 `buyer`
--
ALTER TABLE `buyer`
  ADD PRIMARY KEY (`B_ID`);

--
-- 資料表索引 `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`evaluation_id`);

--
-- 資料表索引 `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_sender_receiver` (`sender_id`,`receiver_id`),
  ADD KEY `idx_receiver_sender` (`receiver_id`,`sender_id`),
  ADD KEY `idx_book_id` (`book_id`);

--
-- 資料表索引 `seller`
--
ALTER TABLE `seller`
  ADD PRIMARY KEY (`S_ID`),
  ADD UNIQUE KEY `S_telephone` (`S_telephone`);

--
-- 資料表索引 `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_transaction_book` (`book_id`),
  ADD KEY `fk_transaction_buyer` (`buyer_id`),
  ADD KEY `fk_transaction_seller` (`seller_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `book`
--
ALTER TABLE `book`
  MODIFY `Book_ID` int(10) NOT NULL AUTO_INCREMENT COMMENT '書本ID', AUTO_INCREMENT=2025019;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '訊息ID', AUTO_INCREMENT=6;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_book` FOREIGN KEY (`book_id`) REFERENCES `book` (`Book_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `buyer` (`B_ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_seller` FOREIGN KEY (`seller_id`) REFERENCES `seller` (`S_ID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
