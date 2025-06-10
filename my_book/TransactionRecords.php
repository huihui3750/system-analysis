<?php
session_start();
include 'db_connection.php'; // 確保包含資料庫連線檔案

// 啟用錯誤報告，開發階段很有用
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 確保用戶已登入
if (!isset($_SESSION['currentUser'])) {
    echo "<script>alert('請先登入！'); window.location.href='login.html';</script>";
    exit();
}

$currentUser = $_SESSION['currentUser'];
$userID = $currentUser['id'];
$userAccount = htmlspecialchars($currentUser['account']);
$userRole = $currentUser['role'];
$userName = htmlspecialchars($currentUser['name'] ?? $userAccount); // 優先使用 name，如果沒有則用 account

$transactions = []; // 用於儲存從資料庫讀取的交易數據

// 檢查資料庫連線是否成功
if ($conn && !$conn->connect_error) {
    $sql = "";
    $params = [];
    $types = "";

    // 基礎 SQL 查詢，包含 JOIN 以獲取書籍標題、作者、圖片路徑，以及買家/賣家帳號
    // 新增 LEFT JOIN evaluations 來判斷是否已評價
    $sql = "
        SELECT
            t.transaction_id,
            t.book_id,
            t.buyer_id,
            b.B_account AS buyer_account,
            b.B_name AS buyer_name,
            t.seller_id,
            s.S_account AS seller_account,
            s.S_name AS seller_name,
            t.status,
            t.price,
            t.timestamp AS transaction_date,
            bk.Book_title,
            bk.Book_author,
            bk.Book_image_path,
            bk.Book_price AS original_book_price,
            bk.Book_status AS book_condition,
            e.evaluation_id IS NOT NULL AS has_rated_seller, -- 檢查買家是否已評價賣家
            e2.evaluation_id IS NOT NULL AS has_rated_buyer -- 檢查賣家是否已評價買家
        FROM
            transactions t
        LEFT JOIN
            buyer b ON t.buyer_id = b.B_ID
        LEFT JOIN
            seller s ON t.seller_id = s.S_ID
        JOIN
            book bk ON t.book_id = bk.Book_ID
        LEFT JOIN
            evaluations e ON t.transaction_id = e.transaction_id AND e.rater_id = ? AND e.rated_id = t.seller_id
        LEFT JOIN
            evaluations e2 ON t.transaction_id = e2.transaction_id AND e2.rater_id = ? AND e2.rated_id = t.buyer_id
        WHERE
            t.buyer_id = ? OR t.seller_id = ?
        ORDER BY
            t.timestamp DESC;
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iiii", $userID, $userID, $userID, $userID); // 綁定參數
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        $stmt->close();
    } else {
        error_log("SQL prepare failed: " . $conn->error);
    }
} else {
    error_log("Database connection failed: " . $conn->connect_error);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的交易紀錄</title>
    <link rel="stylesheet" href="css/normalize.css">
    <style>
        /* 全局樣式調整 */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
        }

        /* Header 樣式 - 與 upload.php 保持一致 */
        header {
            background-color: #3f51b5; /* upload.php 的藍色 */
            color: white;
            padding: 15px 0; /* 垂直內邊距，水平由 .container 控制 */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* 陰影效果 */
        }

        header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px; /* 為 .container 添加水平內邊距 */
        }

        header h1 {
            margin: 0;
            font-size: 24px; /* 與 upload.php 的 h1 字體大小一致 */
            color: white;
        }

        header nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px; /* 導航連結間距 */
        }

        header nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 5px 10px; /* 與 upload.php 的連結內邊距一致 */
            border-radius: 5px;
            transition: background-color 0.3s ease;
            white-space: nowrap;
        }

        header nav ul li a:hover {
            background-color: #5c6bc0; /* 與 upload.php 的懸停效果一致 */
        }

        /* 導航欄中活動連結樣式 (我的交易應該是活動的) */
        header nav ul li.active-nav-link a {
            background-color: #5c6bc0; /* 活動連結使用懸停色或更深的顏色 */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* 主要內容容器 */
        .main-container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.2em;
            letter-spacing: 1px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        /* 表格樣式 */
        .transaction-table-container {
            overflow-x: auto; /* 允許表格在小螢幕上水平滾動 */
        }

        .transaction-table {
            width: 100%;
            border-collapse: collapse; /* 合併邊框 */
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden; /* 確保圓角應用到內容 */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); /* 表格整體陰影 */
        }

        .transaction-table thead {
            background-color: #0056b3; /* 交易紀錄表格頭的藍色 */
            color: white;
        }

        .transaction-table th {
            padding: 15px;
            text-align: left;
            font-weight: bold;
            white-space: nowrap; /* 防止標題換行 */
            font-size: 1.1em; /* 稍微加大表頭文字 */
        }

        .transaction-table tbody tr {
            border-bottom: 1px solid #e0e0e0; /* 行底部分隔線，稍微加深 */
        }

        .transaction-table tbody tr:last-child {
            border-bottom: none; /* 最後一行沒有底線 */
        }

        .transaction-table tbody tr:hover {
            background-color: #e6f7ff; /* 行懸停效果，更清淡的藍色 */
        }

        .transaction-table td {
            padding: 12px 15px;
            vertical-align: middle; /* 垂直居中 */
            font-size: 0.95em;
            color: #444;
        }

        /* 圖片在表格中的樣式 */
        .book-thumbnail {
            width: 50px; /* 縮圖大小 */
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            vertical-align: middle;
            margin-right: 10px;
        }

        /* 狀態標籤 */
        .status {
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 5px;
            font-size: 0.8em;
            color: #fff;
            display: inline-block;
            white-space: nowrap;
        }
        .status.pending { background-color: #ffc107; }
        .status.shipped { background-color: #17a2b8; }
        .status.completed { background-color: #28a745; }
        .status.cancelled { background-color: #dc3545; }

        /* 按鈕樣式 */
        .action-button, .evaluate-button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85em;
            font-weight: bold;
            transition: background-color 0.3s ease;
            white-space: nowrap;
            margin: 2px 0;
            display: inline-block;
        }

        .action-button:hover, .evaluate-button:hover {
            opacity: 0.9; /* 懸停時輕微變暗 */
        }

        .action-button.confirm-ship {
            background-color: #007bff;
            color: white;
        }
        .action-button.confirm-ship:hover {
            background-color: #0056b3;
        }

        .action-button.confirm-complete {
            background-color: #28a745;
            color: white;
        }
        .action-button.confirm-complete:hover {
            background-color: #218838;
        }

        .evaluate-button {
            background-color: #ffc107;
            color: #343a40;
        }
        .evaluate-button:hover {
            background-color: #e0a800;
        }
        .evaluate-button:disabled {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .status-message {
            color: #888;
            font-style: italic;
            font-size: 0.85em;
            white-space: nowrap;
        }

        .no-records {
            text-align: center;
            color: #777;
            padding: 40px;
            border: 1px dashed #ddd;
            border-radius: 8px;
            font-size: 1.1em;
            background-color: #fcfcfc;
        }

        /* Footer 樣式 */
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            font-size: 0.9em;
        }

        /* 響應式設計 */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding: 0 15px; /* 小螢幕下調整容器內邊距 */
            }
            header nav ul {
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
                gap: 10px;
            }
            header nav ul li {
                flex-grow: 1;
                text-align: center;
            }
            .main-container {
                width: 95%;
                padding: 15px;
            }
            .transaction-table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .transaction-table {
                display: block;
                width: max-content;
            }
            .transaction-table th, .transaction-table td {
                padding: 8px 10px;
                font-size: 0.9em;
            }
            .action-button, .evaluate-button {
                display: block;
                width: auto;
                margin: 5px 0;
            }
            .status-message {
                display: block;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>二手書交易平台</h1>
            <nav>
                <ul>
                    <li><a href="index.php">首頁</a></li>
                    <li><a href="upload.php">刊登書籍</a></li>
                    <li><a href="messages.php">訊息</a></li>
                    <li><a href="profile.php">個人中心</a></li>
                    <li class="active-nav-link"><a href="TransactionRecords.php">我的交易</a></li> <?php if (isset($_SESSION['currentUser'])): ?>
                        <li><a href="logout.php">登出</a></li>
                    <?php else: ?>
                        <li><a href="login.html">登入</a></li>
                        <li><a href="register.html">註冊</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="main-container">
            <h2>我的交易紀錄</h2>
            <div class="transaction-table-container">
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>書籍</th>
                            <th>交易ID</th>
                            <th>買家</th>
                            <th>賣家</th>
                            <th>價格</th>
                            <th>狀態</th>
                            <th>日期</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="transactionTableBody">
                        </tbody>
                </table>
            </div>
            <?php if (empty($transactions)): ?>
                <p class="no-records">目前沒有任何交易紀錄。</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 二手書交易平台. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const allTransactions = <?php echo json_encode($transactions); ?>;
        const currentUserID = <?php echo json_encode($userID); ?>;
        const currentUserRole = <?php echo json_encode($userRole); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            renderTransactions(allTransactions);
        });

        function renderTransactions(transactions) {
            const tbody = document.getElementById('transactionTableBody');
            tbody.innerHTML = ''; // 清空現有內容

            if (transactions.length === 0) {
                // 如果沒有交易，則頁面會顯示 PHP 渲染的 no-records 訊息
                return;
            }

            transactions.forEach(record => {
                const tr = document.createElement('tr');

                const isBuyer = (currentUserID == record.buyer_id);
                const isSeller = (currentUserID == record.seller_id);

                let buyerName = record.buyer_name || record.buyer_account || '未知買家';
                let sellerName = record.seller_name || record.seller_account || '未知賣家';
                let displayCounterparty = isBuyer ? sellerName : buyerName;

                const defaultImagePath = 'path/to/default_book_image.jpg'; // 設置預設圖片路徑
                const imageUrl = record.Book_image_path ? record.Book_image_path : defaultImagePath;

                let actionHtml = '';

                if (isSeller && record.status === 'pending') {
                    actionHtml = `<button class="action-button confirm-ship" onclick="completeTransaction(${record.transaction_id}, ${record.book_id}, 'shipped')">確認出貨</button>`;
                } else if (isBuyer && record.status === 'shipped') {
                    actionHtml = `<button class="action-button confirm-complete" onclick="completeTransaction(${record.transaction_id}, ${record.book_id}, 'completed')">確認收貨</button>`;
                } else if (record.status === 'completed') {
                    if (isBuyer && !record.has_rated_seller) {
                        // 買家評價賣家
                        actionHtml = `<button class="evaluate-button" onclick="window.location.href='evaluate.php?transaction_id=${record.transaction_id}&rater_id=${currentUserID}&rated_user_id=${record.seller_id}&rated_user_role=seller'">評價賣家</button>`;
                    } else if (isSeller && !record.has_rated_buyer) {
                        // 賣家評價買家
                        actionHtml = `<button class="evaluate-button" onclick="window.location.href='evaluate.php?transaction_id=${record.transaction_id}&rater_id=${currentUserID}&rated_user_id=${record.buyer_id}&rated_user_role=buyer'">評價買家</button>`;
                    } else {
                        actionHtml = '<span class="status-message">已完成</span>';
                    }
                } else if (record.status === 'cancelled') {
                    actionHtml = '<span class="status-message">已取消</span>';
                } else {
                     actionHtml = '<span class="status-message">無操作</span>';
                }

                tr.innerHTML = `
                    <td>
                        <img src="${imageUrl}" alt="${record.Book_title}" class="book-thumbnail">
                        ${record.Book_title}
                    </td>
                    <td>${record.transaction_id}</td>
                    <td>${buyerName}</td>
                    <td>${sellerName}</td>
                    <td>$${record.price}</td>
                    <td><span class="status ${record.status}">${record.status}</span></td>
                    <td>${new Date(record.transaction_date).toLocaleString()}</td>
                    <td>${actionHtml}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        // completeTransaction 函數 (保持不變)
        function completeTransaction(transactionId, bookId, targetStatus) {
            fetch('complete_transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    transaction_id: transactionId,
                    book_id: bookId,
                    target_status: targetStatus
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload(); // 最簡單的方法是直接重新載入頁面
                } else {
                    alert('操作失敗: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('操作時發生錯誤！');
            });
        }
    </script>
</body>
</html>