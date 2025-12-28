<?php
require_once '../core/Session.php';
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Security.php';

// Already logged in?
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0e27;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #e4e4e7;
        }

        .login-box {
            background: #151932;
            border: 1px solid #2a2f4a;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 32px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #94a3b8;
        }

        input {
            width: 100%;
            padding: 12px;
            background: #1a1f3a;
            border: 1px solid #2a2f4a;
            border-radius: 8px;
            color: #e4e4e7;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #ef4444;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h1>🔐 Admin Girişi</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Kullanıcı Adı</label>
                <input type="text" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label>Şifre</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="btn">Giriş Yap</button>
        </form>
    </div>
</body>

</html>