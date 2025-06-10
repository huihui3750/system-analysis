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
$userRole = $currentUser['role'];
$userName = htmlspecialchars($currentUser['name'] ?? $currentUser['account']);

$transaction_id = $_GET['transaction_id'] ?? null;

// 如果沒有提供 transaction_id，則導回交易紀錄頁面或顯示錯誤
if (empty($transaction_id)) {
    echo "<script>alert('缺少交易ID，無法評價！'); window.location.href='TransactionRecords.php';</script>";
    exit();
}

$transaction_info = null; // 用於儲存交易的詳細資訊

// 查詢交易詳細資訊，包括買家、賣家ID、書籍資訊和狀態
if ($conn && !$conn->connect_error) {
    $sql = "
        SELECT
            t.transaction_id,
            t.book_id,
            t.buyer_id,
            t.seller_id,
            t.status,
            t.transaction_datetime AS transaction_date,
            b.Book_title,
            b.Book_author,
            b.Book_image_path,
            buy.B_account AS buyer_account,
            buy.B_name AS buyer_name,
            sell.S_account AS seller_account,
            sell.S_name AS seller_name
        FROM
            transactions t
        JOIN
            book b ON t.book_id = b.Book_ID
        LEFT JOIN
            buyer buy ON t.buyer_id = buy.B_ID
        LEFT JOIN
            seller sell ON t.seller_id = sell.S_ID
        WHERE
            t.transaction_id = ?
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction_info = $result->fetch_assoc();
        $stmt->close();
    } else {
        error_log("準備 SQL 語句失敗: " . $conn->error);
    }
} else {
    error_log("資料庫連線未正確建立。");
}

// 檢查交易是否存在且用戶是參與者
if (!$transaction_info) {
    echo "<script>alert('找不到該交易或您無權評價！'); window.location.href='TransactionRecords.php';</script>";
    exit();
}

// 確保只有交易的買家或賣家可以進入評價頁面
// 並且，如果評價是單向的（例如只有買家評價賣家），需要額外檢查
// 這裡假設是買家評價賣家，所以只有買家ID與當前用戶ID相符才能評價
if ($userRole === 'buyer' && $transaction_info['buyer_id'] != $userID) {
    echo "<script>alert('您無權評價此交易！'); window.location.href='TransactionRecords.php';</script>";
    exit();
}
// 如果是雙向評價，或者賣家也能評價買家，需要調整上面的邏輯
// 例如：
// if ($transaction_info['buyer_id'] != $userID && $transaction_info['seller_id'] != $userID) {
//     echo "<script>alert('您無權評價此交易！'); window.location.href='TransactionRecords.php';</script>";
//     exit();
// }


// 確定被評價的對象 (假設買家評價賣家)
$ratedUserName = htmlspecialchars($transaction_info['seller_name'] ?? $transaction_info['seller_account']);
$ratedUserID = $transaction_info['seller_id'];
$ratedUserRole = 'seller'; // 被評價人的角色


// 檢查是否已經評價過
$has_evaluated = false;
if ($conn && !$conn->connect_error) {
    // 檢查 evaluations 表中是否存在該交易ID且評價人是被評價人
    $stmt_check_eval = $conn->prepare("SELECT COUNT(*) FROM evaluations WHERE transaction_id = ? AND rater_id = ? AND rated_user_id = ?");
    if ($stmt_check_eval) {
        $stmt_check_eval->bind_param("iii", $transaction_id, $userID, $ratedUserID);
        $stmt_check_eval->execute();
        $stmt_check_eval->bind_result($count);
        $stmt_check_eval->fetch();
        $stmt_check_eval->close();
        if ($count > 0) {
            $has_evaluated = true;
        }
    }
}
$conn->close();

