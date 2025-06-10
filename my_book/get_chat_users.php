<?php
session_start();
include 'db_connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'chat_users' => [], 'message' => ''];

$currentUser = $_SESSION['currentUser'] ?? null;
if (!$currentUser) {
    $response['message'] = '請先登入！';
    echo json_encode($response);
    exit();
}

$userID = $currentUser['id'];

// 查詢所有與當前用戶有過對話的發送者和接收者
// 我們需要從 messages 表中找出所有與 userID 相關的 sender_id 和 receiver_id
// 然後根據這些 ID 去 buyer 或 seller 表中查找對應的帳號和 ID
$sql = "
    SELECT DISTINCT
        CASE
            WHEN m.sender_id = ? AND m.sender_id_role = ? THEN m.receiver_id
            WHEN m.receiver_id = ? AND m.receiver_id_role = ? THEN m.sender_id
            ELSE NULL
        END AS chat_partner_id,
        CASE
            WHEN m.sender_id = ? AND m.sender_id_role = ? THEN m.receiver_id_role
            WHEN m.receiver_id = ? AND m.receiver_id_role = ? THEN m.sender_id_role
            ELSE NULL
        END AS chat_partner_role
    FROM messages m
    WHERE (m.sender_id = ? AND m.sender_id_role = ?) OR (m.receiver_id = ? AND m.receiver_id_role = ?)
";

$stmt = $conn->prepare($sql);

// 注意：這裡假設 messages 表中也有 sender_id_role 和 receiver_id_role 欄位
// 如果您的 messages 表沒有這些欄位，則需要調整您的 messages 表結構
// 或者，如果您的 ID 是唯一的且不會在買家和賣家之間重複，則無需判斷 role

// 為了通用性，我們傳遞當前用戶的 ID 和 Role 進行匹配
// 'i' 表示 integer, 's' 表示 string
$stmt->bind_param("isisisisisis",
    $userID, $currentUser['role'],
    $userID, $currentUser['role'],
    $userID, $currentUser['role'],
    $userID, $currentUser['role'],
    $userID, $currentUser['role'],
    $userID, $currentUser['role']
);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $chat_partners = [];
    $processed_ids = []; // 用於避免重複的聊天對象

    while ($row = $result->fetch_assoc()) {
        $partner_id = $row['chat_partner_id'];
        $partner_role = $row['chat_partner_role'];

        if (!empty($partner_id) && !isset($processed_ids[$partner_id . '_' . $partner_role])) {
            $partner_account = '';
            // 根據角色查詢帳號
            if ($partner_role === 'buyer') {
                $sql_get_account = "SELECT B_account FROM buyer WHERE B_ID = ?";
            } else if ($partner_role === 'seller') {
                $sql_get_account = "SELECT S_account FROM seller WHERE S_ID = ?";
            } else {
                continue; // 跳過無效角色
            }

            $stmt_get_account = $conn->prepare($sql_get_account);
            if ($stmt_get_account) {
                $stmt_get_account->bind_param("i", $partner_id);
                $stmt_get_account->execute();
                $result_get_account = $stmt_get_account->get_result();
                if ($result_get_account->num_rows > 0) {
                    $partner_account = $result_get_account->fetch_assoc();
                    $chat_partners[] = [
                        'id' => $partner_id,
                        'account' => ($partner_role === 'buyer' ? $partner_account['B_account'] : $partner_account['S_account']),
                        'role' => $partner_role
                    ];
                    $processed_ids[$partner_id . '_' . $partner_role] = true;
                }
                $stmt_get_account->close();
            }
        }
    }
    $response['success'] = true;
    $response['chat_users'] = $chat_partners;
} else {
    $response['message'] = '查詢聊天對象失敗: ' . $conn->error;
}

$stmt->close();
mysqli_close($conn);

echo json_encode($response);
?>