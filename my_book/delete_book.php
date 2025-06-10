<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json'); // 返回 JSON 格式響應

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_id = $_POST['book_id'] ?? null;
    $currentUser = $_SESSION['currentUser'] ?? null;

    if (!$currentUser || $currentUser['role'] !== 'seller') {
        $response['message'] = '未授權操作，請先登入賣家帳號。';
        echo json_encode($response);
        exit();
    }

    if (!$book_id) {
        $response['message'] = '缺少書籍ID。';
        echo json_encode($response);
        exit();
    }

    $seller_id = $currentUser['id'];

    // 為了安全起見，確保只有該賣家才能刪除自己的書籍
    $sql = "DELETE FROM book WHERE Book_ID = ? AND S_ID = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ii", $book_id, $seller_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = '書籍已成功下架。';
            } else {
                $response['message'] = '書籍不存在或您沒有權限刪除此書籍。';
            }
        } else {
            $response['message'] = '資料庫執行失敗: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = '準備語句失敗: ' . $conn->error;
    }
} else {
    $response['message'] = '無效的請求方法。';
}

$conn->close();
echo json_encode($response);
exit();
?>