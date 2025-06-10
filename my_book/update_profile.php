<?php
session_start();
include 'db_connection.php'; // 確保包含資料庫連線檔案

header('Content-Type: application/json'); // 設定回傳格式為 JSON

$response = ['success' => false, 'message' => ''];

// 檢查用戶是否已登入
if (!isset($_SESSION['currentUser'])) {
    $response['message'] = '請先登入！';
    echo json_encode($response);
    exit();
}

$currentUser = $_SESSION['currentUser'];
$userID = $currentUser['id'];
$userRole = $currentUser['role'];

// 確保請求方法為 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = '無效的請求方法。';
    echo json_encode($response);
    exit();
}

// 獲取並解碼前端傳來的 JSON 數據
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 驗證輸入數據
$name = $data['name'] ?? '';
$telephone = $data['telephone'] ?? '';
$email = $data['email'] ?? '';
$department = $data['department'] ?? '';

if (empty($name) || empty($telephone) || empty($email) || empty($department)) {
    $response['message'] = '所有欄位都不能為空。';
    echo json_encode($response);
    exit();
}

// 簡單的 Email 格式驗證
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Email 格式不正確。';
    echo json_encode($response);
    exit();
}

// 電話號碼驗證 (假設為 10 位數字)
if (!preg_match('/^\d{10}$/', $telephone)) {
    $response['message'] = '電話號碼必須是10位數字。';
    echo json_encode($response);
    exit();
}


$stmt = null;
if ($userRole === 'buyer') {
    $sql = "UPDATE buyer SET B_name = ?, B_telephone = ?, B_email = ?, B_department = ? WHERE B_ID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssi", $name, $telephone, $email, $department, $userID);
    }
} else { // seller
    $sql = "UPDATE seller SET S_name = ?, S_telephone = ?, S_email = ?, S_department = ? WHERE S_ID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssi", $name, $telephone, $email, $department, $userID);
    }
}

if ($stmt) {
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response['success'] = true;
            $response['message'] = '資料更新成功。';
            // 更新 session 中的用戶名稱，以便導航欄也能即時更新
            $_SESSION['currentUser']['name'] = $name;
        } else {
            $response['message'] = '沒有資料被更新，可能資料沒有改變。';
        }
    } else {
        $response['message'] = '資料庫更新失敗: ' . $stmt->error;
        error_log("Database update error: " . $stmt->error); // 記錄錯誤到伺服器日誌
    }
    $stmt->close();
} else {
    $response['message'] = '資料庫準備語句失敗: ' . $conn->error;
    error_log("Prepare statement failed: " . $conn->error);
}

$conn->close();
echo json_encode($response);
?>