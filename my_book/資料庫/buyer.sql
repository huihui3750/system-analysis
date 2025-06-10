-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-06-07 15:58:34
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

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `buyer`
--
ALTER TABLE `buyer`
  ADD PRIMARY KEY (`B_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
