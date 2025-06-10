<?php
session_start(); // 啟動會話

// 清除所有 session 變數
$_SESSION = array();

// 如果要徹底銷毀 session，請同時刪除 session cookie。
// 注意：這將銷毀會話，而不僅僅是會話數據！
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 最後，銷毀 session
session_destroy();

// 清除 localStorage 中的 currentUser (由前端 JS 處理)
echo "<script>
        localStorage.removeItem('currentUser');
        alert('您已成功登出！');
        window.location.href='login.html';
      </script>";
exit();
?>