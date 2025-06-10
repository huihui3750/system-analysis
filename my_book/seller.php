<?php
session_start(); // 啟動會話
include 'db_connection.php'; // 確保這個檔案存在並能正確連接資料庫

$currentUser = $_SESSION['currentUser'] ?? null;

// 如果沒有登入，重定向到登入頁面並顯示提示
if (!$currentUser) {
    echo "<script>alert('請先登入！'); window.location.href='login.html';</script>";
    exit();
}

// 確保只有 'seller' 角色可以訪問此頁面
if ($currentUser['role'] !== 'seller') {
    echo "<script>alert('只有賣家才能管理商品！'); window.location.href='index.php';</script>";
    exit();
}

$seller_id = $currentUser['id']; // 獲取當前登入賣家的 ID
$books = []; // 用於儲存從資料庫查詢到的書籍

// 從資料庫獲取當前賣家上傳的書籍
// **關鍵修改：在 SELECT 語句中加入 Transaction_status 欄位**
$sql = "SELECT Book_ID, Book_title, Book_author, Book_version, Book_department, Book_price, Book_status, Book_image_path, Book_remark, Transaction_status FROM book WHERE S_ID = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // 錯誤處理：預處理語句失敗
    error_log("seller.php prepare failed: " . $conn->error);
    echo "<p>無法載入書籍資料，請稍後再試。</p>";
    exit();
}

$stmt->bind_param("i", $seller_id); // 綁定賣家 ID
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

$stmt->close();
$conn->close(); // 在這裡關閉資料庫連接
?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>我的商品管理</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        /* 為 seller.php 特有的一些樣式調整，如果需要的話 */
        .book-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
        }

        .book-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            text-align: center;
            transition: transform 0.2s;
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .book-card:hover {
            transform: translateY(-5px);
        }

        .book-card h3 {
            color: #3f51b5;
            margin-bottom: 10px;
            font-size: 1.2em;
        }

        .book-card p {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 5px;
        }

        .book-image {
            max-width: 100%;
            height: 150px; /* 固定高度 */
            object-fit: cover; /* 裁剪圖片以填滿 */
            border-radius: 4px;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .card-actions {
            margin-top: 15px;
            display: flex;
            justify-content: space-around;
            gap: 10px;
        }

        .card-actions button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s;
            flex-grow: 1; /* 讓按鈕平均分佈 */
        }

        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }

        .edit-btn:hover {
            background-color: #45a049;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .transaction-status {
            font-weight: bold;
            margin-top: 10px;
            padding: 5px;
            border-radius: 3px;
        }
        <style>
        /* 您的 CSS 樣式保持不變 */
        body {
            margin: 0;
            font-family: 'Noto Sans TC', sans-serif;
            background: #f5f5f5;
        }

        header {
            background: #3f51b5;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo-title {
            font-size: 1.5rem;
            font-weight: bold;
        }

        nav a {
            color: white;
            margin: 0 10px;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #ffeb3b;
        }

        .user-status {
            display: flex;
            align-items: center;
        }

        .user-status span {
            margin-right: 15px;
            font-weight: bold;
        }

        .user-status .user-link {
            background-color: #5c6bc0;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .user-status .user-link:hover {
            background-color: #7986cb;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #3f51b5;
            font-size: 2rem;
        }

        .book-list {
            max-width: 900px;
            margin: 20px auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 10px;
        }

        .book-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .book-card img {
            max-width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .book-card h3 {
            margin: 0;
            color: #3f51b5;
            font-size: 1.3rem;
            text-align: center;
        }

        .book-card p {
            margin: 5px 0;
            color: #555;
            font-size: 0.95rem;
        }

        .book-card .price {
            font-weight: bold;
            color: #e91e63;
            font-size: 1.1rem;
        }

        .book-card .status {
            font-style: italic;
            color: #666;
        }

        .book-card .remark {
            font-size: 0.85rem;
            color: #777;
        }

        .book-card .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            justify-content: center;
        }

        .book-card button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.95rem;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .book-card .edit-btn {
            background-color: #2196F3;
            color: white;
        }

        .book-card .edit-btn:hover {
            background-color: #1976D2;
            transform: translateY(-2px);
        }

        .book-card .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .book-card .delete-btn:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
        }

        .no-books-message {
            text-align: center;
            margin-top: 50px;
            font-size: 1.2rem;
            color: #777;
        }
        footer {
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            background-color: #eee;
            color: #666;
            font-size: 0.9rem;
            border-top: 1px solid #ddd;
        }
    </style>
    </style>
</head>

