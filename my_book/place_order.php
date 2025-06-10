<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

$currentUser = $_SESSION['currentUser'] ?? null;
if (!$currentUser || $currentUser['role'] !== 'buyer') { // 只有買家能下單
    $response['message'] = '請先登入買家帳戶才能下單！';
    echo json_encode($response);
    exit();
}

// 獲取前端傳來的 JSON 數據
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$book_id = $data['book_id'] ?? null;
$seller_id = $data['seller_id'] ?? null;
$buyer_id = $currentUser['id']; // 從 Session 獲取買家 ID，更安全
$price = $data['price'] ?? null;

if (empty($book_id) || empty($seller_id) || empty($buyer_id) || empty($price)) {
    $response['message'] = '缺少必要的下單資訊。';
    echo json_encode($response);
    exit();
}

// 開始事務 (確保兩次更新操作是原子的)
$conn->begin_transaction();

try {
    // 1. 檢查書籍狀態是否仍然為 '未售出' (避免重複下單或已被他人預訂)
    $stmt_check_book = $conn->prepare("SELECT Transaction_status FROM book WHERE Book_ID = ? FOR UPDATE"); // FOR UPDATE 鎖定行
    if (!$stmt_check_book) {
        throw new Exception("準備檢查書籍狀態語句失敗: " . $conn->error);
    }
    $stmt_check_book->bind_param("i", $book_id);
    $stmt_check_book->execute();
    $result_check_book = $stmt_check_book->get_result();
    if ($result_check_book->num_rows === 0) {
        throw new Exception("書籍不存在。");
    }
    $book_status_row = $result_check_book->fetch_assoc();
    if ($book_status_row['Transaction_status'] !== '未售出') {
        throw new Exception("此書籍已被預訂或已售出。");
    }
    $stmt_check_book->close();

    // 2. 插入新的交易記錄
    $stmt_insert_transaction = $conn->prepare("INSERT INTO transactions (book_id, buyer_id, seller_id, price, status) VALUES (?, ?, ?, ?, 'pending')");
    if (!$stmt_insert_transaction) {
        throw new Exception("準備插入交易語句失敗: " . $conn->error);
    }
    $stmt_insert_transaction->bind_param("iiid", $book_id, $buyer_id, $seller_id, $price); // 'd' for decimal/double
    if (!$stmt_insert_transaction->execute()) {
        throw new Exception("插入交易記錄失敗: " . $stmt_insert_transaction->error);
    }
    $stmt_insert_transaction->close();

    // 3. 更新書籍狀態為 '預訂'
    $stmt_update_book_status = $conn->prepare("UPDATE book SET Transaction_status = '預訂' WHERE Book_ID = ?");
    if (!$stmt_update_book_status) {
        throw new Exception("準備更新書籍狀態語句失敗: " . $conn->error);
    }
    $stmt_update_book_status->bind_param("i", $book_id);
    if (!$stmt_update_book_status->execute()) {
        throw new Exception("更新書籍狀態失敗: " . $stmt_update_book_status->error);
    }
    $stmt_update_book_status->close();

    // 提交事務
    $conn->commit();
    $response['success'] = true;
    $response['message'] = '下單成功，書籍已預訂！';

} catch (Exception $e) {
    // 回滾事務
    $conn->rollback();
    $response['message'] = $e->getMessage();
    error_log("Place order error: " . $e->getMessage()); // 記錄錯誤到伺服器日誌
}

mysqli_close($conn);
echo json_encode($response);
?>