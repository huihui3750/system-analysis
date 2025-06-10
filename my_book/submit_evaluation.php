<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connection.php'; // 確保包含資料庫連線檔案


header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// 確保用戶已登入
if (!isset($_SESSION['currentUser'])) {
    $response['message'] = '請先登入！';
    echo json_encode($response);
    exit();
}

$currentUser = $_SESSION['currentUser'];
$currentUserID = $currentUser['id'];

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$transaction_id = $data['transaction_id'] ?? null;
$rater_id = $data['rater_id'] ?? null; // 評價人 ID (即當前登入用戶 ID)
$rated_user_id = $data['rated_user_id'] ?? null; // 被評價人 ID (例如賣家 ID)
$rated_user_role = $data['rated_user_role'] ?? null; // 被評價人的角色 (例如 'seller')
$rating = $data['rating'] ?? null;
$comment = $data['comment'] ?? '';

// 驗證輸入
if (empty($transaction_id) || empty($rater_id) || empty($rated_user_id) || empty($rated_user_role) || $rating === null || $rating < 1 || $rating > 5) {
    $response['message'] = '缺少必要的評價資訊或評分無效。';
    echo json_encode($response);
    exit();
}

// 確保 rater_id 是當前登入用戶
if ($rater_id != $currentUserID) {
    $response['message'] = '評價人ID不匹配。';
    echo json_encode($response);
    exit();
}

$conn->begin_transaction();

try {
    // 1. 檢查該交易是否已完成 (通常只有完成的交易才能評價)
    // 這裡我們假設只有買家評價賣家，且交易狀態必須是 'completed'
    $stmt_check_transaction = $conn->prepare("SELECT status, buyer_id FROM transactions WHERE transaction_id = ? FOR UPDATE");
    if (!$stmt_check_transaction) {
        throw new Exception("準備檢查交易狀態語句失敗: " . $conn->error);
    }
    $stmt_check_transaction->bind_param("i", $transaction_id);
    $stmt_check_transaction->execute();
    $result_check_transaction = $stmt_check_transaction->get_result();
    $transaction_data = $result_check_transaction->fetch_assoc();
    $stmt_check_transaction->close();

    if (!$transaction_data) {
        throw new Exception("交易不存在。");
    }
    if ($transaction_data['status'] !== 'completed') {
        throw new Exception("只有已完成的交易才能評價。目前狀態為: " . $transaction_data['status']);
    }
    // 確保只有買家能評價賣家，並且買家是當前用戶
    if ($currentUser['role'] !== 'buyer' || $transaction_data['buyer_id'] !== $currentUserID) {
        throw new Exception("您無權評價此交易。");
    }

    // 2. 檢查是否已經對此交易評價過 (避免重複評價)
    $stmt_check_eval = $conn->prepare("SELECT COUNT(*) FROM evaluations WHERE transaction_id = ? AND rater_id = ? AND rated_user_id = ?");
    if (!$stmt_check_eval) {
        throw new Exception("準備檢查評價語句失敗: " . $conn->error);
    }
    $stmt_check_eval->bind_param("iii", $transaction_id, $rater_id, $rated_user_id);
    $stmt_check_eval->execute();
    $stmt_check_eval->bind_result($count);
    $stmt_check_eval->fetch();
    $stmt_check_eval->close();

    if ($count > 0) {
        throw new Exception("您已評價過此筆交易。");
    }

    // 3. 插入評價記錄到 evaluations 表
    $stmt_insert_eval = $conn->prepare("INSERT INTO evaluations (transaction_id, rater_id, rated_user_id, rated_user_role, rating, comment) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt_insert_eval) {
        throw new Exception("準備插入評價語句失敗: " . $conn->error);
    }
    $stmt_insert_eval->bind_param("iiisis", $transaction_id, $rater_id, $rated_user_id, $rated_user_role, $rating, $comment);
    if (!$stmt_insert_eval->execute()) {
        throw new Exception("插入評價記錄失敗: " . $stmt_insert_eval->error);
    }
    $stmt_insert_eval->close();

    // 4. (可選) 更新被評價用戶的平均評分
    // 如果您希望顯示賣家的平均評分，可以在這裡計算並更新 seller 表中的字段

    $conn->commit();
    $response['success'] = true;
    $response['message'] = '評價提交成功！';

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
    error_log("Submit Evaluation Error: " . $e->getMessage());
}

$conn->close();
echo json_encode($response);
?>