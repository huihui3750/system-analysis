<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$currentUser = $_SESSION['currentUser'] ?? null;
if (!$currentUser) {
    $response['message'] = '請先登入！';
    echo json_encode($response);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$transaction_id = $data['transaction_id'] ?? null;
$book_id = $data['book_id'] ?? null;
$user_id = $currentUser['id'];
$user_role = $currentUser['role'];

if (empty($transaction_id) || empty($book_id)) {
    $response['message'] = '缺少交易ID或書籍ID。';
    echo json_encode($response);
    exit();
}

$conn->begin_transaction();

try {
    // 1. 檢查交易所有權和狀態
    $stmt_check = $conn->prepare("SELECT buyer_id, seller_id, status FROM transactions WHERE transaction_id = ? FOR UPDATE");
    if (!$stmt_check) {
        throw new Exception("準備檢查交易語句失敗: " . $conn->error);
    }
    $stmt_check->bind_param("i", $transaction_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows === 0) {
        throw new Exception("交易不存在。");
    }
    $transaction_data = $result_check->fetch_assoc();
    $stmt_check->close();

    // 只有交易的買家或賣家可以執行此操作
    if (($user_role === 'buyer' && $user_id != $transaction_data['buyer_id']) ||
        ($user_role === 'seller' && $user_id != $transaction_data['seller_id'])) {
        throw new Exception("您無權取消此交易。");
    }

    // 只有 'pending' 狀態的交易才能被取消（或根據需求擴展允許取消的狀態）
    if ($transaction_data['status'] !== 'pending') {
        throw new Exception("此交易狀態為 '{$transaction_data['status']}'，無法取消。");
    }

    // 2. 更新 transactions 表的狀態為 'cancelled'
    $stmt_update_transaction = $conn->prepare("UPDATE transactions SET status = 'cancelled' WHERE transaction_id = ?");
    if (!$stmt_update_transaction) {
        throw new Exception("準備更新交易狀態語句失敗: " . $conn->error);
    }
    $stmt_update_transaction->bind_param("i", $transaction_id);
    if (!$stmt_update_transaction->execute()) {
        throw new Exception("更新交易狀態失敗: " . $stmt_update_transaction->error);
    }
    $stmt_update_transaction->close();

    // 3. 更新 book 表的 Transaction_status 為 '未售出' (重新上架)
    $stmt_update_book = $conn->prepare("UPDATE book SET Transaction_status = '未售出' WHERE Book_ID = ?");
    if (!$stmt_update_book) {
        throw new Exception("準備更新書籍狀態語句失敗: " . $conn->error);
    }
    $stmt_update_book->bind_param("i", $book_id);
    if (!$stmt_update_book->execute()) {
        throw new Exception("更新書籍狀態失敗: " . $stmt_update_book->error);
    }
    $stmt_update_book->close();

    $conn->commit();
    $response['success'] = true;
    $response['message'] = '交易已成功取消！書籍已重新上架。';

} catch (Exception $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
    error_log("Cancel transaction error: " . $e->getMessage());
}

mysqli_close($conn);
echo json_encode($response);
?>