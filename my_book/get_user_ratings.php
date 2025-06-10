<?php
session_start();
include 'db_connection.php'; // 資料庫連線檔

header('Content-Type: application/json');

$response = ['success' => false, 'ratings' => [], 'message' => ''];

$currentUser = $_SESSION['currentUser'] ?? null;
if (!$currentUser) {
    $response['message'] = '請先登入！';
    echo json_encode($response);
    exit();
}

$userID = $currentUser['id'];
$userRole = $currentUser['role'];

// ------------------ 1. 待評價清單：買家評價賣家 ------------------
if ($userRole === 'buyer') {
    $pending_buyer_sql = "
        SELECT
            t.transaction_id AS transactionID,
            t.book_id AS bookID,
            b.Book_title AS bookTitle,
            s.S_account AS sellerAccount,
            t.timestamp AS transaction_date
        FROM transactions t
        JOIN book b ON t.book_id = b.Book_ID
        JOIN seller s ON t.seller_id = s.S_ID
        WHERE
            t.buyer_id = ? AND t.status = 'completed'
            AND NOT EXISTS (
                SELECT 1 FROM evaluations e
                WHERE e.transaction_id = t.transaction_id
                AND e.rater_id = ? AND e.rated_user_id = t.seller_id AND e.rated_user_role = 'seller'
            )
    ";
    $stmt = $conn->prepare($pending_buyer_sql);
    if ($stmt) {
        $stmt->bind_param("ii", $userID, $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row['type'] = 'pending_to_rate_seller';
            $response['ratings'][] = $row;
        }
        $stmt->close();
    } else {
        $response['message'] = "查詢待評價賣家交易失敗：" . $conn->error;
        echo json_encode($response); exit();
    }
}

// ------------------ 2. 待評價清單：賣家評價買家 ------------------
if ($userRole === 'seller') {
    $pending_seller_sql = "
        SELECT
            t.transaction_id AS transactionID,
            t.book_id AS bookID,
            b.Book_title AS bookTitle,
            buy.B_account AS buyerAccount,
            t.timestamp AS transaction_date
        FROM transactions t
        JOIN book b ON t.book_id = b.Book_ID
        JOIN buyer buy ON t.buyer_id = buy.B_ID
        WHERE
            t.seller_id = ? AND t.status = 'completed'
            AND NOT EXISTS (
                SELECT 1 FROM evaluations e
                WHERE e.transaction_id = t.transaction_id
                AND e.rater_id = ? AND e.rated_user_id = t.buyer_id AND e.rated_user_role = 'buyer'
            )
    ";
    $stmt = $conn->prepare($pending_seller_sql);
    if ($stmt) {
        $stmt->bind_param("ii", $userID, $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row['type'] = 'pending_to_rate_buyer';
            $response['ratings'][] = $row;
        }
        $stmt->close();
    } else {
        $response['message'] = "查詢待評價買家交易失敗：" . $conn->error;
        echo json_encode($response); exit();
    }
}

// ------------------ 3. 已收到評價：作為賣家 ------------------
$received_as_seller_sql = "
    SELECT
        e.evaluation_id,
        e.rating AS stars,
        e.comment,
        e.evaluation_date AS timestamp,
        b.Book_title AS bookTitle,
        buy.B_account AS raterAccount,
        e.rater_id AS raterID,
        e.rated_user_id AS ratedID,
        t.transaction_id AS transactionID,
        t.timestamp AS transaction_date
    FROM evaluations e
    JOIN transactions t ON e.transaction_id = t.transaction_id
    JOIN book b ON t.book_id = b.Book_ID
    LEFT JOIN buyer buy ON e.rater_id = buy.B_ID
    WHERE e.rated_user_id = ? AND e.rated_user_role = 'seller'
    ORDER BY e.evaluation_date DESC
";
$stmt = $conn->prepare($received_as_seller_sql);
if ($stmt) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['type'] = 'received';
        $response['ratings'][] = $row;
    }
    $stmt->close();
} else {
    $response['message'] = "查詢作為賣家收到的評價失敗：" . $conn->error;
    echo json_encode($response); exit();
}

// ------------------ 4. 已收到評價：作為買家 ------------------
$received_as_buyer_sql = "
    SELECT
        e.evaluation_id,
        e.rating AS stars,
        e.comment,
        e.evaluation_date AS timestamp,
        b.Book_title AS bookTitle,
        s.S_account AS raterAccount,
        e.rater_id AS raterID,
        e.rated_user_id AS ratedID,
        t.transaction_id AS transactionID,
        t.timestamp AS transaction_date
    FROM evaluations e
    JOIN transactions t ON e.transaction_id = t.transaction_id
    JOIN book b ON t.book_id = b.Book_ID
    LEFT JOIN seller s ON e.rater_id = s.S_ID
    WHERE e.rated_user_id = ? AND e.rated_user_role = 'buyer'
    ORDER BY e.evaluation_date DESC
";
$stmt = $conn->prepare($received_as_buyer_sql);
if ($stmt) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['type'] = 'received';
        $response['ratings'][] = $row;
    }
    $stmt->close();
} else {
    $response['message'] = "查詢作為買家收到的評價失敗：" . $conn->error;
    echo json_encode($response); exit();
}

// ------------------ 回傳結果 ------------------
$conn->close();
$response['success'] = true;
echo json_encode($response);
?>
