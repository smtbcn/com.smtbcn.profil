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

// 1. OTOMASYON: Proje ve Uygulamalardan Timeline Oluştur
if (isset($_POST['auto_sync'])) {
    try {
        $apps = $db->query("SELECT * FROM applications")->fetchAll();
        foreach ($apps as $app) {
            $check = $db->prepare("SELECT id FROM timeline WHERE title_tr = ?");
            $check->execute([$app['name_tr'] . " Yayında!"]);
            if (!$check->fetch()) {
                $ins = $db->prepare("INSERT INTO timeline (title_tr, title_en, desc_tr, desc_en, type, icon, color, link) VALUES (?, ?, ?, ?, 'app', ?, ?, ?)");
                $ins->execute([$app['name_tr'] . " Yayında!", $app['name_en'] . " is Live!", "Uygulama yayına alındı.", "App is live on store.", $app['app_key'] == 'apple' ? 'apple' : 'android', $app['color'] ?? '#3fb950', $app['url']]);
            }
        }
        $projects = $db->query("SELECT * FROM projects")->fetchAll();
        foreach ($projects as $proj) {
            $check = $db->prepare("SELECT id FROM timeline WHERE title_tr = ?");
            $check->execute([$proj['name'] . " Projesi Tamamlandı"]);
            if (!$check->fetch()) {
                $ins = $db->prepare("INSERT INTO timeline (title_tr, title_en, desc_tr, desc_en, type, icon, color, link) VALUES (?, ?, ?, ?, 'project', 'github', '#ffffff', ?)");
                $ins->execute([$proj['name'] . " Projesi Tamamlandı", $proj['name'] . " Project Completed", $proj['description'], $proj['description'], $proj['html_url']]);
            }
        }
        header("Location: timeline.php?msg=" . urlencode("Otomatik eşitleme tamamlandı!"));
        exit;
    } catch (Exception $e) {
        $msg = "Hata: " . $e->getMessage();
    }
}

