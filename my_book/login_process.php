<?php
session_start(); // 啟動會話

// 包含資料庫連接檔案
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account = $_POST['account'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($account) || empty($password)) {
        die("<script>alert('請填寫完整資訊！'); window.location.href='login.html';</script>");
    }

    $currentUser = null;
    $role = null;

    // 嘗試從 buyer 表格驗證
    $sql_buyer = "SELECT B_ID, B_name, B_account, B_password, B_email, B_department, B_telephone, B_personal_profile, B_evaluate FROM buyer WHERE B_account = ?";
    $stmt_buyer = $conn->prepare($sql_buyer);

    if (!$stmt_buyer) {
        die("<script>alert('買家登入預處理語句失敗: " . $conn->error . "'); window.location.href='login.html';</script>");
    }

    $stmt_buyer->bind_param("s", $account);
    $stmt_buyer->execute();
    $result_buyer = $stmt_buyer->get_result();

    if ($result_buyer->num_rows > 0) {
        $buyer_data = $result_buyer->fetch_assoc();
        if ($password === $buyer_data['B_password']) { // 這裡應該使用 password_verify() 如果密碼是雜湊過的
            $currentUser = [
                'id' => $buyer_data['B_ID'],
                'name' => $buyer_data['B_name'],
                'account' => $buyer_data['B_account'],
                'email' => $buyer_data['B_email'],
                'department' => $buyer_data['B_department'],
                'telephone' => $buyer_data['B_telephone'],
                'profile' => $buyer_data['B_personal_profile'],
                'evaluate' => $buyer_data['B_evaluate'],
                'role' => 'buyer'
            ];
            $role = 'buyer';
        }
    }
    $stmt_buyer->close();

    // 如果在 buyer 表中沒找到，嘗試從 seller 表格驗證
    if (!$currentUser) {
        $sql_seller = "SELECT S_ID, S_name, S_account, S_password, S_email, S_department, S_telephone, S_personal_profile, S_evaluate FROM seller WHERE S_account = ?";
        $stmt_seller = $conn->prepare($sql_seller);

        if (!$stmt_seller) {
            die("<script>alert('賣家登入預處理語句失敗: " . $conn->error . "'); window.location.href='login.html';</script>");
        }

        $stmt_seller->bind_param("s", $account);
        $stmt_seller->execute();
        $result_seller = $stmt_seller->get_result();

        if ($result_seller->num_rows > 0) {
            $seller_data = $result_seller->fetch_assoc();
            if ($password === $seller_data['S_password']) { // 這裡應該使用 password_verify() 如果密碼是雜湊過的
                $currentUser = [
                    'id' => $seller_data['S_ID'],
                    'name' => $seller_data['S_name'],
                    'account' => $seller_data['S_account'],
                    'email' => $seller_data['S_email'],
                    'department' => $seller_data['S_department'],
                    'telephone' => $seller_data['S_telephone'],
                    'profile' => $seller_data['S_personal_profile'],
                    'evaluate' => $seller_data['S_evaluate'],
                    'role' => 'seller'
                ];
                $role = 'seller';
            }
        }
        $stmt_seller->close();
    }

    $conn->close();

    if ($currentUser) {
        $_SESSION['currentUser'] = $currentUser; // 將用戶資料存入 session

        // 移除 localStorage 的設置，因為 Session 已經足以維持登入狀態
        // 移除 JavaScript 的 alert 和 window.location.href 重導向
        // 改用 PHP 的 header() 進行重導向

        header('Location: index.php'); // 重導向到首頁
        exit(); // 終止腳本執行
    } else {
        echo "<script>alert('帳號或密碼錯誤！'); window.location.href='login.html';</script>";
        exit();
    }
} else {
    // 如果不是 POST 請求，重導向到登入頁面
    header('Location: login.html');
    exit();
}
?>