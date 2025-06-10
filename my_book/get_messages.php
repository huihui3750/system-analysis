<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'messages' => [], 'message' => ''];

$currentUser = $_SESSION['currentUser'] ?? null;
if (!$currentUser) {
    $response['message'] = '請先登入！';
    echo json_encode($response);
    exit();
}

$userID = $currentUser['id'];
$userRole = $currentUser['role']; // 獲取當前用戶的角色

// 從 URL 參數獲取 `talk_to_id`，表示要獲取與哪個對象的聊天記錄
$talkToID = $_GET['talk_to_id'] ?? null;

if (empty($talkToID)) {
    $response['message'] = '缺少聊天對象ID。';
    echo json_encode($response);
    exit();
}

// 獲取與特定對象的聊天記錄
// 查詢 messages 表，並聯結 book 表以獲取書籍標題 (如果 book_id 存在)
// 同時查詢 sender 和 receiver 的帳號名稱
$sql = "
    SELECT
        m.message_id,
        m.sender_id,
        m.receiver_id,
        m.message_content,
        m.timestamp,
        m.book_id,
        b.Book_title AS book_title,
        CASE
            WHEN m.sender_id_role = 'buyer' THEN (SELECT B_account FROM buyer WHERE B_ID = m.sender_id)
            WHEN m.sender_id_role = 'seller' THEN (SELECT S_account FROM seller WHERE S_ID = m.sender_id)
            ELSE NULL
        END AS sender_account,
        CASE
            WHEN m.receiver_id_role = 'buyer' THEN (SELECT B_account FROM buyer WHERE B_ID = m.receiver_id)
            WHEN m.receiver_id_role = 'seller' THEN (SELECT S_account FROM seller WHERE S_ID = m.receiver_id)
            ELSE NULL
        END AS receiver_account
    FROM
        messages m
    LEFT JOIN
        book b ON m.book_id = b.Book_ID
    WHERE
        (m.sender_id = ? AND m.sender_id_role = ? AND m.receiver_id = ? AND m.receiver_id_role IN ('buyer', 'seller'))
        OR
        (m.sender_id = ? AND m.sender_id_role IN ('buyer', 'seller') AND m.receiver_id = ? AND m.receiver_id_role = ?)
    ORDER BY
        m.timestamp ASC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // 需要判斷 talkToID 的角色，這裡我們假設 talkToID 傳入的是對方 ID，需要透過查詢來獲取對方的角色
    // 這是一個更複雜的處理，為了簡化，我們暫時假設 talkToID 是單一的唯一 ID，且在 messages 表中 role 會被正確記錄。
    // 實際應用中，您可能需要額外查詢 talkToID 的角色。

    // 這裡的綁定參數應該與 SQL 語句中的問號一一對應
    // 我們需要傳遞 currentUser 的 ID 和 Role，以及 talkToID
    // 為了處理 sender_id_role 和 receiver_id_role 判斷，參數會變多
    // 更好的做法是明確傳遞 talkToID 的角色，或者在 messages 表中只存 ID，並在 JOIN 時判斷

    // 由於我們不知道 talkToID 是買家還是賣家，所以 IN ('buyer', 'seller') 會比較通用
    // 綁定參數： (userID, userRole, talkToID, userID, talkToID, userRole)
    $stmt->bind_param("isiisi",
        $userID, $userRole, $talkToID, // For first set of conditions (current user sending)
        $talkToID, $userID, $userRole  // For second set of conditions (current user receiving)
    );


    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $response['messages'][] = $row;
        }
        $response['success'] = true;
    } else {
        $response['message'] = '執行查詢失敗: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $response['message'] = '準備語句失敗: ' . $conn->error;
}

mysqli_close($conn);
echo json_encode($response);
?>