<body>
    <header>
        <div class="logo-title">
            <h1>📚 校園二手書交易平台</h1>
        </div>
        <nav>
            <a href="index.php">首頁</a>
            <a href="profile.html">個人資料</a>
            <a href="upload.php">上傳書籍</a>
            <a href="seller.php">我的商品管理</a>
            <a href="communicate.php">溝通</a>
            <a href="TransactionRecords.php">交易紀錄</a>
            <a href="evaluate.html">評價</a>
            <div class="user-status">
                <span id="accountNameDisplay" style="display: none;"></span>
                <a href="profile.html" id="profileLink" class="user-link" style="display: none;">個人資料</a>
                <a href="login.html" id="loginLink" class="user-link">登入</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="seller-dashboard">
            <h2>我的商品</h2>
            <div id="sellerBooksContainer" class="book-list">
                <?php if (empty($books)): ?>
                    <p>您目前沒有上傳任何書籍。</p>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <div class="book-card">
                            <img src="<?php echo htmlspecialchars($book['Book_image_path']); ?>" alt="<?php echo htmlspecialchars($book['Book_title']); ?>" class="book-image">
                            <h3><?php echo htmlspecialchars($book['Book_title']); ?></h3>
                            <p>作者: <?php echo htmlspecialchars($book['Book_author']); ?></p>
                            <p>版本: <?php echo htmlspecialchars($book['Book_version']); ?></p>
                            <p>系級: <?php echo htmlspecialchars($book['Book_department']); ?></p>
                            <p>價格: $<?php echo htmlspecialchars($book['Book_price']); ?></p>
                            <p>書況: <?php echo htmlspecialchars($book['Book_status']); ?></p>
                            <p>備註: <?php echo htmlspecialchars($book['Book_remark']); ?></p>
                            <p class="transaction-status" style="background-color: <?php
                                if ($book['Transaction_status'] === '未售出') {
                                    echo '#e0ffe0'; // 淺綠
                                } else if ($book['Transaction_status'] === '預訂') {
                                    echo '#fffbe0'; // 淺黃
                                } else if ($book['Transaction_status'] === '已完成') {
                                    echo '#ffe0e0'; // 淺紅
                                } else {
                                    echo '#f0f0f0'; // 灰色
                                }
                            ?>; color: <?php
                                if ($book['Transaction_status'] === '未售出') {
                                    echo '#28a745'; // 深綠
                                } else if ($book['Transaction_status'] === '預訂') {
                                    echo '#ffc107'; // 深黃
                                } else if ($book['Transaction_status'] === '已完成') {
                                    echo '#dc3545'; // 深紅
                                } else {
                                    echo '#6c757d'; // 深灰
                                }
                            ?>;">交易狀態: <?php echo htmlspecialchars($book['Transaction_status']); ?></p>
                            <div class="card-actions">
                                <button class="edit-btn" data-book-id="<?php echo htmlspecialchars($book['Book_ID']); ?>">編輯</button>
                                <button class="delete-btn" data-book-id="<?php echo htmlspecialchars($book['Book_ID']); ?>">下架</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 校園二手書交易平台</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 處理導航欄登入狀態顯示
            const accountNameDisplay = document.getElementById("accountNameDisplay");
            const profileLink = document.getElementById("profileLink");
            const loginLink = document.getElementById("loginLink");

            // 從 localStorage 獲取當前登入的使用者資訊
            const currentUser = JSON.parse(localStorage.getItem("currentUser"));

            if (currentUser && currentUser.account) {
                accountNameDisplay.textContent = `歡迎，${currentUser.account}`;
                accountNameDisplay.style.display = "inline";
                profileLink.style.display = "inline-block";
                loginLink.style.display = "none";
            } else {
                accountNameDisplay.style.display = "none";
                profileLink.style.display = "none";
                loginLink.style.display = "inline-block";
            }

            // 處理編輯和刪除按鈕的事件委派
            const sellerBooksContainer = document.getElementById('sellerBooksContainer');
            sellerBooksContainer.addEventListener('click', function(event) {
                const target = event.target;
                const bookId = target.dataset.bookId;

                if (target.classList.contains('edit-btn')) {
                    alert(`編輯書籍 ID: ${bookId}`);
                    // 例如：window.location.href = `edit_book.php?id=${bookId}`;
                } else if (target.classList.contains('delete-btn')) {
                    if (confirm(`確定要下架書籍 ID: ${bookId} 嗎？此操作不可恢復！`)) {
                        deleteBook(bookId);
                    }
                }
            });

            function deleteBook(bookId) {
                fetch('delete_book.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `book_id=${bookId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('書籍已成功下架！');
                        location.reload();
                    } else {
                        alert('下架失敗: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('下架過程中發生錯誤！');
                });
            }
        });
    </script>
</body>

</html>