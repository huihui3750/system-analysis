-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-06-07 15:58:50
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

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `seller`
--
ALTER TABLE `seller`
  ADD PRIMARY KEY (`S_ID`),
  ADD UNIQUE KEY `S_telephone` (`S_telephone`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
