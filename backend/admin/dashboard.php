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

if (isset($_POST['update_profile'])) {
    try {
        $stmt = $db->prepare("UPDATE profile_status SET 
            status_key = ?, 
            activity_tr = ?, 
            activity_en = ?, 
            about_tr = ?, 
            about_en = ? 
            WHERE id = 1");
        $stmt->execute([
            $_POST['status_key'],
            $_POST['activity_tr'],
            $_POST['activity_en'],
            $_POST['about_tr'],
            $_POST['about_en']
        ]);
        $msg = "Profil başarıyla güncellendi!";
    } catch (Exception $e) {
        $msg = "Hata: " . $e->getMessage();
    }
}

$current = $db->query("SELECT * FROM profile_status WHERE id = 1")->fetch();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SMTBCN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Quill Editor CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        :root {
            --bg-main: #0d1117;
            --bg-side: #161b22;
            --border: #30363d;
            --text-main: #c9d1d9;
            --text-dim: #8b949e;
            --accent: #238636;
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
            max-width: 900px;
            margin: 0 auto;
        }

        .card {
            background: var(--bg-side);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
        }

        input,
        select {
            width: 100%;
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: white;
            padding: 12px;
            margin-bottom: 15px;
            box-sizing: border-box;
            outline: none;
            font-size: 1rem;
        }

        input:focus,
        select:focus {
            border-color: var(--accent);
        }

        .ql-toolbar {
            background: #f0f0f0;
            border-radius: 6px 6px 0 0;
            border: 1px solid var(--border) !important;
        }

        .editor-container {
            height: 250px;
            background: white;
            color: black;
            border-radius: 0 0 6px 6px;
            border: 1px solid var(--border) !important;
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

        /* Overlay */
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
                padding-left: 20px;
                padding-right: 20px;
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
            <a href="dashboard.php" class="nav-link active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="apps.php" class="nav-link"><i class="fas fa-mobile-alt"></i> Uygulamalar</a>
            <a href="skills.php" class="nav-link"><i class="fas fa-code"></i> Yetenekler</a>
            <a href="projects.php" class="nav-link"><i class="fas fa-project-diagram"></i> Projeler</a>
            <a href="timeline.php" class="nav-link"><i class="fas fa-stream"></i> Timeline</a>
            <a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Ayarlar</a>
        </nav>
        <a href="logout.php"
            style="padding:20px; color:#f85149; text-decoration:none; border-top:1px solid var(--border);"><i
                class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h1>Genel Bakış</h1>
            <?php if ($msg)
                echo "<div style='padding:15px; background:#23863622; border:1px solid var(--accent); color:#3fb950; border-radius:6px; margin-bottom:20px;'>$msg</div>"; ?>

            <form method="POST" id="profileForm">
                <div class="card">
                    <h3>Canlı Durum</h3>
                    <select name="status_key" style="padding:10px; margin-bottom:15px;">
                        <option value="online" <?php echo $current['status_key'] == 'online' ? 'selected' : ''; ?>>Aktif
                            (Yeşil)</option>
                        <option value="busy" <?php echo $current['status_key'] == 'busy' ? 'selected' : ''; ?>>Meşgul
                            (Turuncu)</option>
                        <option value="coding" <?php echo $current['status_key'] == 'coding' ? 'selected' : ''; ?>>Kod
                            Yazıyor (Mavi)</option>
                        <option value="offline" <?php echo $current['status_key'] == 'offline' ? 'selected' : ''; ?>>
                            Çevrimdışı (Gri)</option>
                    </select>
                    <div style="display:flex; gap:15px;">
                        <input type="text" name="activity_tr"
                            value="<?php echo htmlspecialchars($current['activity_tr']); ?>" placeholder="Aktivite TR"
                            style="padding:10px; flex:1;">
                        <input type="text" name="activity_en"
                            value="<?php echo htmlspecialchars($current['activity_en']); ?>" placeholder="Aktivite EN"
                            style="padding:10px; flex:1;">
                    </div>
                </div>

                <div class="card">
                    <h3>Hakkımda (Türkçe)</h3>
                    <div id="editor_tr" class="editor-container"><?php echo $current['about_tr']; ?></div>
                    <input type="hidden" name="about_tr" id="about_tr_input">
                </div>

                <div class="card">
                    <h3>Hakkımda (İngilizce)</h3>
                    <div id="editor_en" class="editor-container"><?php echo $current['about_en']; ?></div>
                    <input type="hidden" name="about_en" id="about_en_input">
                </div>

                <button type="submit" name="update_profile" class="btn-save">Değişiklikleri Kaydet</button>
            </form>
        </div>
    </div>

    <!-- Quill Editor JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],
            ['link', 'blockquote', 'code-block'],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['clean']
        ];

        var quillTr = new Quill('#editor_tr', { theme: 'snow', modules: { toolbar: toolbarOptions } });
        var quillEn = new Quill('#editor_en', { theme: 'snow', modules: { toolbar: toolbarOptions } });

        document.getElementById('profileForm').onsubmit = function () {
            document.getElementById('about_tr_input').value = quillTr.root.innerHTML;
            document.getElementById('about_en_input').value = quillEn.root.innerHTML;
        };

        // Sidebar Toggle JS
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