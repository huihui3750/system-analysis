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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $currentUser['id'];
    $sender_role = $currentUser['role']; // 獲取發送者的角色
    $receiver_id = $_POST['receiver_id'] ?? null;
    $book_id = $_POST['book_id'] ?? null; // 從前端傳遞過來，如果聊天是針對某本書的
    $message_content = $_POST['message_content'] ?? '';

    if (empty($receiver_id) || empty($message_content)) {
        $response['message'] = '接收者ID和訊息內容不能為空。';
        echo json_encode($response);
        exit();
    }

    // 為了安全和正確性，需要查詢 receiver_id 對應的角色
    $receiver_role = null;
    // 嘗試從 buyer 表中查找
    $stmt_buyer_check = $conn->prepare("SELECT B_account FROM buyer WHERE B_ID = ?");
    if ($stmt_buyer_check) {
        $stmt_buyer_check->bind_param("i", $receiver_id);
        $stmt_buyer_check->execute();
        $result_buyer_check = $stmt_buyer_check->get_result();
        if ($result_buyer_check->num_rows > 0) {
            $receiver_role = 'buyer';
        }
        $stmt_buyer_check->close();
    }

    // 如果不是買家，嘗試從 seller 表中查找
    if (!$receiver_role) {
        $stmt_seller_check = $conn->prepare("SELECT S_account FROM seller WHERE S_ID = ?");
        if ($stmt_seller_check) {
            $stmt_seller_check->bind_param("i", $receiver_id);
            $stmt_seller_check->execute();
            $result_seller_check = $stmt_seller_check->get_result();
            if ($result_seller_check->num_rows > 0) {
                $receiver_role = 'seller';
            }
            $stmt_seller_check->close();
        }
    }

    if (!$receiver_role) {
        $response['message'] = '無效的接收者ID。';
        echo json_encode($response);
        exit();
    }


    // 插入訊息到資料庫
    // 假設 messages 表結構包含：message_id (PK, AUTO_INCREMENT), sender_id, receiver_id, book_id (可NULL), message_content, timestamp (DEFAULT CURRENT_TIMESTAMP), sender_id_role, receiver_id_role
    $sql = "INSERT INTO messages (sender_id, sender_id_role, receiver_id, receiver_id_role, book_id, message_content) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // 確保 book_id 是 int 或 null。如果從前端傳來的是空字符串，PHP 會把它當作 0 或 null。
        // MySQLi 的 bind_param 不直接支持 NULL，所以對於 NULLABLE 的 int 欄位，
        // 如果值是空的，我們可以用 NULL 來代替 (在 bind_param 時需要特殊處理，或者確保數據類型正確)
        // 這裡我們直接傳遞 $book_id，如果它是空字符串，MySQLi 會盡力轉換。
        // 更嚴謹的處理方式：
        $book_id_to_insert = is_numeric($book_id) && $book_id > 0 ? (int)$book_id : NULL;

        // ssis 是 string, string, int, string, 但 book_id 是 int, content 是 string
        // 所以應該是 i, s, i, s, i, s
        // sender_id (int), sender_id_role (string), receiver_id (int), receiver_id_role (string), book_id (int or NULL), message_content (string)
        // 對於 NULL 值，bind_param 的 'i' 類型會嘗試轉換為 0，這可能不是您想要的。
        // 如果 book_id 可以是 NULL，您需要修改 SQL 語句或處理綁定。
        // 最簡單的方法是：對於可以為 NULL 的 INT 欄位，您可以將其設為 NULL，並在 SQL 語句中處理。
        // 但 mysqli_stmt_bind_param 不接受 NULL。
        // 所以，如果 book_id 是 NULL，您可以考慮將其替換為 PHP 的 null，但綁定類型仍需為 'i' 或 's'。
        // 如果 $book_id_to_insert 為 null，而您綁定 'i'，PHP 會自動轉換為 0。
        // 解決方案：如果 book_id 可以是 NULL，則在 SQL 語句中將其對應的問號設為 NULL 或實際值。
        // 或者根據是否有 book_id 來構建不同的 SQL。
        
        // 考慮到簡潔性，如果 book_id 為空，就傳遞 NULL。
        // 在 MySQLi 中，對於 INT 類型，NULL 仍然可以綁定為 'i'，只要 PHP 變數是 NULL 即可。
        // 但舊版 PHP/MySQLi 可能有問題，為了相容性，可以這樣做：
        // 如果 $book_id_to_insert 是 NULL，傳遞 's' 然後在 SQL 中判斷。
        // 或者直接設置為 0 如果您希望 NULL 就是 0。

        // 這裡我將假設 book_id 可以是 NULL，並且 mysqli_bind_param 可以處理 NULL 值。
        // 如果遇到問題，需要將 $book_id_to_insert 的類型綁定為 's'，並在 SQL 中使用 CAST 或 IFNULL。

        $stmt->bind_param("isisis",
            $sender_id,
            $sender_role,
            $receiver_id,
            $receiver_role,
            $book_id_to_insert, // 如果是 NULL，這裡應該能正確處理
            $message_content
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = '訊息發送成功。';
        } else {
            $response['message'] = '執行插入失敗: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = '準備語句失敗: ' . $conn->error;
    }
} else {
    $response['message'] = '無效的請求方法。';
}

mysqli_close($conn);
echo json_encode($response);
?>