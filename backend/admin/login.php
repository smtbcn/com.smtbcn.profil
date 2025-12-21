<?php
session_start();
require_once '../config/config.php';
require_once '../core/Database.php';

$db = Database::getInstance();

if (isset($_POST['login'])) {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
    $stmt->execute([$user]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Hatalı Giriş Bilgileri!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - SMTBCN Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-main: #0d1117;
            --bg-side: #161b22;
            --border: #30363d;
            --text-main: #c9d1d9;
            --text-dim: #8b949e;
            --accent: #238636;
            --danger: #f85149;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            background: var(--bg-main);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--accent);
            margin-bottom: 15px;
            box-shadow: 0 0 20px rgba(35, 134, 54, 0.3);
        }

        .brand h2 {
            margin: 0;
            color: white;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .login-box {
            background: var(--bg-side);
            padding: 35px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dim);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
            font-size: 1rem;
        }

        input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: white;
            box-sizing: border-box;
            outline: none;
            font-size: 1rem;
            transition: all 0.2s;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(35, 134, 54, 0.15);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #2ea043;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-msg {
            background: rgba(248, 81, 73, 0.1);
            color: var(--danger);
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(248, 81, 73, 0.2);
            font-size: 0.9rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: var(--text-dim);
            font-size: 0.85rem;
        }

        /* Mobile specific adjustments */
        @media (max-width: 480px) {
            .login-box {
                padding: 25px 20px;
            }
            .brand img {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="brand">
            <img src="https://avatars.githubusercontent.com/u/75270742?v=4" alt="Avatar">
            <h2>SMTBCN Panel</h2>
        </div>

        <div class="login-box">
            <?php if (isset($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Kullanıcı adınız..." required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label>Şifre</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Şifreniz..." required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" name="login" class="btn-login">
                    Giriş Yap <i class="fas fa-arrow-right" style="margin-left: 10px; font-size: 0.9rem;"></i>
                </button>
            </form>
        </div>

        <div class="footer">
            &copy; 2025 SMTBCN | Tüm Hakları Saklıdır.
        </div>
    </div>
</body>

</html>