<?php
$servername = "127.0.0.1"; // XAMPP 預設
$username = "root";      // XAMPP 中 MySQL 的預設用戶名
$password = "B11256030";          // XAMPP 中 MySQL 的預設密碼 (通常為空)
$dbname = "test"; // 你的資料庫名稱
$dbport = "3307";

//對資料庫連線
$conn=mysqli_connect($servername, $username, $password, $dbname, $dbport);

if ($conn->connect_error) {
    // 在生產環境中，不要直接顯示錯誤訊息給用戶
    // 而是將錯誤寫入日誌，然後顯示一個友好的錯誤頁面
error_log("Database connection failed: " . $conn->connect_error);
    die("連線資料庫失敗，請稍後再試。"); // 這行會停止腳本執行
}

// 設置字符集
$conn->set_charset("utf8mb4");
if(!@mysqli_connect($servername, $username, $password, $dbname, $dbport))
        die("無法對資料庫連線");

//資料庫連線採UTF8
mysqli_query( $conn, "SET NAMES 'utf8'" );

//選擇資料庫
if(!@mysqli_select_db($conn,$dbname))
        die("無法使用資料庫");
?>
