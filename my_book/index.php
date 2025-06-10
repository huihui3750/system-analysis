<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$currentUser = $_SESSION['currentUser'] ?? null;
$currentUserID = $currentUser['id'] ?? null;
$userRole = $currentUser['role'] ?? null;
$userName = htmlspecialchars($currentUser['name'] ?? $currentUser['account'] ?? '訪客'); // 獲取用戶名稱或帳號，如果未登入則為訪客

include 'db_connection.php';

$books_data = [];

if ($conn && !$conn->connect_error) {
    // 只顯示 Transaction_status 為 '未售出' 的書籍
    $sql = "SELECT Book_ID, Book_title, Book_author, Book_version, Book_department, Book_price, Book_status, Book_remark, S_ID, Book_image_path, Transaction_status 
            FROM book 
            WHERE Transaction_status = '未售出'";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $books_data[] = $row;
        }
        mysqli_free_result($result);
    } else {
        error_log("Error fetching books: " . mysqli_error($conn));
    }
} else {
    error_log("Database connection was not established correctly in index.php.");
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 校園二手書交易平台 - 首頁</title>
    <link rel="stylesheet" href="css/normalize.css">
    <style>
        /* 全局樣式調整，確保 body 無內外邊距干擾 */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6; /* 頁面背景色 */
        }

        /* Header 樣式 - 與 upload.php 保持一致，並調整標題和導覽列佈局 */
        header {
            background-color: #3f51b5; /* upload.php 的藍色 */
            color: white; /* 確保 header 內所有文字都是白色 */
            padding: 15px 0; /* 垂直內邊距，水平由 .container 控制 */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* 陰影效果 */
        }

        header .container {
            display: flex;
            justify-content: space-between; /* 標題和導覽列分開左右兩邊 */
            align-items: center;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px; /* 為 .container 添加水平內邊距 */
        }

        header h1 {
            margin: 0;
            font-size: 28px; /* 放大標題字體 */
            color: white; /* 確保標題文字為白色 */
            text-align: left; /* 靠左對齊 */
            flex-shrink: 0; /* 防止標題縮小 */
        }

        header nav {
            display: flex;
            align-items: center; /* 確保導覽列內的項目垂直居中 */
        }

        header nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 20px; /* 導航連結間距 */
        }

        header nav ul li a {
            color: white; /* 導航連結文字為白色 */
            text-decoration: none;
            font-weight: bold;
            padding: 5px 10px; /* 與 upload.php 的連結內邊距一致 */
            border-radius: 5px;
            transition: background-color 0.3s ease;
            white-space: nowrap; /* 防止連結文字換行 */
        }

        header nav ul li a:hover {
            background-color: #5c6bc0; /* 與 upload.php 的懸停效果一致 */
        }

        /* 導航欄中活動連結樣式 (首頁應該是活動的) */
        header nav ul li.active-nav-link a {
            background-color: #5c6bc0; /* 活動連結使用懸停色或更深的顏色 */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .user-info {
            color: white;
            font-weight: bold;
            margin-left: 20px; /* 與導航列的間距 */
            white-space: nowrap; /* 防止用戶名換行 */
        }
        
        .logout-btn {
            background-color: #f44336; /* 紅色登出按鈕 */
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: bold;
            margin-left: 10px; /* 與用戶名間距 */
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #d32f2f;
        }

        /* 搜索框樣式 */
        .search-bar {
            width: 90%;
            max-width: 600px;
            margin: 20px auto;
            display: flex;
            background-color: #fff;
            border-radius: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .search-bar input[type="text"] {
            flex-grow: 1;
            border: none;
            padding: 12px 20px;
            font-size: 1em;
            outline: none;
        }

        .search-bar button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 0 25px 25px 0;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .search-bar button:hover {
            background-color: #0056b3;
        }

        /* 主要內容容器 */
        .main-content-area {
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

        /* 書籍列表容器 (網格佈局) */
        .book-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px; /* 書籍卡片之間的間距 */
            padding: 20px 0; /* 整個網格的內邊距 */
            justify-content: center; /* 如果卡片未填滿整行，居中顯示 */
            margin-top: 20px;
        }

        /* 書籍卡片樣式 */
        .book-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .book-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        .book-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .book-info h3 {
            font-size: 1.4em;
            color: #333;
            margin-top: 0;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .book-info p {
            font-size: 0.95em;
            color: #666;
            margin-bottom: 8px;
        }

        .book-info .price {
            font-size: 1.5em;
            color: #007bff;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .book-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 15px;
        }

        .buy-btn, .contact-seller-btn {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease;
            text-align: center;
            text-decoration: none;
            color: white;
        }

        .buy-btn {
            background-color: #28a745;
        }

        .buy-btn:hover {
            background-color: #218838;
        }

        .contact-seller-btn {
            background-color: #007bff;
        }

        .contact-seller-btn:hover {
            background-color: #0056b3;
        }

        .no-books-message {
            text-align: center;
            padding: 40px;
            font-size: 1.1em;
            color: #777;
            border: 1px dashed #ccc;
            border-radius: 8px;
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
        @media (max-width: 900px) {
            .book-list {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            }
        }

        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding: 0 15px; /* 小螢幕下調整容器內邊距 */
            }
            header nav {
                width: 100%;
                flex-direction: column; /* 導航列在小螢幕上垂直堆疊 */
                align-items: flex-start;
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
            .user-info {
                margin: 10px 0 0 0; /* 調整用戶信息間距 */
                width: 100%;
                text-align: center;
            }
            .logout-btn {
                margin: 5px auto 0 auto; /* 登出按鈕居中 */
                width: calc(100% - 20px); /* 讓按鈕寬度適應容器 */
            }
            .search-bar {
                width: 95%;
            }
            .main-content-area {
                width: 95%;
                padding: 15px;
                margin: 15px auto;
            }
            .book-list {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 15px;
            }
            .book-card img {
                height: 160px;
            }
        }

        @media (max-width: 480px) {
            header h1 {
                font-size: 24px; /* 更小螢幕下標題字體再縮小 */
            }
            header nav ul {
                flex-direction: column;
                gap: 5px;
            }
            .book-list {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .book-card img {
                height: 200px;
            }
            .search-bar {
                flex-direction: column;
            }
            .search-bar input[type="text"] {
                border-radius: 25px;
                margin-bottom: 10px;
            }
            .search-bar button {
                width: 100%;
                border-radius: 25px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📚 校園二手書交易平台</h1>
            <nav>
                <ul>
                    <li class="active-nav-link"><a href="index.php">首頁</a></li>
                    <li><a href="upload.php">上傳書籍</a></li>
                    <li><a href="messages.php">溝通</a></li>
                    <li><a href="profile.php">個人中心</a></li>
                    <li><a href="TransactionRecords.php">交易紀錄</a></li>
                    <li><a href="evaluate.php">評價</a></li> </ul>
                <?php if (isset($_SESSION['currentUser'])): ?>
                    <span class="user-info">歡迎, <?= $userName ?></span>
                    <button class="logout-btn" onclick="location.href='logout.php'">登出</button>
                <?php else: ?>
                    <ul>
                        <li><a href="login.html">登入</a></li>
                        <li><a href="register.html">註冊</a></li>
                    </ul>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="搜尋書籍名稱、作者、科系..." onkeyup="searchBooks()">
            <button onclick="searchBooks()">搜尋</button>
        </div>

        <div class="main-content-area">
            <h2>所有書籍</h2>
            <div id="bookList" class="book-list">
                </div>
            <?php if (empty($books_data)): ?>
                <p class="no-books-message">目前沒有可供出售的書籍。</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 校園二手書交易平台. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const allBooks = <?php echo json_encode($books_data); ?>;
        const currentUserID = <?php echo json_encode($currentUserID); ?>;
        const currentUserRole = <?php echo json_encode($userRole); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            renderBooks(allBooks);
        });

        function renderBooks(books) {
            const bookListDiv = document.getElementById('bookList');
            bookListDiv.innerHTML = ''; // 清空現有內容

            if (books.length === 0) {
                bookListDiv.innerHTML = '<p class="no-books-message">沒有找到符合條件的書籍。</p>';
                return;
            }

            books.forEach(book => {
                const bookCard = document.createElement('div');
                bookCard.className = 'book-card';

                const defaultImagePath = 'path/to/default_book_image.jpg'; // 設置預設圖片路徑，請替換為實際路徑
                const imageUrl = book.Book_image_path ? book.Book_image_path : defaultImagePath;

                let actionButtons = '';
                // 只有登入且不是賣家自己的書才能下單或聯絡
                if (currentUserID && book.S_ID !== currentUserID) {
                    actionButtons = `
                        <button class="buy-btn" data-book-id="${book.Book_ID}" data-seller-id="${book.S_ID}" data-price="${book.Book_price}">下單</button>
                        <a href="communicate.php?seller_id=${book.S_ID}&book_id=${book.Book_ID}" class="contact-seller-btn">聯絡賣家</a>
                    `;
                } else if (!currentUserID) {
                    // 未登入，提示登入
                    actionButtons = `
                        <button class="buy-btn disabled-btn" disabled>登入後下單</button>
                        <a href="login.html" class="contact-seller-btn">登入聯絡</a>
                    `;
                } else if (book.S_ID === currentUserID) {
                    // 是自己的書，顯示已上架
                    actionButtons = `
                        <button class="buy-btn disabled-btn" disabled>自己的書</button>
                        <button class="contact-seller-btn disabled-btn" disabled>已上架</button>
                    `;
                }

                bookCard.innerHTML = `
                    <img src="${imageUrl}" alt="${book.Book_title}">
                    <div class="book-info">
                        <h3>${book.Book_title}</h3>
                        <p><strong>作者:</strong> ${book.Book_author}</p>
                        <p><strong>科系:</strong> ${book.Book_department}</p>
                        <p><strong>版本:</strong> ${book.Book_version}</p>
                        <p><strong>狀況:</strong> ${book.Book_status}</p>
                        <p><strong>備註:</strong> ${book.Book_remark}</p>
                        <p class="price">$${book.Book_price}</p>
                        <div class="book-actions">
                            ${actionButtons}
                        </div>
                    </div>
                `;
                bookListDiv.appendChild(bookCard);
            });

            // 綁定下單按鈕事件
            document.querySelectorAll('.buy-btn:not(.disabled-btn)').forEach(button => {
                button.onclick = function() {
                    const bookId = this.dataset.bookId;
                    const sellerId = this.dataset.sellerId;
                    const price = this.dataset.price;
                    placeOrder(bookId, sellerId, price);
                };
            });
        }

        function placeOrder(bookId, sellerId, price) {
            if (!currentUserID) {
                alert('請先登入才能下單！');
                window.location.href = 'login.html';
                return;
            }
            if (currentUserID === parseInt(sellerId)) { // 確保 sellerId 是數字以便比較
                alert('您不能購買自己的書籍！');
                return;
            }

            if (!confirm(`確定要購買這本書嗎？價格：$${price}`)) {
                return;
            }

            fetch('place_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    book_id: bookId,
                    buyer_id: currentUserID,
                    seller_id: sellerId,
                    price: price
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("下單成功！您可以在交易紀錄中查看。");
                    window.location.href = "TransactionRecords.php";
                } else {
                    alert("下單失敗: " + data.message);
                }
            })
            .catch(error => {
                console.error("下單錯誤:", error);
                alert("無法完成下單！");
            });
        }

        function searchBooks() {
            const keyword = document.getElementById("searchInput").value.trim().toLowerCase();
            const filtered = allBooks.filter(book =>
                book.Book_title.toLowerCase().includes(keyword) ||
                book.Book_author.toLowerCase().includes(keyword) ||
                book.Book_department.toLowerCase().includes(keyword)
            );
            renderBooks(filtered);
        }
    </script>
</body>
</html>