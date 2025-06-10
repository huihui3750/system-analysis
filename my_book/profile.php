<?php
session_start();
include 'db_connection.php'; // 確保包含資料庫連線檔案

$currentUser = $_SESSION['currentUser'] ?? null;

// 如果沒有登入，重定向到登入頁面
if (!$currentUser) {
    echo "<script>alert('請先登入！'); window.location.href='login.html';</script>";
    exit();
}

// 獲取當前用戶的 ID 和角色
$userID = $currentUser['id'];
$userRole = $currentUser['role'];
$userAccount = $currentUser['account']; // 用於顯示在導航欄和 JavaScript 中
$userName = htmlspecialchars($currentUser['name'] ?? $userAccount); // 優先使用 name，如果沒有則用 account


// 獲取用戶資料
$userData = [];
if ($userRole === 'buyer') {
    $stmt = $conn->prepare("SELECT B_account as account, B_name as name, B_telephone as telephone, B_email as email, B_department as department FROM buyer WHERE B_ID = ?");
} else { // seller
    $stmt = $conn->prepare("SELECT S_account as account, S_name as name, S_telephone as telephone, S_email as email, S_department as department FROM seller WHERE S_ID = ?");
}

if ($stmt) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();
} else {
    // 處理預處理失敗
    error_log("Failed to prepare statement for fetching user data: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 校園二手書交易平台 - 個人中心</title>
    <link rel="stylesheet" href="css/normalize.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6; /* 頁面背景色 */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header 樣式 - 與 index.php 保持一致 */
        header {
            background-color: #3f51b5; /* 藍色 */
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
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            white-space: nowrap; /* 防止連結文字換行 */
        }

        header nav ul li a:hover {
            background-color: #5c6bc0; /* 懸停效果 */
        }

        /* 導航欄中活動連結樣式 (個人中心應該是活動的) */
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

        /* Main content styles */
        main {
            flex-grow: 1; /* 讓 main 區域佔滿剩餘空間 */
            padding: 40px 20px;
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        h2 {
            text-align: center;
            color: #3f51b5;
            margin-bottom: 30px;
            font-size: 2.2em;
            letter-spacing: 1px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        .profile-section {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .profile-section h3 {
            color: #555;
            font-size: 1.5em;
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 1px dashed #e0e0e0;
            padding-bottom: 10px;
        }

        .profile-info p {
            margin: 10px 0;
            font-size: 1.1em;
            color: #444;
            display: flex; /* 使用 flexbox 讓標籤和值對齊 */
            align-items: center;
        }

        .profile-info p strong {
            color: #333;
            display: inline-block;
            width: 100px; /* 統一標籤寬度 */
            flex-shrink: 0; /* 防止標籤縮小 */
        }

        /* 編輯模式下的輸入框樣式 */
        .profile-info input[type="text"],
        .profile-info input[type="email"] {
            flex-grow: 1; /* 輸入框佔據剩餘空間 */
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
            margin-left: 10px; /* 與標籤的間距 */
            max-width: calc(100% - 110px); /* 限制輸入框最大寬度 */
        }
        
        .profile-actions {
            text-align: right; /* 按鈕靠右 */
            margin-top: 20px;
        }

        .action-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-left: 10px; /* 按鈕間距 */
        }

        .action-btn.edit-btn {
            background-color: #28a745; /* 綠色編輯按鈕 */
        }
        .action-btn.edit-btn:hover {
            background-color: #218838;
        }

        .action-btn.save-btn {
            background-color: #007bff; /* 藍色保存按鈕 */
        }
        .action-btn.save-btn:hover {
            background-color: #0056b3;
        }

        .action-btn.cancel-btn {
            background-color: #6c757d; /* 灰色取消按鈕 */
        }
        .action-btn.cancel-btn:hover {
            background-color: #5a6268;
        }


        /* 評價區塊 */
        .ratings-section {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap; /* 允許換行 */
        }

        .rating-category {
            flex: 1; /* 讓每個類別平均分配空間 */
            min-width: 300px; /* 最小寬度，防止過度擠壓 */
            background-color: #fcfcfc;
            border: 1px solid #e8eaf6;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
        }

        .rating-category h3 {
            color: #3f51b5;
            font-size: 1.3em;
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #e8eaf6;
            padding-bottom: 10px;
            text-align: center;
        }

        .rating-list {
            flex-grow: 1;
            overflow-y: auto; /* 允許滾動 */
            max-height: 400px; /* 設定最大高度 */
            padding-right: 10px; /* 為了滾動條 */
        }

        .rating-card {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            font-size: 0.95em;
            line-height: 1.6;
        }

        .rating-card strong {
            color: #333;
        }

        .rating-card .stars {
            color: #fbc02d; /* 星星顏色 */
            font-size: 1.2em;
            margin: 5px 0;
        }

        .rating-card p {
            margin: 5px 0;
        }

        .rating-card small {
            color: #888;
            font-size: 0.8em;
            display: block;
            margin-top: 8px;
        }

        .no-data-message {
            text-align: center;
            color: #777;
            padding: 20px;
            font-style: italic;
        }

        /* Footer 樣式 */
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: auto; /* 將 footer 推到底部 */
            font-size: 0.9em;
        }

        /* 響應式設計 */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding: 0 15px;
            }
            header nav {
                width: 100%;
                flex-direction: column;
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
                margin: 10px 0 0 0;
                width: 100%;
                text-align: center;
            }
            .logout-btn {
                margin: 5px auto 0 auto;
                width: calc(100% - 20px);
            }
            main {
                padding: 20px;
                margin: 15px auto;
                width: 95%;
            }
            .profile-info p strong {
                width: 80px; /* 小螢幕下標籤寬度可以縮小 */
            }
            .ratings-section {
                flex-direction: column;
                gap: 15px;
            }
            .rating-category {
                min-width: unset; /* 取消最小寬度限制 */
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            header h1 {
                font-size: 24px;
            }
            header nav ul {
                flex-direction: column;
                gap: 5px;
            }
            .profile-section {
                padding: 15px;
            }
            .profile-info p {
                font-size: 1em;
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
                    <li><a href="index.php">首頁</a></li>
                    <li><a href="upload.php">上傳書籍</a></li>
                    <li><a href="communicate.php">溝通</a></li>
                    <li class="active-nav-link"><a href="profile.php">個人中心</a></li>
                    <li><a href="TransactionRecords.php">交易紀錄</a></li>
                    <li><a href="evaluate.php">評價</a></li>
                </ul>
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
        <h2>個人中心</h2>

        <section class="profile-section">
            <h3>個人資料</h3>
            <div id="profileInfoDisplay" class="profile-info">
                <p><strong>帳號:</strong> <?= htmlspecialchars($userData['account'] ?? 'N/A') ?></p>
                <p><strong>姓名:</strong> <span id="display-name"><?= htmlspecialchars($userData['name'] ?? 'N/A') ?></span></p>
                <p><strong>角色:</strong> <?= htmlspecialchars($userRole === 'buyer' ? '買家' : '賣家') ?></p>
                <p><strong>電話:</strong> <span id="display-telephone"><?= htmlspecialchars($userData['telephone'] ?? 'N/A') ?></span></p>
                <p><strong>Email:</strong> <span id="display-email"><?= htmlspecialchars($userData['email'] ?? 'N/A') ?></span></p>
                <p><strong>科系:</strong> <span id="display-department"><?= htmlspecialchars($userData['department'] ?? 'N/A') ?></span></p>
            </div>
            <div id="profileInfoEdit" class="profile-info" style="display:none;">
                <p><strong>帳號:</strong> <?= htmlspecialchars($userData['account'] ?? 'N/A') ?></p>
                <p><strong>姓名:</strong> <input type="text" id="edit-name" value="<?= htmlspecialchars($userData['name'] ?? '') ?>"></p>
                <p><strong>角色:</strong> <?= htmlspecialchars($userRole === 'buyer' ? '買家' : '賣家') ?></p>
                <p><strong>電話:</strong> <input type="text" id="edit-telephone" value="<?= htmlspecialchars($userData['telephone'] ?? '') ?>"></p>
                <p><strong>Email:</strong> <input type="email" id="edit-email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>"></p>
                <p><strong>科系:</strong> <input type="text" id="edit-department" value="<?= htmlspecialchars($userData['department'] ?? '') ?>"></p>
            </div>
            <div class="profile-actions">
                <button id="editProfileBtn" class="action-btn edit-btn">編輯資料</button>
                <button id="saveProfileBtn" class="action-btn save-btn" style="display:none;">儲存</button>
                <button id="cancelEditBtn" class="action-btn cancel-btn" style="display:none;">取消</button>
            </div>
        </section>

        <section class="profile-section">
            <h3>我的評價</h3>
            <div class="ratings-section">
                <div class="rating-category">
                    <h3>我給出的評價</h3>
                    <div id="givenRatings" class="rating-list">
                        <p class="no-data-message">載入中...</p>
                    </div>
                </div>
                <div class="rating-category">
                    <h3>我收到的評價</h3>
                    <div id="receivedRatings" class="rating-list">
                        <p class="no-data-message">載入中...</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 校園二手書交易平台. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const currentUserID = <?= json_encode($userID); ?>;
        const currentUserRole = <?= json_encode($userRole); ?>;
        let allRatings = []; // 用於儲存所有評價數據，方便過濾

        document.addEventListener('DOMContentLoaded', function() {
            fetchRatings();
            
            const editProfileBtn = document.getElementById('editProfileBtn');
            const saveProfileBtn = document.getElementById('saveProfileBtn');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            const profileInfoDisplay = document.getElementById('profileInfoDisplay');
            const profileInfoEdit = document.getElementById('profileInfoEdit');

            // 原始資料的副本，用於取消編輯時恢復
            let originalData = {
                name: document.getElementById('display-name').textContent,
                telephone: document.getElementById('display-telephone').textContent,
                email: document.getElementById('display-email').textContent,
                department: document.getElementById('display-department').textContent
            };

            editProfileBtn.addEventListener('click', () => {
                profileInfoDisplay.style.display = 'none';
                profileInfoEdit.style.display = 'block';
                editProfileBtn.style.display = 'none';
                saveProfileBtn.style.display = 'inline-block';
                cancelEditBtn.style.display = 'inline-block';
                
                // 確保編輯框中的值與當前顯示值一致
                document.getElementById('edit-name').value = originalData.name;
                document.getElementById('edit-telephone').value = originalData.telephone;
                document.getElementById('edit-email').value = originalData.email;
                document.getElementById('edit-department').value = originalData.department;
            });

            cancelEditBtn.addEventListener('click', () => {
                profileInfoDisplay.style.display = 'block';
                profileInfoEdit.style.display = 'none';
                editProfileBtn.style.display = 'inline-block';
                saveProfileBtn.style.display = 'none';
                cancelEditBtn.style.display = 'none';
                // 恢復顯示的資料為原始資料 (如果中間有編輯但未儲存)
                document.getElementById('display-name').textContent = originalData.name;
                document.getElementById('display-telephone').textContent = originalData.telephone;
                document.getElementById('display-email').textContent = originalData.email;
                document.getElementById('display-department').textContent = originalData.department;
            });

            saveProfileBtn.addEventListener('click', async () => {
                const updatedData = {
                    name: document.getElementById('edit-name').value.trim(),
                    telephone: document.getElementById('edit-telephone').value.trim(),
                    email: document.getElementById('edit-email').value.trim(),
                    department: document.getElementById('edit-department').value.trim()
                };

                // 簡單的客戶端驗證 (可以根據需求添加更複雜的驗證)
                if (!updatedData.name || !updatedData.telephone || !updatedData.email || !updatedData.department) {
                    alert('所有欄位都不能為空！');
                    return;
                }
                if (!/\S+@\S+\.\S+/.test(updatedData.email)) {
                    alert('請輸入有效的Email格式！');
                    return;
                }
                if (!/^\d{10}$/.test(updatedData.telephone)) {
                    alert('請輸入10位數字的電話號碼！');
                    return;
                }

                try {
                    const response = await fetch('update_profile.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(updatedData)
                    });
                    const result = await response.json();

                    if (result.success) {
                        alert('資料更新成功！');
                        // 更新顯示的資料
                        document.getElementById('display-name').textContent = updatedData.name;
                        document.getElementById('display-telephone').textContent = updatedData.telephone;
                        document.getElementById('display-email').textContent = updatedData.email;
                        document.getElementById('display-department').textContent = updatedData.department;

                        // 更新原始資料副本
                        originalData = { ...updatedData };

                        // 切換回顯示模式
                        profileInfoDisplay.style.display = 'block';
                        profileInfoEdit.style.display = 'none';
                        editProfileBtn.style.display = 'inline-block';
                        saveProfileBtn.style.display = 'none';
                        cancelEditBtn.style.display = 'none';

                        // 由於名稱可能更新，更新導航欄的名稱顯示 (如果需要)
                        // 注意：這需要 PHP 重新載入 session 或有其他機制來更新導航欄
                        // 目前最簡單的方法是重新載入頁面，但會讓用戶體驗稍差
                        // window.location.reload(); // 可以選擇重新載入頁面
                    } else {
                        alert('資料更新失敗: ' + result.message);
                    }
                } catch (error) {
                    console.error('更新資料時發生錯誤:', error);
                    alert('更新資料時發生錯誤！');
                }
            });
        });

        async function fetchRatings() {
            try {
                const response = await fetch('get_user_ratings.php');
                const data = await response.json();

                if (data.success) {
                    allRatings = data.ratings; // 將數據儲存到全局變數
                    renderGivenRatings();
                    renderReceivedRatings();
                } else {
                    console.error("Error fetching ratings:", data.message);
                    document.getElementById("givenRatings").innerHTML = `<p class="no-data-message">載入評價失敗: ${data.message}</p>`;
                    document.getElementById("receivedRatings").innerHTML = `<p class="no-data-message">載入評價失敗: ${data.message}</p>`;
                }
            } catch (error) {
                console.error("Error fetching ratings:", error);
                document.getElementById("givenRatings").innerHTML = "<p class='no-data-message'>無法連接到伺服器獲取評價。</p>";
                document.getElementById("receivedRatings").innerHTML = "<p class='no-data-message'>無法連接到伺服器獲取評價。</p>";
            }
        }

        function renderGivenRatings() {
            const givenContainer = document.getElementById("givenRatings");
            givenContainer.innerHTML = ""; // 清空現有內容

            // 過濾出評價人是當前使用者的評價
            const given = allRatings.filter(rating =>
                rating.type === 'given' && rating.raterID === currentUserID
            );

            if (given.length === 0) {
                givenContainer.innerHTML = "<p class='no-data-message'>目前沒有給出任何評價。</p>";
                return;
            }

            given.forEach(rating => {
                const card = document.createElement("div");
                card.className = "rating-card";
                card.innerHTML = `
                    <p>評價對象: <strong>${rating.ratedAccount}</strong></p>
                    <p>針對書籍: <strong>${rating.bookTitle}</strong></p>
                    <p class="stars">${'⭐'.repeat(rating.stars)} (${rating.stars}星)</p>
                    <p>留言: ${rating.comment}</p>
                    <p><small>評價時間: ${new Date(rating.timestamp).toLocaleString()}</small></p>
                `;
                givenContainer.appendChild(card);
            });
        }

        function renderReceivedRatings() {
            const receivedContainer = document.getElementById("receivedRatings");
            receivedContainer.innerHTML = ""; // 清空現有內容

            // 過濾出評價對象是當前使用者的評價
            const received = allRatings.filter(rating =>
                rating.type === 'received' && rating.ratedID === currentUserID
            );

            if (received.length === 0) {
                receivedContainer.innerHTML = "<p class='no-data-message'>目前沒有收到任何評價。</p>";
                return;
            }

            received.forEach(rating => {
                const card = document.createElement("div");
                card.className = "rating-card";
                card.innerHTML = `
                    <p>來自 <strong>${rating.raterAccount}</strong> 的評價</p>
                    <p>針對書籍: <strong>${rating.bookTitle}</strong></p>
                    <p class="stars">${'⭐'.repeat(rating.stars)} (${rating.stars}星)</p>
                    <p>留言: ${rating.comment}</p>
                    <p><small>評價時間: ${new Date(rating.timestamp).toLocaleString()}</small></p>
                `;
                receivedContainer.appendChild(card);
            });
        }
    </script>
</body>
</html>