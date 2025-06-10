<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <title>評價</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* 基本樣式設定 */

        body {
            margin: 0;
            font-family: 'Noto Sans TC', sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        header {
            background: #3f51b5;
            color: white;
            padding: 15px 20px;
        }

        nav a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
        }

        h2 {
            text-align: center;
            margin-top: 30px;
            color: #3f51b5;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        /* 區塊標題 */

        h3 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-top: 30px;
            margin-bottom: 20px;
            color: #555;
        }
        /* 評價卡片樣式 */

        .rating-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .rating-card strong {
            color: #3f51b5;
        }

        .rating-card .stars {
            color: gold;
            font-size: 1.2em;
        }

        .rating-card textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            min-height: 60px;
            resize: vertical;
        }

        .rating-card button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 5px;
        }

        .rating-card .submit-rating-btn {
            background-color: #4CAF50;
            /* 綠色 */
            color: white;
        }

        .rating-card .submit-rating-btn:hover {
            background-color: #45a049;
        }

        .no-data-message {
            text-align: center;
            color: #777;
            padding: 20px;
        }
    </style>
</head>

<body>

    <header>
        <nav>
            <a href="index.php">首頁</a>
            <a href="profile.php">個人資料</a>
            <a href="upload.php">上傳書籍</a>
            <a href="seller.php">我的商品管理</a>
            <a href="communicate.php">溝通</a>
            <a href="TransactionRecords.php">交易紀錄</a>
            <a href="evaluation_dashboard.php">評價</a>
        </nav>
    </header>

    <h2>⭐ 評價中心</h2>

    <div class="container">
        <h3>待我評價的訂單</h3>
        <div id="pendingRatings" class="rating-list">
            <p class="no-data-message">目前沒有待評價的訂單。</p>
        </div>

        <h3>我收到的評價</h3>
        <div id="receivedRatings" class="rating-list">
            <p class="no-data-message">目前沒有收到任何評價。</p>
        </div>
    </div>

    <script>
        const currentUser = JSON.parse(localStorage.getItem("currentUser"));

        // 若尚未登入，導向登入頁面
        if (!currentUser) {
            alert("請先登入！");
            window.location.href = "login.html";
        }

        // 讀取所有書籍資料 (用於獲取書籍標題等)
        const allBooks = JSON.parse(localStorage.getItem("books") || "[]");

        // 假設有交易紀錄 (為簡化，這裡用模擬資料，實際應從 TransactionRecords.html 的 localStorage 拿)
        // 為了展示評價功能，我將在這裡模擬一些交易紀錄
        // 實際應用中，completedTransactions 應該是從 TransactionRecords.html 頁面儲存的已完成訂單
        const completedTransactions = [
            // 模擬已完成的交易，需要評價賣家
            {
                transactionId: "T001",
                buyerAccount: "buyer123", // 買家帳號
                sellerAccount: "sellerA", // 賣家帳號 (假設這是我要評價的對象)
                bookIsbn: "978-0321765723", // 賣家A上傳的書
                status: "completed",
                isRatedByBuyer: false // 買家是否已評價
            }, {
                transactionId: "T002",
                buyerAccount: "currentUser.account", // 如果你是買家，這個就是你的帳號
                sellerAccount: "sellerB",
                bookIsbn: "978-1234567890",
                status: "completed",
                isRatedByBuyer: false
            },
            // 模擬已完成的交易，我作為賣家，別人評價我
            {
                transactionId: "T003",
                buyerAccount: "buyerX",
                sellerAccount: "currentUser.account", // 如果你是賣家，這個就是你的帳號
                bookIsbn: "978-0123456789",
                status: "completed",
                isRatedBySeller: false, // 賣家是否已評價
                ratingFromBuyer: { // 買家已給的評價
                    stars: 4,
                    comment: "書況很好，交易順利！"
                }
            }
        ];
        // 請替換 "currentUser.account" 為實際登入使用者的帳號
        // 這裡暫時用一個替身字串，您應該用實際的 currentUser.account 來過濾
        // 為了方便測試，暫時不替換，請您實際測試時替換
        const userAccount = currentUser.account || "未知使用者"; // 獲取當前登入者的帳號

        // 讀取所有評價資料
        let allRatings = JSON.parse(localStorage.getItem("ratings") || "[]");

        // --- 待評價訂單 ---
        function renderPendingRatings() {
            const pendingContainer = document.getElementById("pendingRatings");
            pendingContainer.innerHTML = ""; // 清空現有內容

            // 過濾出當前使用者是「買家」且「尚未評價」的已完成交易
            const pendingOrders = completedTransactions.filter(transaction =>
                transaction.buyerAccount === userAccount && transaction.status === "completed" && !transaction.isRatedByBuyer
            );

            if (pendingOrders.length === 0) {
                pendingContainer.innerHTML = "<p class='no-data-message'>目前沒有待評價的訂單。</p>";
                return;
            }

            pendingOrders.forEach(order => {
                const book = allBooks.find(b => b.isbn === order.bookIsbn);
                const bookTitle = book ? book.title : "未知書籍";
                const sellerName = order.sellerAccount; // 這裡簡化為帳號

                const card = document.createElement("div");
                card.className = "rating-card";
                card.innerHTML = `
                    <p>訂單 ID: <strong>${order.transactionId}</strong></p>
                    <p>書籍: <strong>${bookTitle}</strong></p>
                    <p>賣家: <strong>${sellerName}</strong></p>
                    <label for="rating-stars-${order.transactionId}">評價星數：</label>
                    <select id="rating-stars-${order.transactionId}">
                        <option value="5">⭐⭐⭐⭐⭐ (5星)</option>
                        <option value="4">⭐⭐⭐⭐ (4星)</option>
                        <option value="3">⭐⭐⭐ (3星)</option>
                        <option value="2">⭐⭐ (2星)</option>
                        <option value="1">⭐ (1星)</option>
                    </select>
                    <label for="rating-comment-${order.transactionId}">評價留言：</label>
                    <textarea id="rating-comment-${order.transactionId}" placeholder="請寫下您的評價..."></textarea>
                    <button class="submit-rating-btn" data-transaction-id="${order.transactionId}">提交評價</button>
                `;
                pendingContainer.appendChild(card);
            });

            // 為每個提交按鈕添加事件監聽器
            pendingContainer.querySelectorAll(".submit-rating-btn").forEach(button => {
                button.addEventListener("click", function() {
                    const transactionId = this.dataset.transactionId;
                    const stars = document.getElementById(`rating-stars-${transactionId}`).value;
                    const comment = document.getElementById(`rating-comment-${transactionId}`).value.trim();

                    if (!comment) {
                        alert("請填寫評價留言！");
                        return;
                    }

                    // 創建新的評價物件
                    const newRating = {
                        transactionId: transactionId,
                        raterAccount: userAccount, // 評價者（買家）
                        ratedAccount: pendingOrders.find(o => o.transactionId === transactionId).sellerAccount, // 被評價者（賣家）
                        bookIsbn: pendingOrders.find(o => o.transactionId === transactionId).bookIsbn,
                        stars: parseInt(stars),
                        comment: comment,
                        timestamp: new Date().toISOString()
                    };

                    allRatings.push(newRating);
                    localStorage.setItem("ratings", JSON.stringify(allRatings));
                    alert("評價成功！");

                    // 更新交易狀態，標記為已評價
                    // 實際應用中，需要找到 completedTransactions 中對應的交易並更新 isRatedByBuyer
                    // 這裡只是簡化示範，需要您自行實現對 completedTransactions 的更新
                    const updatedTransactions = completedTransactions.map(trans => {
                        if (trans.transactionId === transactionId) {
                            return {...trans,
                                isRatedByBuyer: true
                            };
                        }
                        return trans;
                    });
                    // 注意：這裡假設 completedTransactions 是全域變數或可儲存到 LocalStorage 的變數
                    // 實際應用中，您可能需要將更新後的 completedTransactions 儲存回 localStorage
                    // localStorage.setItem("completedTransactions", JSON.stringify(updatedTransactions)); // 如果您有獨立的已完成交易儲存

                    renderPendingRatings(); // 重新渲染待評價列表
                    renderReceivedRatings(); // 同步更新我收到的評價（如果評價了自己的話，雖然通常是評價別人）
                });
            });
        }


        // --- 我收到的評價 ---
        function renderReceivedRatings() {
            const receivedContainer = document.getElementById("receivedRatings");
            receivedContainer.innerHTML = ""; // 清空現有內容

            // 過濾出評價對象是當前使用者的評價
            const received = allRatings.filter(rating => rating.ratedAccount === userAccount);

            if (received.length === 0) {
                receivedContainer.innerHTML = "<p class='no-data-message'>目前沒有收到任何評價。</p>";
                return;
            }

            received.forEach(rating => {
                const book = allBooks.find(b => b.isbn === rating.bookIsbn);
                const bookTitle = book ? book.title : "未知書籍";

                const card = document.createElement("div");
                card.className = "rating-card";
                card.innerHTML = `
                    <p>來自 <strong>${rating.raterAccount}</strong> 的評價</p>
                    <p>針對書籍: <strong>${bookTitle}</strong></p>
                    <p class="stars">${'⭐'.repeat(rating.stars)} (${rating.stars}星)</p>
                    <p>留言: ${rating.comment}</p>
                    <p><small>評價時間: ${new Date(rating.timestamp).toLocaleString()}</small></p>
                `;
                receivedContainer.appendChild(card);
            });
        }

        // 頁面載入時執行
        document.addEventListener("DOMContentLoaded", () => {
            renderPendingRatings();
            renderReceivedRatings();
        });
    </script>
</body>

</html>