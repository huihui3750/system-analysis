-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-06-07 15:58:30
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

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`Book_ID`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `book`
--
ALTER TABLE `book`
  MODIFY `Book_ID` int(10) NOT NULL AUTO_INCREMENT COMMENT '書本ID', AUTO_INCREMENT=2025019;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
