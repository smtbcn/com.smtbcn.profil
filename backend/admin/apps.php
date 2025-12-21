<?php
session_start();
require_once '../config/config.php';
require_once '../core/Database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$msg = $_GET['msg'] ?? "";

// Silme İşlemi
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: apps.php?msg=deleted');
    exit;
}

// Kaydetme İşlemi (Ekleme / Güncelleme)
if (isset($_POST['save_app'])) {
    $id = $_POST['app_id'] ?? '';
    if ($id) {
        // Güncelleme
        $stmt = $db->prepare("UPDATE applications SET app_key = ?, name_tr = ?, name_en = ?, desc_tr = ?, desc_en = ?, icon = ?, color = ?, url = ? WHERE id = ?");
        $stmt->execute([$_POST['category'], $_POST['name'], $_POST['name'], $_POST['description'], $_POST['description'], $_POST['icon'], $_POST['color'], $_POST['url'], $id]);
        $msg = "Uygulama başarıyla güncellendi!";
    } else {
        // Yeni Ekleme
        $stmt = $db->prepare("INSERT INTO applications (app_key, name_tr, name_en, desc_tr, desc_en, icon, color, url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['category'], $_POST['name'], $_POST['name'], $_POST['description'], $_POST['description'], $_POST['icon'], $_POST['color'], $_POST['url']]);
        $msg = "Uygulama başarıyla eklendi!";
    }
}

$apps = $db->query("SELECT * FROM applications ORDER BY sort_order ASC, id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uygulama Yönetimi - SMTBCN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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
            padding: 25px;
            margin-bottom: 25px;
        }

        input,
        textarea,
        select {
            width: 100%;
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: white;
            padding: 12px;
            box-sizing: border-box;
            margin-bottom: 15px;
            font-size: 1rem;
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--accent);
        }

        .btn-success {
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: 0.2s;
        }

        .btn-success:hover {
            transform: translateY(-1px);
        }

        .btn-fetch {
            background: #1f6feb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            white-space: nowrap;
            font-weight: bold;
        }

        .app-item {
            display: flex;
            align-items: center;
            background: var(--bg-main);
            border: 1px solid var(--border);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: grab;
        }

        .handler {
            margin-right: 15px;
            color: var(--text-dim);
        }

        .app-icon-img {
            width: 32px;
            height: 32px;
            border-radius: 6px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: var(--bg-side);
            border: 1px solid var(--border);
            border-radius: 12px;
            width: 100%;
            max-width: 400px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .btn-modal {
            padding: 12px 25px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            flex: 1;
            transition: 0.2s;
        }

        .btn-cancel {
            background: #21262d;
            color: var(--text-main);
            border: 1px solid var(--border);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
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
        <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
    </header>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="modal" id="confirmModal">
        <div class="modal-content">
            <div class="modal-title">Emin misiniz?</div>
            <div class="modal-text" id="modalText">Bu uygulamayı silmek istediğinize emin misiniz?</div>
            <div class="modal-buttons">
                <button class="btn-modal btn-cancel" onclick="closeModal()">Vazgeç</button>
                <a href="#" class="btn-modal btn-danger" id="confirmAction">Evet, Sil</a>
            </div>
        </div>
    </div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="https://avatars.githubusercontent.com/u/75270742?v=4" alt="Avatar">
            <div style="font-weight: bold; margin-top: 5px;">Samet BİÇEN</div>
        </div>
        <nav class="sidebar-nav" style="flex:1; padding-top:20px;">
            <a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
            <a href="apps.php" class="nav-link active"><i class="fas fa-mobile-alt"></i> Uygulamalar</a>
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
            <h1>Uygulama Yönetimi</h1>

            <div class="card" id="formCard">
                <h3 id="formTitle">Yeni Uygulama Ekle</h3>
                <div id="fetchSection" style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <input type="text" id="storeUrl" placeholder="Mağaza URL'si..." style="margin-bottom:0;">
                    <button class="btn-fetch" onclick="fetchMetadata()">Bilgileri Çek</button>
                </div>

                <div id="iconPreviewContainer" style="display:none; margin-bottom: 20px; text-align: center;">
                    <img id="iconPreview" src=""
                        style="width: 80px; height: 80px; border-radius: 15px; border: 1px solid var(--border);">
                </div>

                <form method="POST">
                    <input type="hidden" name="app_id" id="appId">
                    <select name="category" id="appCategory">
                        <option value="android">Android</option>
                        <option value="apple">iOS</option>
                    </select>
                    <input type="text" name="name" id="appName" placeholder="Uygulama Adı" required>
                    <textarea name="description" id="appDesc" placeholder="Açıklama" required
                        style="height:100px;"></textarea>
                    <input type="text" name="url" id="appUrl" placeholder="Mağaza URL" required>
                    <input type="hidden" name="icon" id="appIcon">
                    <input type="hidden" name="color" id="appColor" value="#238636">
                    <button type="submit" name="save_app" id="saveBtn" class="btn-success">Kaydet ve Yayınla</button>
                    <button type="button" onclick="resetForm()" id="cancelBtn"
                        style="display:none; margin-top:10px; background:#30363d; border:none; color:white; padding:10px; border-radius:6px; width:100%; cursor:pointer;">İptal</button>
                </form>
            </div>

            <div class="card">
                <h3>Uygulamalar</h3>
                <div id="apps-list">
                    <?php foreach ($apps as $a): ?>
                        <div class="app-item" data-id="<?php echo $a['id']; ?>"
                            data-name="<?php echo htmlspecialchars($a['name_tr']); ?>"
                            data-desc="<?php echo htmlspecialchars($a['desc_tr']); ?>"
                            data-url="<?php echo htmlspecialchars($a['url']); ?>"
                            data-icon="<?php echo htmlspecialchars($a['icon']); ?>" data-cat="<?php echo $a['app_key']; ?>"
                            data-color="<?php echo $a['color']; ?>">
                            <div class="handler"><i class="fas fa-grip-lines"></i></div>
                            <div style="width: 40px; margin-right:15px; text-align:center;">
                                <?php if ($a['icon'] && strpos($a['icon'], 'http') === 0): ?>
                                    <img src="<?php echo $a['icon']; ?>" class="app-icon-img">
                                <?php else: ?>
                                    <i class="fab fa-<?php echo ($a['app_key'] == 'apple') ? 'apple' : 'android'; ?>"
                                        style="font-size: 1.5rem;"></i>
                                <?php endif; ?>
                            </div>
                            <div style="flex:1; font-weight:bold;"><?php echo htmlspecialchars($a['name_tr']); ?></div>
                            <div style="display:flex; gap:10px;">
                                <button type="button" onclick="editApp(this)"
                                    style="background:none; border:none; color:#58a6ff; cursor:pointer;"><i
                                        class="fas fa-edit"></i></button>
                                <button type="button"
                                    onclick="openDeleteModal('?delete=<?php echo $a['id']; ?>', '<?php echo htmlspecialchars($a['name_tr']); ?> silinsin mi?')"
                                    style="background:none; border:none; color:#f85149; cursor:pointer;"><i
                                        class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function fetchMetadata() {
            const url = document.getElementById('storeUrl').value;
            if (!url) return;
            try {
                const response = await fetch('fetch_metadata.php?url=' + encodeURIComponent(url));
                const data = await response.json();
                if (data.name) {
                    document.getElementById('appName').value = data.name;
                    document.getElementById('appDesc').value = data.description;
                    document.getElementById('appUrl').value = url;
                    if (data.icon) {
                        document.getElementById('appIcon').value = data.icon;
                        document.getElementById('iconPreview').src = data.icon;
                        document.getElementById('iconPreviewContainer').style.display = 'block';
                    }
                    document.getElementById('appCategory').value = url.includes('apple.com') ? 'apple' : 'android';
                }
            } catch (e) { alert('Hata: ' + e.message); }
        }

        const el = document.getElementById('apps-list');
        Sortable.create(el, {
            animation: 150, onEnd: async function () {
                const order = Array.from(el.children).map(item => item.getAttribute('data-id'));
                await fetch('update_order.php', { method: 'POST', body: JSON.stringify({ table: 'applications', order: order }), headers: { 'Content-Type': 'application/json' } });
            }
        });

        function editApp(btn) {
            const item = btn.closest('.app-item');
            document.getElementById('appId').value = item.dataset.id;
            document.getElementById('appName').value = item.dataset.name;
            document.getElementById('appDesc').value = item.dataset.desc;
            document.getElementById('appUrl').value = item.dataset.url;
            document.getElementById('appIcon').value = item.dataset.icon;
            document.getElementById('appCategory').value = item.dataset.cat;
            document.getElementById('appColor').value = item.dataset.color;
            if (item.dataset.icon.startsWith('http')) {
                document.getElementById('iconPreview').src = item.dataset.icon;
                document.getElementById('iconPreviewContainer').style.display = 'block';
            }
            document.getElementById('formTitle').innerText = "Uygulamayı Düzenle";
            document.getElementById('saveBtn').innerText = "Güncelle";
            document.getElementById('fetchSection').style.display = 'none';
            document.getElementById('cancelBtn').style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('appId').value = "";
            document.getElementById('appName').value = "";
            document.getElementById('appDesc').value = "";
            document.getElementById('appUrl').value = "";
            document.getElementById('appIcon').value = "";
            document.getElementById('iconPreviewContainer').style.display = 'none';
            document.getElementById('formTitle').innerText = "Yeni Uygulama Ekle";
            document.getElementById('saveBtn').innerText = "Kaydet ve Yayınla";
            document.getElementById('fetchSection').style.display = 'flex';
            document.getElementById('cancelBtn').style.display = 'none';
        }

        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); }
        menuToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
        function openDeleteModal(url, text) { document.getElementById('modalText').innerText = text; document.getElementById('confirmAction').href = url; document.getElementById('confirmModal').classList.add('show'); }
        function closeModal() { document.getElementById('confirmModal').classList.remove('show'); }
        window.onclick = function (event) { if (event.target == document.getElementById('confirmModal')) closeModal(); }
    </script>
</body>

</html>