// Kaydetme İşlemi (Ekleme / Güncelleme)
if (isset($_POST['save_event'])) {
    $id = $_POST['event_id'] ?? '';
    if ($id) {
        $upd = $db->prepare("UPDATE timeline SET title_tr = ?, title_en = ?, desc_tr = ?, desc_en = ?, event_date = ?, type = ?, icon = ?, color = ? WHERE id = ?");
        $upd->execute([$_POST['title_tr'], $_POST['title_en'], $_POST['desc_tr'], $_POST['desc_en'], $_POST['event_date'], $_POST['type'], $_POST['icon'], $_POST['color'], $id]);
        $msg = "Olay güncellendi!";
    } else {
        $ins = $db->prepare("INSERT INTO timeline (title_tr, title_en, desc_tr, desc_en, event_date, type, icon, color) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$_POST['title_tr'], $_POST['title_en'], $_POST['desc_tr'], $_POST['desc_en'], $_POST['event_date'], $_POST['type'], $_POST['icon'], $_POST['color']]);
        $msg = "Olay eklendi!";
    }
}

if (isset($_GET['delete'])) {
    $db->prepare("DELETE FROM timeline WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: timeline.php?msg=deleted");
    exit;
}

$events = $db->query("SELECT * FROM timeline ORDER BY sort_order ASC, id DESC")->fetchAll();

function getTimelineIconPrefix($icon)
{
    if (!$icon)
        return 'fas';
    $brands = ['apple', 'android', 'github', 'google', 'play-store', 'app-store', 'react', 'js', 'node-js', 'php', 'laravel', 'python'];
    return in_array($icon, $brands) ? 'fab' : 'fas';
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Yönetimi - SMTBCN</title>
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
        select,
        textarea {
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
        select:focus,
        textarea:focus {
            border-color: var(--accent);
        }

        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 15px;
            background: var(--bg-main);
            border: 1px solid var(--border);
            padding: 8px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            height: 48px;
            position: relative;
        }

        .color-preview {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .color-input-hidden {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .item {
            display: flex;
            align-items: center;
            background: var(--bg-main);
            border: 1px solid var(--border);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: grab;
        }

        .handler {
            margin-right: 15px;
            color: var(--text-dim);
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
            <h3 id="modalTitle">Emin misiniz?</h3>
            <p id="modalText">Bu olay silinecek.</p>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button onclick="closeModal()"
                    style="flex:1; padding:12px; border:none; border-radius:6px; background:#21262d; color:white; cursor:pointer;">Vazgeç</button>
                <a href="#" id="confirmAction"
                    style="flex:1; padding:12px; border:none; border-radius:6px; background:var(--danger); color:white; text-decoration:none; display:flex; align-items:center; justify-content:center; font-weight:bold;">Evet,
                    Sil</a>
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
            <a href="apps.php" class="nav-link"><i class="fas fa-mobile-alt"></i> Uygulamalar</a>
            <a href="skills.php" class="nav-link"><i class="fas fa-code"></i> Yetenekler</a>
            <a href="projects.php" class="nav-link"><i class="fas fa-project-diagram"></i> Projeler</a>
            <a href="timeline.php" class="nav-link active"><i class="fas fa-stream"></i> Timeline</a>
            <a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Ayarlar</a>
        </nav>
        <a href="logout.php"
            style="padding:20px; color:#f85149; text-decoration:none; border-top:1px solid var(--border);"><i
                class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h1>Zaman Tüneli (Timeline)</h1>

            <?php if ($msg): ?>
                <div
                    style="background:#23863622; color:#3fb950; padding:15px; border-radius:6px; margin-bottom:20px; border:1px solid var(--accent);">
                    <?php echo $msg == 'deleted' ? 'Olay silindi.' : $msg; ?></div>
            <?php endif; ?>

            <form method="POST" id="syncForm">
                <button type="submit" name="auto_sync" class="btn"
                    style="background:#1f6feb; color:white; padding:12px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; margin-bottom:20px;">
                    <i class="fas fa-magic"></i> "Sihirli Eşitleme" (Mevcut Projelerden Olay Oluştur)
                </button>
            </form>

            <div class="card">
                <h3 id="formTitle">Yeni Manuel Olay Ekle</h3>
                <form method="POST">
                    <input type="hidden" name="event_id" id="eventId">
                    <div style="display:flex; gap:15px;">
                        <input type="text" name="title_tr" id="evTitleTr" placeholder="Başlık (TR)" required>
                        <input type="text" name="title_en" id="evTitleEn" placeholder="Başlık (EN)" required>
                    </div>
                    <div style="display:flex; gap:15px;">
                        <textarea name="desc_tr" id="evDescTr" placeholder="Açıklama (TR)"></textarea>
                        <textarea name="desc_en" id="evDescEn" placeholder="Açıklama (EN)"></textarea>
                    </div>
                    <div style="display:flex; gap:15px;">
                        <input type="text" name="event_date" id="evDate" placeholder="Tarih (Örn: Haz 2024)">
                        <select name="type" id="evType">
                            <option value="app">Uygulama</option>
                            <option value="project">Proje</option>
                            <option value="work">İş Deneyimi</option>
                            <option value="education">Eğitim</option>
                            <option value="milestone">Dönüm Noktası</option>
                        </select>
                    </div>
                    <div style="display:flex; gap:15px;">
                        <input type="text" name="icon" id="evIcon" placeholder="İkon (e.g. rocket)" style="flex:1;">
                        <div style="flex:1;">
                            <div class="color-picker-wrapper">
                                <div id="colorPreview" class="color-preview" style="background:#238636;"></div>
                                <input type="color" name="color" id="timelineColor" value="#238636"
                                    class="color-input-hidden" oninput="syncColorPicker(this.value)">
                                <span id="colorCode" class="color-code-text">#238636</span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="save_event" id="saveBtn"
                        style="background:var(--accent); color:white; font-weight:bold; padding:15px; border:none; border-radius:6px; width:100%; cursor:pointer;">Olayı
                        Kaydet</button>
                    <button type="button" onclick="resetForm()" id="cancelBtn"
                        style="display:none; margin-top:10px; background:#30363d; border:none; color:white; padding:10px; border-radius:6px; width:100%; cursor:pointer;">İptal</button>
                </form>
            </div>

            <div class="card">
                <h3>Akış Sıralaması</h3>
                <div id="timeline-list">
                    <?php foreach ($events as $ev): ?>
                        <div class="item" data-id="<?php echo $ev['id']; ?>"
                            data-title-tr="<?php echo htmlspecialchars($ev['title_tr']); ?>"
                            data-title-en="<?php echo htmlspecialchars($ev['title_en']); ?>"
                            data-desc-tr="<?php echo htmlspecialchars($ev['desc_tr']); ?>"
                            data-desc-en="<?php echo htmlspecialchars($ev['desc_en']); ?>"
                            data-date="<?php echo htmlspecialchars($ev['event_date']); ?>"
                            data-type="<?php echo $ev['type']; ?>" data-icon="<?php echo $ev['icon']; ?>"
                            data-color="<?php echo $ev['color']; ?>">
                            <div class="handler"><i class="fas fa-grip-lines"></i></div>
                            <div
                                style="width:40px; height:40px; border-radius:50%; background:<?php echo $ev['color']; ?>22; display:flex; align-items:center; justify-content:center; margin-right:15px;">
                                <i class="<?php echo getTimelineIconPrefix($ev['icon']); ?> fa-<?php echo $ev['icon']; ?>"
                                    style="color:<?php echo $ev['color']; ?>;"></i>
                            </div>
                            <div style="flex:1;">
                                <div style="font-weight:bold;"><?php echo htmlspecialchars($ev['title_tr']); ?></div>
                                <div style="font-size:0.8rem; color:var(--text-dim);"><?php echo $ev['event_date']; ?></div>
                            </div>
                            <div style="display:flex; gap:10px;">
                                <button type="button" onclick="editEvent(this)"
                                    style="background:none; border:none; color:#58a6ff; cursor:pointer;"><i
                                        class="fas fa-edit"></i></button>
                                <button type="button"
                                    onclick="openDeleteModal('?delete=<?php echo $ev['id']; ?>', '<?php echo htmlspecialchars($ev['title_tr']); ?> silinsin mi?')"
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
        function syncColorPicker(color) { document.getElementById('colorPreview').style.backgroundColor = color; document.getElementById('colorCode').innerText = color.toUpperCase(); }
        const el = document.getElementById('timeline-list');
        Sortable.create(el, {
            animation: 150, onEnd: async function () {
                const order = Array.from(el.children).map(item => item.getAttribute('data-id'));
                await fetch('update_order.php', { method: 'POST', body: JSON.stringify({ table: 'timeline', order: order }), headers: { 'Content-Type': 'application/json' } });
            }
        });

        function editEvent(btn) {
            const item = btn.closest('.item');
            document.getElementById('eventId').value = item.dataset.id;
            document.getElementById('evTitleTr').value = item.dataset.titleTr;
            document.getElementById('evTitleEn').value = item.dataset.titleEn;
            document.getElementById('evDescTr').value = item.dataset.descTr;
            document.getElementById('evDescEn').value = item.dataset.descEn;
            document.getElementById('evDate').value = item.dataset.date;
            document.getElementById('evType').value = item.dataset.type;
            document.getElementById('evIcon').value = item.dataset.icon;
            syncColorPicker(item.dataset.color);
            document.getElementById('timelineColor').value = item.dataset.color;
            document.getElementById('formTitle').innerText = "Olayı Düzenle";
            document.getElementById('saveBtn').innerText = "Güncelle";
            document.getElementById('cancelBtn').style.display = 'block';
            document.getElementById('syncForm').style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('eventId').value = ""; document.getElementById('evTitleTr').value = ""; document.getElementById('evTitleEn').value = ""; document.getElementById('evDescTr').value = ""; document.getElementById('evDescEn').value = ""; document.getElementById('evDate').value = ""; document.getElementById('evIcon').value = "";
            document.getElementById('formTitle').innerText = "Yeni Manuel Olay Ekle";
            document.getElementById('saveBtn').innerText = "Olayı Kaydet";
            document.getElementById('cancelBtn').style.display = 'none';
            document.getElementById('syncForm').style.display = 'block';
        }

        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        function toggleSidebar() { sidebar.classList.toggle('show'); overlay.classList.toggle('show'); }
        menuToggle.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', toggleSidebar);
        function openDeleteModal(url, text) { document.getElementById('modalText').innerText = text; document.getElementById('confirmAction').href = url; document.getElementById('confirmModal').classList.add('show'); }
        function closeModal() { document.getElementById('confirmModal').classList.remove('show'); }
        window.onclick = function (event) { if (event.target == document.getElementById('confirmModal')) closeModal(); }
    </script>
</body>

</html>