<?php
// 顯示所有 PHP 錯誤 (僅在開發環境中使用，生產環境應關閉)
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start(); // 確保 session_start 在最頂端，以避免任何與 session 相關的問題

// 包含資料庫連接檔案
include 'db_connection.php'; // 假設 db_connection.php 包含你的 $conn 資料庫連接變數

// 檢查請求方法是否為 POST (即表單是否被提交)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 從 POST 資料中獲取使用者輸入
    $account = $_POST['account'] ?? ''; // 使用空合併運算符避免未定義變數警告
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? ''; // 這個變數會是 'buyer' 或 'seller'

    // 基本驗證：檢查是否有空欄位
    if (empty($account) || empty($password) || empty($email) || empty($role)) {
        die("<script>alert('所有欄位都必須填寫！'); window.location.href='register.html';</script>");
    }

    // *** 移除密碼雜湊處理 (再次強調：這在生產環境中非常不安全！) ***
    $raw_password = $password;

    // 設置預設值 (可以根據您的需求調整)
    $default_name = '新用戶';
    $default_department = '未設定';
    $default_telephone = 0;
    $default_profile = '無'; // 確保這個變數在所有路徑中都定義了
    $default_evaluate = '5'; // 確保這個變數在所有路徑中都定義了

    // 初始化 $stmt 為 null
    $stmt = null;
    $new_id = null; // 初始化

    // 根據角色選擇插入資料庫
    if ($role === 'buyer') {
        // 從 buyer 表格獲取最大的 B_ID
        $result_id = mysqli_query($conn, "SELECT MAX(B_ID) AS max_id FROM buyer");
        if (!$result_id) {
            die("<script>alert('查詢買家ID失敗: " . $conn->error . "'); window.location.href='register.html';</script>");
        }
        $row_id = mysqli_fetch_assoc($result_id);
        $new_id = $row_id['max_id'] ? $row_id['max_id'] + 1 : 30000001; // 首次註冊從 30000001 開始

        $sql = "INSERT INTO buyer (B_ID, B_name, B_account, B_password, B_email, B_department, B_telephone, B_personal_profile, B_evaluate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // B_ID(int), B_name(s), B_account(s), B_password(s), B_email(s), B_department(s), B_telephone(i), B_personal_profile(s), B_evaluate(i)
        $bind_types = 'isssssisi'; 
        
        if ($stmt) {
            $stmt->bind_param(
                $bind_types,
                $new_id,
                $default_name,
                $account,
                $raw_password,
                $email,
                $default_department,
                $default_telephone,
                $default_profile,
                $default_evaluate
            );
        }
    } elseif ($role === 'seller') {
        // 從 seller 表格獲取最大的 S_ID
        $result_id = mysqli_query($conn, "SELECT MAX(S_ID) AS max_id FROM seller");
        if (!$result_id) {
            die("<script>alert('查詢賣家ID失敗: " . $conn->error . "'); window.location.href='register.html';</script>");
        }
        $row_id = mysqli_fetch_assoc($result_id);
        $new_id = $row_id['max_id'] ? $row_id['max_id'] + 1 : 20000001; // 首次註冊從 20000001 開始

        $sql = "INSERT INTO seller (S_ID, S_name, S_account, S_password, S_email, S_department, S_telephone, S_personal_profile, S_evaluate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // S_ID(int), S_name(s), S_account(s), S_password(s), S_email(s), S_department(s), S_telephone(i), S_personal_profile(s), S_evaluate(i)
        $bind_types = 'isssssisi'; 
        
        if ($stmt) {
            $stmt->bind_param(
                $bind_types,
                $new_id,
                $default_name,
                $account,
                $raw_password,
                $email,
                $default_department,
                $default_telephone,
                $default_profile,
                $default_evaluate
            );
        }
    } else {
        // 如果角色不合法
        die("<script>alert('無效的角色選擇！'); window.location.href='register.html';</script>");
    }

    // 檢查預處理語句是否成功
    if (!$stmt) {
        die("<script>alert('預處理語句失敗: " . $conn->error . "'); window.location.href='register.html';</script>");
    }

    // 執行插入語句
    if ($stmt->execute()) {
        // 插入成功，顯示成功訊息並重定向到登錄頁面
        echo "<script>alert('註冊成功！'); window.location.href='login.html';</script>";
        exit(); // 確保跳轉後停止執行
    } else {
        // 如果執行失敗，顯示詳細錯誤訊息
        die("<script>alert('資料插入失敗: " . $stmt->error . "'); window.location.href='register.html';</script>");
    }
} else {
    // 如果不是 POST 請求，重定向到註冊頁面
    header('Location: register.html');
    exit();
}
?>