?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>評價交易</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
        }

        nav ul li {
            margin-right: 15px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .account-container {
            margin-left: auto;
            margin-right: 15px;
            color: white;
            font-weight: bold;
        }

        main {
            padding: 20px;
            max-width: 700px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #3f51b5;
            margin-bottom: 20px;
        }

        .transaction-details {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .transaction-details img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }

        .transaction-details .info {
            flex-grow: 1;
        }

        .transaction-details h2 {
            margin-top: 0;
            margin-bottom: 8px;
            color: #3f51b5;
            font-size: 1.5rem;
        }

        .transaction-details p {
            margin: 4px 0;
            color: #555;
        }

        .evaluation-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #444;
        }

        .rating-stars {
            font-size: 2.5rem;
            color: #ccc;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .rating-stars .star {
            display: inline-block;
            transition: color 0.2s;
        }

        .rating-stars .star.selected {
            color: #ffc107; /* 金黃色 */
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-height: 120px;
            resize: vertical;
            font-size: 1rem;
            box-sizing: border-box; /* 確保 padding 不增加寬度 */
            margin-bottom: 20px;
        }

        .form-actions {
            text-align: center;
        }

        .submit-btn {
            padding: 12px 25px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        .message-box {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            background-color: #e0f2f7;
            color: #2196f3;
            font-weight: bold;
            border: 1px solid #a7d9ed;
        }
        .message-box.error {
            background-color: #ffebee;
            color: #f44336;
            border-color: #ef9a9a;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">首頁</a></li>
                <li><a href="TransactionRecords.php">交易紀錄</a></li>
                <div class="account-container">
                    <span id="accountNameDisplay"><?php echo htmlspecialchars($currentUser['account']); ?></span>
                </div>
                <li><a href="logout.php">登出</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>評價交易</h1>
        <?php if ($has_evaluated): ?>
            <div class="message-box">您已評價過此筆交易。</div>
        <?php else: ?>
            <div class="transaction-details">
                <img src="<?php echo htmlspecialchars($transaction_info['Book_image_path'] ?? 'uploads/default.jpg'); ?>" alt="<?php echo htmlspecialchars($transaction_info['Book_title']); ?>">
                <div class="info">
                    <h2><?php echo htmlspecialchars($transaction_info['Book_title']); ?></h2>
                    <p>作者: <?php echo htmlspecialchars($transaction_info['Book_author']); ?></p>
                    <p>被評價對象: **<?php echo $ratedUserName; ?>**</p>
                    <p>交易ID: <?php echo htmlspecialchars($transaction_id); ?></p>
                </div>
            </div>

            <form id="evaluationForm" class="evaluation-form">
                <input type="hidden" id="transactionId" name="transaction_id" value="<?php echo htmlspecialchars($transaction_id); ?>">
                <input type="hidden" id="ratedUserId" name="rated_user_id" value="<?php echo htmlspecialchars($ratedUserID); ?>">
                <input type="hidden" id="raterId" name="rater_id" value="<?php echo htmlspecialchars($userID); ?>">
                <input type="hidden" id="ratedUserRole" name="rated_user_role" value="<?php echo htmlspecialchars($ratedUserRole); ?>">
                <input type="hidden" id="ratingValue" name="rating" value="0">

                <label for="rating">您的評分:</label>
                <div id="ratingStars" class="rating-stars">
                    <span class="star" data-value="1">&#9733;</span>
                    <span class="star" data-value="2">&#9733;</span>
                    <span class="star" data-value="3">&#9733;</span>
                    <span class="star" data-value="4">&#9733;</span>
                    <span class="star" data-value="5">&#9733;</span>
                </div>

                <label for="comment">您的評論 (選填):</label>
                <textarea id="comment" name="comment" placeholder="請輸入您的評論..."></textarea>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">提交評價</button>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ratingStars = document.getElementById('ratingStars');
            const ratingValueInput = document.getElementById('ratingValue');
            const stars = ratingStars.querySelectorAll('.star');
            const evaluationForm = document.getElementById('evaluationForm');

            let currentRating = 0;

            // 處理星級點擊事件
            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const value = parseInt(star.dataset.value);
                    currentRating = value;
                    ratingValueInput.value = value;
                    updateStars(value);
                });

                // 處理滑鼠移入事件 (視覺回饋)
                star.addEventListener('mouseover', () => {
                    const value = parseInt(star.dataset.value);
                    updateStars(value, true); // 暫時顯示 hover 效果
                });

                // 處理滑鼠移出事件 (恢復到選定狀態或無選定狀態)
                star.addEventListener('mouseout', () => {
                    updateStars(currentRating); // 恢復到已選定的星級
                });
            });

            function updateStars(rating, isHover = false) {
                stars.forEach(star => {
                    const value = parseInt(star.dataset.value);
                    if (isHover) {
                        // 鼠標懸停時，亮起從1到當前鼠標所在星級的星
                        if (value <= rating) {
                            star.classList.add('selected');
                        } else {
                            star.classList.remove('selected');
                        }
                    } else {
                        // 非懸停狀態，根據選定值 permanent 亮起
                        if (value <= rating) {
                            star.classList.add('selected');
                        } else {
                            star.classList.remove('selected');
                        }
                    }
                });
            }

            // 表單提交處理
            evaluationForm.addEventListener('submit', function(event) {
                event.preventDefault(); // 阻止表單默認提交

                const transactionId = document.getElementById('transactionId').value;
                const ratedUserId = document.getElementById('ratedUserId').value;
                const raterId = document.getElementById('raterId').value;
                const ratedUserRole = document.getElementById('ratedUserRole').value;
                const rating = parseInt(document.getElementById('ratingValue').value);
                const comment = document.getElementById('comment').value.trim();

                if (rating === 0) {
                    alert('請給予星級評分！');
                    return;
                }

                // 發送數據到後端 submit_evaluation.php
                fetch('submit_evaluation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        transaction_id: transactionId,
                        rater_id: raterId, // 評價人 (當前用戶)
                        rated_user_id: ratedUserId, // 被評價人 (賣家)
                        rated_user_role: ratedUserRole, // 被評價人的角色
                        rating: rating,
                        comment: comment
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('評價提交成功！');
                        window.location.href = 'TransactionRecords.php'; // 評價成功後導回交易紀錄頁面
                    } else {
                        alert('評價提交失敗: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('提交評價時發生錯誤！');
                });
            });
        });
    </script>
</body>
</html>