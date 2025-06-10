<?php
session_start();
include 'db_connection.php'; // 確保這個檔案的路徑正確

header('Content-Type: application/json');

$response = ['success' => false, 'transactions' => [], 'message' => ''];

$currentUser = $_SESSION['currentUser'] ?? null;
if (!$currentUser) {
    $response['message'] = '請先登入！';
    echo json_encode($response);
    exit();
}

$userID = $currentUser['id'];
$userRole = $currentUser['role'];
$statusFilter = $_GET['status'] ?? 'all'; // 從前端獲取篩選狀態

// 構建 SQL 查詢來獲取交易記錄
// 這裡的邏輯需要根據你的交易表結構來設計
// 假設你有一個 `transactions` 表，包含 `transaction_id`, `book_id`, `buyer_id`, `seller_id`, `status`, `price`, `timestamp` 等
// 並且 book 表有 Book_title, buyer 表有 B_account, seller 表有 S_account

$sql = "
    SELECT
        t.transaction_id,
        t.book_id,
        t.buyer_id,
        t.seller_id,
        t.status,
        t.price,
        t.timestamp AS transaction_date,
        b.Book_title,
        buy.B_account AS buyer_account,
        sell.S_account AS seller_account
    FROM
        transactions t
    JOIN
        book b ON t.book_id = b.Book_ID
    JOIN
        buyer buy ON t.buyer_id = buy.B_ID
    JOIN
        seller sell ON t.seller_id = sell.S_ID
    WHERE
        (t.buyer_id = ? AND ? = 'buyer') OR (t.seller_id = ? AND ? = 'seller')
";

$params = [$userID, $userRole, $userID, $userRole];
$types = "isis"; // userID (int), userRole (string), userID (int), userRole (string)

// 添加狀態篩選條件
if ($statusFilter !== 'all') {
    $sql .= " AND t.status = ?";
    $params[] = $statusFilter;
    $types .= "s"; // status (string)
}

$sql .= " ORDER BY t.timestamp DESC"; // 按照時間降序排序

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param($types, ...$params); // 使用 ...$params 來展開陣列作為參數

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        $response['success'] = true;
        $response['transactions'] = $transactions;
    } else {
        $response['message'] = '執行查詢失敗: ' . $stmt->error;
        error_log("Error fetching transactions: " . $stmt->error);
    }
    $stmt->close();
} else {
    $response['message'] = '準備查詢失敗: ' . $conn->error;
    error_log("Error preparing transactions query: " . $conn->error);
}

mysqli_close($conn);
echo json_encode($response);
?>