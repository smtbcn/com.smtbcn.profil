<?php
session_start();
require_once '../config/config.php';
require_once '../core/Database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$msg = "";
$error = "";

if (isset($_POST['update_admin'])) {
    $new_user = $_POST['username'] ?? '';
    $new_pass = $_POST['password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if (empty($new_user)) {
        $error = "Kullanıcı adı boş olamaz!";
    } elseif (!empty($new_pass) && $new_pass !== $confirm_pass) {
        $error = "Şifreler eşleşmiyor!";
    } else {
        try {
            if (!empty($new_pass)) {
                $hash = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE admins SET username = ?, password = ? WHERE id = 1");
                $stmt->execute([$new_user, $hash]);
            } else {
                $stmt = $db->prepare("UPDATE admins SET username = ? WHERE id = 1");
                $stmt->execute([$new_user]);
            }
            $_SESSION['admin_user'] = $new_user;
            $msg = "Giriş bilgileri başarıyla güncellendi!";
        } catch (Exception $e) {
            $error = "Hata: " . $e->getMessage();
        }
    }
}

$admin = $db->query("SELECT * FROM admins WHERE id = 1")->fetch();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - SMTBCN Admin</title>
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
            --sidebar-width: 260px;
            --topbar-height: 60px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            background: var(--bg-main);
            color: var(--text-main);
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* Mobile Top Bar */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--topbar-height);
            background: var(--bg-side);
            border-bottom: 1px solid var(--border);
            padding: 0 20px;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: var(--text-main);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-side);
            border-right: 1px solid var(--border);
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 1001;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-header img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 2px solid var(--accent);
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: var(--text-dim);
            text-decoration: none;
            gap: 12px;
            transition: 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #21262d;
            color: white;
        }

        .nav-link.active {
            border-left: 4px solid var(--accent);
            padding-left: 21px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 40px;
            transition: margin-left 0.3s ease;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .card {
            background: var(--bg-side);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
        }

        input {
            width: 100%;
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: white;
            padding: 12px;
            margin-bottom: 20px;
            box-sizing: border-box;
            outline: none;
            font-size: 1rem;
        }

        input:focus {
            border-color: var(--accent);
        }

        .btn-save {
            background: var(--accent);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            font-size: 1rem;
            transition: 0.2s;
        }

        .btn-save:hover {
            transform: translateY(-1px);
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: #23863622;
            border-color: var(--accent);
            color: #3fb950;
        }

        .alert-error {
            background: #f8514922;
            border-color: var(--danger);
            color: var(--danger);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        @media (max-width: 992px) {
            .mobile-header {
                display: flex;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding-top: calc(var(--topbar-height) + 20px);
            }

            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>

<body>
    <header class="mobile-header">
        <div style="font-weight: bold;">SMTBCN Admin</div>
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="https://avatars.githubusercontent.com/u/75270742?v=4" alt="Avatar">
            <div style="font-weight: bold; margin-top: 5px;">Samet BİÇEN</div>
        </div>
        <nav class="sidebar-nav" style="flex:1; padding-top:20px;">
            <a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
            <a href="apps.php" class="nav-link"><i class="fas fa-mobile-alt"></i> Uygulamalar</a>
            <a href="skills.php" class="nav-link"><i class="fas fa-code"></i> Yetenekler</a>
            <a href="projects.php" class="nav-link"><i class="fas fa-project-diagram"></i> Projeler</a>
            <a href="timeline.php" class="nav-link"><i class="fas fa-stream"></i> Timeline</a>
            <a href="settings.php" class="nav-link active"><i class="fas fa-cog"></i> Ayarlar</a>
        </nav>
        <a href="logout.php"
            style="padding:20px; color:#f85149; text-decoration:none; border-top:1px solid var(--border);"><i
                class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h1>Giriş Ayarları</h1>

            <?php if ($msg): ?>
                <div class="alert alert-success"><?php echo $msg; ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST">
                    <label style="display:block; margin-bottom:8px; color:var(--text-dim);">Kullanıcı Adı</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>"
                        required>

                    <label style="display:block; margin-bottom:8px; color:var(--text-dim);">Yeni Şifre (Değiştirmek
                        istemiyorsanız boş bırakın)</label>
                    <input type="password" name="password" placeholder="••••••••">

                    <label style="display:block; margin-bottom:8px; color:var(--text-dim);">Yeni Şifre Tekrar</label>
                    <input type="password" name="confirm_password" placeholder="••••••••">

                    <button type="submit" name="update_admin" class="btn-save">Bilgileri Güncelle</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    </script>
</body>

</html>