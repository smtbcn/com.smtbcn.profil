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

// Kaydetme İşlemi (Ekleme / Güncelleme)
if (isset($_POST['save_project'])) {
    $id = $_POST['project_id'] ?? '';
    if ($id) {
        $stmt = $db->prepare("UPDATE projects SET name = ?, description = ?, html_url = ?, language = ?, stargazers_count = ?, forks_count = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['description'], $_POST['html_url'], $_POST['language'], $_POST['stars'], $_POST['forks'], $id]);
        $msg = "Proje başarıyla güncellendi!";
    } else {
        $stmt = $db->prepare("INSERT INTO projects (name, description, html_url, language, stargazers_count, forks_count) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['description'], $_POST['html_url'], $_POST['language'], $_POST['stars'], $_POST['forks']]);
        $msg = "Proje eklendi!";
    }
}

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([(int) $_GET['delete']]);
    header('Location: projects.php?msg=deleted');
    exit;
}

$projects = $db->query("SELECT * FROM projects ORDER BY sort_order ASC, id DESC")->fetchAll();

// Sihirli GitHub Senkronizasyonu
if (isset($_POST['bulk_github'])) {
    $url = $_POST['github_profile_url'] ?? '';
    if (!empty($url)) {
        require_once '../core/StoreScraper.php';
        $data = StoreScraper::fetchMetadata($url);
        if ($data && isset($data['type']) && $data['type'] === 'github_user') {
            $added = 0;
            $updated = 0;
            foreach ($data['repos'] as $repo) {
                $check = $db->prepare("SELECT id FROM projects WHERE html_url = ?");
                $check->execute([$repo['url']]);
                $existing = $check->fetch();
                if ($existing) {
                    $upd = $db->prepare("UPDATE projects SET description = ?, language = ?, stargazers_count = ?, forks_count = ? WHERE id = ?");
                    $upd->execute([$repo['description'], $repo['language'], $repo['stars'], $repo['forks'], $existing['id']]);
                    $updated++;
                } else {
                    $ins = $db->prepare("INSERT INTO projects (name, description, html_url, language, stargazers_count, forks_count) VALUES (?, ?, ?, ?, ?, ?)");
                    $ins->execute([$repo['name'], $repo['description'], $repo['url'], $repo['language'], $repo['stars'], $repo['forks']]);
                    $added++;
                }
            }
            header("Location: projects.php?msg=" . urlencode("Senkronizasyon Başarılı! {$added} Yeni, {$updated} Güncellendi."));
            exit;
        } else {
            $msg = "Hata: Bilgiler alınamadı.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proje Yönetimi - SMTBCN</title>
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
        textarea {
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

        .btn-github {
            background: #333;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .project-item {
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
            <div class="modal-text" id="modalText">Bu proje silinecek.</div>
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
            <a href="apps.php" class="nav-link"><i class="fas fa-mobile-alt"></i> Uygulamalar</a>
            <a href="skills.php" class="nav-link"><i class="fas fa-code"></i> Yetenekler</a>
            <a href="projects.php" class="nav-link active"><i class="fas fa-project-diagram"></i> Projeler</a>
            <a href="timeline.php" class="nav-link"><i class="fas fa-stream"></i> Timeline</a>
            <a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Ayarlar</a>
        </nav>
        <a href="logout.php"
            style="padding:20px; color:#f85149; text-decoration:none; border-top:1px solid var(--border);"><i
                class="fas fa-sign-out-alt"></i> Çıkış Yap</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h1>Proje Yönetimi</h1>

            <?php if ($msg): ?>
                <div
                    style="background:#23863622; color:#3fb950; padding:15px; border-radius:6px; margin-bottom:20px; border:1px solid var(--accent);">
                    <?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <div class="card" id="bulkSection">
                <h3><i class="fas fa-magic"></i> "Sihirli" Tüm Projeleri Çek</h3>
                <form method="POST" style="display:flex; gap:10px;">
                    <input type="text" name="github_profile_url" placeholder="GitHub Profil URL"
                        style="margin-bottom:0;" required>
                    <button type="submit" name="bulk_github" class="btn-github"><i class="fas fa-sync-alt"></i> Hepsini
                        Getir</button>
                </form>
            </div>

            <div class="card">
                <h3 id="formTitle">Yeni Proje Ekle</h3>
                <div id="fetchSection" style="display:flex; gap:10px; margin-bottom:15px;">
                    <input type="text" id="githubUrl" placeholder="Tekil GitHub URL" style="margin-bottom:0;">
                    <button class="btn-github" onclick="fetchGitHub()"><i class="fab fa-github"></i> Çek</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="project_id" id="projId">
                    <input type="text" name="name" id="projName" placeholder="Proje Adı" required>
                    <textarea name="description" id="projDesc" placeholder="Açıklama" required
                        style="height:80px;"></textarea>
                    <input type="text" name="html_url" id="projUrl" placeholder="GitHub Linki" required>
                    <input type="text" name="language" id="projLang" placeholder="Kullanılan Dil">
                    <div style="display:flex; gap:15px;">
                        <input type="number" name="stars" id="projStars" placeholder="Stars" value="0">
                        <input type="number" name="forks" id="projForks" placeholder="Forks" value="0">
                    </div>
                    <button type="submit" name="save_project" id="saveBtn" class="btn-success">Kaydet</button>
                    <button type="button" onclick="resetForm()" id="cancelBtn"
                        style="display:none; margin-top:10px; background:#30363d; border:none; color:white; padding:10px; border-radius:6px; width:100%; cursor:pointer;">İptal</button>
                </form>
            </div>

            <div class="card">
                <h3>Projeler</h3>
                <div id="projects-list">
                    <?php foreach ($projects as $p): ?>
                        <div class="project-item" data-id="<?php echo $p['id']; ?>"
                            data-name="<?php echo htmlspecialchars($p['name']); ?>"
                            data-desc="<?php echo htmlspecialchars($p['description']); ?>"
                            data-url="<?php echo htmlspecialchars($p['html_url']); ?>"
                            data-lang="<?php echo htmlspecialchars($p['language']); ?>"
                            data-stars="<?php echo $p['stargazers_count']; ?>"
                            data-forks="<?php echo $p['forks_count']; ?>">
                            <div class="handler"><i class="fas fa-grip-lines"></i></div>
                            <div style="flex:1;">
                                <div style="font-weight:bold;"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div style="font-size:0.8rem; color:var(--text-dim);">
                                    <?php echo htmlspecialchars($p['language']); ?></div>
                            </div>
                            <div style="display:flex; gap:10px;">
                                <button type="button" onclick="editProject(this)"
                                    style="background:none; border:none; color:#58a6ff; cursor:pointer;"><i
                                        class="fas fa-edit"></i></button>
                                <button type="button"
                                    onclick="openDeleteModal('?delete=<?php echo $p['id']; ?>', '<?php echo htmlspecialchars($p['name']); ?> silinsin mi?')"
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
        async function fetchGitHub() {
            const url = document.getElementById('githubUrl').value;
            if (!url) return;
            try {
                const response = await fetch('fetch_metadata.php?url=' + encodeURIComponent(url));
                const data = await response.json();
                if (data.name) {
                    document.getElementById('projName').value = data.name;
                    document.getElementById('projDesc').value = data.description;
                    document.getElementById('projUrl').value = data.url;
                    document.getElementById('projLang').value = data.language;
                    document.getElementById('projStars').value = data.stars;
                    document.getElementById('projForks').value = data.forks;
                }
            } catch (e) { alert('Hata: ' + e.message); }
        }

        const el = document.getElementById('projects-list');
        Sortable.create(el, {
            animation: 150, onEnd: async function () {
                const order = Array.from(el.children).map(item => item.getAttribute('data-id'));
                await fetch('update_order.php', { method: 'POST', body: JSON.stringify({ table: 'projects', order: order }), headers: { 'Content-Type': 'application/json' } });
            }
        });

        function editProject(btn) {
            const item = btn.closest('.project-item');
            document.getElementById('projId').value = item.dataset.id;
            document.getElementById('projName').value = item.dataset.name;
            document.getElementById('projDesc').value = item.dataset.desc;
            document.getElementById('projUrl').value = item.dataset.url;
            document.getElementById('projLang').value = item.dataset.lang;
            document.getElementById('projStars').value = item.dataset.stars;
            document.getElementById('projForks').value = item.dataset.forks;
            document.getElementById('formTitle').innerText = "Projeyi Düzenle";
            document.getElementById('saveBtn').innerText = "Güncelle";
            document.getElementById('cancelBtn').style.display = 'block';
            document.getElementById('fetchSection').style.display = 'none';
            document.getElementById('bulkSection').style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('projId').value = "";
            document.getElementById('projName').value = "";
            document.getElementById('projDesc').value = "";
            document.getElementById('projUrl').value = "";
            document.getElementById('projLang').value = "";
            document.getElementById('projStars').value = "0";
            document.getElementById('projForks').value = "0";
            document.getElementById('formTitle').innerText = "Yeni Proje Ekle";
            document.getElementById('saveBtn').innerText = "Kaydet";
            document.getElementById('cancelBtn').style.display = 'none';
            document.getElementById('fetchSection').style.display = 'flex';
            document.getElementById('bulkSection').style.display = 'block';
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