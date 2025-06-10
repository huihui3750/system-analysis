<?php
session_start();
include 'db_connection.php'; // 確保這個檔案存在並能正確連接資料庫

// ==== 除錯開始 (放置在最前面，確保能及早看到) ====
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
//echo "<pre>";
//echo "upload_process.php 載入\n";
//echo "SESSION 內容:\n";
//print_r($_SESSION);
//echo "\n";
//echo "currentUser 內容:\n";
//print_r($_SESSION['currentUser'] ?? '未定義');
//echo "\n";
//echo "POST 內容:\n";
//print_r($_POST);
//echo "\n";
//echo "FILES 內容:\n";
//print_r($_FILES);
//echo "</pre>";
// ==== 除錯結束 ====


// 檢查是否已登入且為賣家
$currentUser = $_SESSION['currentUser'] ?? null;
if (!$currentUser || $currentUser['role'] !== 'seller') {
    echo "<script>alert('錯誤：無法獲取賣家ID，請重新登入！'); window.location.href='login.html';</script>";
    exit();
}

$seller_id = $currentUser['id']; // 獲取當前登入賣家的 ID

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 獲取表單數據
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $version = $_POST['version'] ?? '';
    $department = $_POST['department'] ?? '';
    $price = $_POST['price'] ?? 0;
    $status = $_POST['status'] ?? ''; // 這會是「書況」（例如「良好」、「九成新」）
    $remark = $_POST['remark'] ?? '';
    $transaction_status = '未售出';

    // 處理圖片上傳
    $target_dir = "uploads/"; // 確保這個目錄存在且可寫入
    $image_file_name = null; // 初始化為 null

    // 檢查 uploads 資料夾是否存在，不存在則創建
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true); // 建立 uploads 資料夾並設定權限
        echo "Debug: 創建了 uploads 資料夾。<br>";
    }

    if (isset($_FILES["bookImage"]) && $_FILES["bookImage"]["error"] == UPLOAD_ERR_OK) {
        $imageFileType = strtolower(pathinfo($_FILES["bookImage"]["name"], PATHINFO_EXTENSION));
        // 生成一個唯一檔名，避免重複
        $unique_file_name = uniqid() . "." . $imageFileType;
        $target_file_path = $target_dir . $unique_file_name; // 完整路徑

        // 檢查文件類型
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            echo "<script>alert('抱歉，只允許 JPG, JPEG, PNG & GIF 檔案。'); window.history.back();</script>";
            exit(); // 阻止後續執行
        }

        // 檢查檔案大小 (可選，例如限制 5MB)
        if ($_FILES["bookImage"]["size"] > 5000000) { // 5MB
            echo "<script>alert('抱歉，檔案太大。'); window.history.back();</script>";
            exit();
        }

        if (move_uploaded_file($_FILES["bookImage"]["tmp_name"], $target_file_path)) {
            $image_file_name = $target_file_path; // 儲存相對路徑到資料庫
            echo "Debug: 圖片成功移動到: " . $image_file_name . "<br>"; // 除錯訊息
        } else {
            echo "<script>alert('抱歉，您的檔案上傳失敗。錯誤碼: " . $_FILES["bookImage"]["error"] . "'); window.history.back();</script>";
            exit(); // 阻止後續執行
        }
    } else if (isset($_FILES["bookImage"]) && $_FILES["bookImage"]["error"] != UPLOAD_ERR_NO_FILE) {
        // 處理其他上傳錯誤
        $phpFileUploadErrors = array(
            UPLOAD_ERR_OK => "No errors.",
            UPLOAD_ERR_INI_SIZE => "檔案大小超過 php.ini 的限制。",
            UPLOAD_ERR_FORM_SIZE => "檔案大小超過 HTML 表單的限制。",
            UPLOAD_ERR_PARTIAL => "檔案只有部分上傳。",
            UPLOAD_ERR_NO_FILE => "沒有檔案被上傳。",
            UPLOAD_ERR_NO_TMP_DIR => "找不到暫存資料夾。",
            UPLOAD_ERR_CANT_WRITE => "檔案寫入失敗。",
            UPLOAD_ERR_EXTENSION => "PHP 擴展阻止了檔案上傳。",
        );
        $error_message = $phpFileUploadErrors[$_FILES["bookImage"]["error"]] ?? '未知上傳錯誤。';
        echo "<script>alert('檔案上傳錯誤: " . $error_message . "'); window.history.back();</script>";
        exit();
    }


    // 將書籍資訊插入到資料庫
    // 現在的欄位順序是: Book_title, Book_author, Book_version, Book_department, Book_price, Book_status, Book_image_path, Book_remark, S_ID
    $sql = "INSERT INTO book (Book_title, Book_author, Book_version, Book_department, Book_price, Book_status, Book_image_path, Book_remark, S_ID, Transaction_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // <--- 這裡多了一個 ?, 因為多了一個欄位
    $stmt = $conn->prepare($sql);

    // 除錯：檢查 prepare 是否成功
    if ($stmt === false) {
        echo "Debug: 準備語句失敗: " . $conn->error . "<br>";
        echo "<script>alert('準備語句失敗: " . $conn->error . "'); window.history.back();</script>";
        exit();  // 終止腳本
}

    // 綁定參數。'sssisssis' 假設：
// title (string), author (string), version (string), department (string),
// price (int), status (string), image_path (string), remark (string), S_ID (int)
// 這裡有 9 個問號，對應 9 個參數
$bind_types = "ssssdsisis"; // **** 關鍵修改：多一個 's' ****
    $bind_result = $stmt->bind_param(
        $bind_types,
        $title,
        $author,
        $version,
        $department,
        $price,
        $status, // 這是書況 (Book_status)
        $image_file_name,
        $remark,
        $seller_id,
        $transaction_status //書籍交易狀況
    );

    // 除錯：檢查 bind_param 是否成功
    if ($bind_result === false) {
        echo "Debug: 綁定參數失敗: " . $stmt->error . "<br>";
        echo "<script>alert('綁定參數失敗: " . $stmt->error . "'); window.history.back();</script>";
        $stmt->close();
        exit(); // 終止腳本
    }

    if ($stmt->execute()) {
        echo "<script>alert('書籍上傳成功！'); window.location.href='seller.php';</script>";
    } else {
        echo "Debug: 資料庫執行失敗: " . $stmt->error . "<br>"; // 具體的 MySQL 錯誤
        echo "<script>alert('資料庫執行失敗: " . $stmt->error . "'); window.history.back();</script>";
    }
    $stmt->close();

    $conn->close();
} else {
    echo "<script>alert('無效的請求方法！'); window.location.href='upload.php';</script>";
}
?>