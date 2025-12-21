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
$error = "";

// Yetenek Ekleme / Güncelleme
if (isset($_POST['save_skill'])) {
    try {
        $id = $_POST['skill_id'] ?? '';
        if ($id) {
            // Güncelleme
            $stmt = $db->prepare("UPDATE skills SET name = ?, color = ?, icon = ? WHERE id = ?");
            $stmt->execute([$_POST['name'], $_POST['color'], $_POST['icon'], $id]);
            $msg = "Yetenek başarıyla güncellendi!";
        } else {
            // Yeni Ekleme
            $stmt = $db->prepare("INSERT INTO skills (name, color, icon, sort_order) VALUES (?, ?, ?, 0)");
            $stmt->execute([$_POST['name'], $_POST['color'], $_POST['icon']]);
            $msg = "Yetenek başarıyla eklendi!";
        }
    } catch (Exception $e) {
        $error = "Hata oluştu: " . $e->getMessage();
    }
}

// Silme İşlemi
if (isset($_GET['delete'])) {
    try {
        $stmt = $db->prepare("DELETE FROM skills WHERE id = ?");
        $stmt->execute([(int) $_GET['delete']]);
        header('Location: skills.php?msg=deleted');
        exit;
    } catch (Exception $e) {
        $error = "Silme hatası: " . $e->getMessage();
    }
}

$skills = $db->query("SELECT * FROM skills ORDER BY sort_order ASC, id DESC")->fetchAll();

// İkon Listesi
$common_icons = [
    ['id' => 'react', 'prefix' => 'fab', 'label' => 'React Native', 'color' => '#61DAFB'],
    ['id' => 'js', 'prefix' => 'fab', 'label' => 'JavaScript', 'color' => '#F7DF1E'],
    ['id' => 'node-js', 'prefix' => 'fab', 'label' => 'Node.js', 'color' => '#339933'],
    ['id' => 'php', 'prefix' => 'fab', 'label' => 'PHP', 'color' => '#777BB4'],
    ['id' => 'laravel', 'prefix' => 'fab', 'label' => 'Laravel', 'color' => '#FF2D20'],
    ['id' => 'database', 'prefix' => 'fas', 'label' => 'MySQL / SQL', 'color' => '#4479A1'],
    ['id' => 'code', 'prefix' => 'fas', 'label' => 'C# / .NET', 'color' => '#178600'],
    ['id' => 'apple', 'prefix' => 'fab', 'label' => 'Swift / iOS', 'color' => '#FFFFFF'],
    ['id' => 'android', 'prefix' => 'fab', 'label' => 'Kotlin / Android', 'color' => '#3DDC84'],
    ['id' => 'github', 'prefix' => 'fab', 'label' => 'GitHub', 'color' => '#FFFFFF'],
    ['id' => 'css3-alt', 'prefix' => 'fab', 'label' => 'CSS3', 'color' => '#1572B6'],
    ['id' => 'html5', 'prefix' => 'fab', 'label' => 'HTML5', 'color' => '#E34F26'],
    ['id' => 'sass', 'prefix' => 'fab', 'label' => 'Sass', 'color' => '#CC6699'],
    ['id' => 'figma', 'prefix' => 'fab', 'label' => 'Figma', 'color' => '#F24E1E'],
    ['id' => 'docker', 'prefix' => 'fab', 'label' => 'Docker', 'color' => '#2496ED'],
    ['id' => 'aws', 'prefix' => 'fab', 'label' => 'AWS', 'color' => '#FF9900'],
    ['id' => 'python', 'prefix' => 'fab', 'label' => 'Python', 'color' => '#3776AB']
];

function getIconPrefix($iconId, $icons)
{
    foreach ($icons as $i) {
        if ($i['id'] == $iconId)
            return $i['prefix'];
    }
    return 'fas';
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yetenek Yönetimi - SMTBCN</title>
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

        input[type="text"],
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

        input[type="text"]:focus,
        select:focus {
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
            box-sizing: border-box;
            position: relative;
        }

        .color-preview {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        .color-input-hidden {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .color-code-text {
            font-family: monospace;
            color: var(--text-dim);
            flex: 1;
            pointer-events: none;
        }

        .btn-success {
            background: var(--accent);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            font-size: 1rem;
            transition: 0.3s;
        }

        .btn-success:hover {
            background: #2ea043;
            transform: translateY(-1px);
        }

        .skill-item {
            display: flex;
            align-items: center;
            background: var(--bg-main);
            border: 1px solid var(--border);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: grab;
            transition: 0.2s;
        }

        .handler {
            margin-right: 15px;
            color: var(--text-dim);
            font-size: 1.2rem;
        }

        .skill-icon-preview {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-right: 15px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
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

    <!-- Confirm Modal -->
    <div class="modal" id="confirmModal">
        <div class="modal-content">
            <div class="modal-title" id="modalTitle">Emin misiniz?</div>
            <div class="modal-text" id="modalText">Bu öğeyi silmek istediğinize emin misiniz?</div>
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
            <a href="skills.php" class="nav-link active"><i class="fas fa-code"></i> Yetenekler</a>
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
            <h1>Yetenek Yönetimi</h1>

            <?php if ($msg): ?>
                <div
                    style="background:#23863622; color:#3fb950; padding:15px; border-radius:6px; margin-bottom:20px; border:1px solid var(--accent);">
                    <?php echo $msg == 'deleted' ? 'Yetenek başarıyla silindi.' : $msg; ?></div>
            <?php elseif ($error): ?>
                <div
                    style="background:#f8514922; color:#f85149; padding:15px; border-radius:6px; margin-bottom:20px; border:1px solid var(--danger);">
                    <?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <h3 id="formTitle">Yeni Yetenek Ekle</h3>
                <form method="POST" id="skillForm">
                    <input type="hidden" name="skill_id" id="skillId">
                    <input type="text" name="name" id="skillName" placeholder="Yetenek Adı" required>
                    <div style="display:flex; gap:15px; margin-bottom:15px;">
                        <div style="flex:2;">
                            <select name="icon" id="skillIcon" onchange="updateBrandColor()">
                                <option value="" disabled selected>Bir teknoloji seçin...</option>
                                <?php foreach ($common_icons as $ic): ?>
                                    <option value="<?php echo $ic['id']; ?>" data-color="<?php echo $ic['color']; ?>"
                                        data-label="<?php echo $ic['label']; ?>"><?php echo $ic['label']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <div class="color-picker-wrapper">
                                <div id="colorPreview" class="color-preview" style="background:#61DAFB;"></div>
                                <input type="color" name="color" id="skillColor" value="#61DAFB"
                                    class="color-input-hidden" oninput="syncColorPicker(this.value)">
                                <span id="colorCode" class="color-code-text">#61DAFB</span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="save_skill" id="saveBtn" class="btn-success">Yetenek Oluştur</button>
                    <button type="button" onclick="resetForm()" id="cancelEdit"
                        style="display:none; margin-top:10px; background:#30363d; border:none; color:white; padding:10px; border-radius:6px; width:100%; cursor:pointer;">İptal</button>
                </form>
            </div>

            <div class="card">
                <h3>Yetenek Sıralaması</h3>
                <div id="skills-list">
                    <?php foreach ($skills as $s):
                        $prefix = getIconPrefix($s['icon'] ?? 'code', $common_icons); ?>
                        <div class="skill-item" data-id="<?php echo $s['id']; ?>"
                            data-name="<?php echo htmlspecialchars($s['name']); ?>" data-icon="<?php echo $s['icon']; ?>"
                            data-color="<?php echo $s['color']; ?>">
                            <div class="handler"><i class="fas fa-grip-lines"></i></div>
                            <div class="skill-icon-preview" style="color: <?php echo $s['color']; ?>;"><i
                                    class="<?php echo $prefix; ?> fa-<?php echo htmlspecialchars($s['icon'] ?? 'code'); ?>"></i>
                            </div>
                            <div style="flex:1; font-weight:600;"><?php echo htmlspecialchars($s['name']); ?></div>
                            <div style="display:flex; gap:10px;">
                                <button type="button" onclick="editSkill(this)"
                                    style="background:none; border:none; color:#58a6ff; cursor:pointer;"><i
                                        class="fas fa-edit"></i></button>
                                <button type="button"
                                    onclick="openDeleteModal('?delete=<?php echo $s['id']; ?>', '<?php echo htmlspecialchars($s['name']); ?> silinsin mi?')"
                                    style="background:none; border:none; color:#f85149; cursor:pointer;"><i
                                        class="fas fa-trash-alt"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateBrandColor() {
            const select = document.getElementById('skillIcon');
            const selectedOption = select.options[select.selectedIndex];
            const color = selectedOption.getAttribute('data-color');
            const label = selectedOption.getAttribute('data-label');
            if (color) { syncColorPicker(color); document.getElementById('skillColor').value = color; }
            if (!document.getElementById('skillName').value) document.getElementById('skillName').value = label;
        }
        function syncColorPicker(color) { document.getElementById('colorPreview').style.backgroundColor = color; document.getElementById('colorCode').innerText = color.toUpperCase(); }

        const el = document.getElementById('skills-list');
        Sortable.create(el, {
            animation: 150, onEnd: async function () {
                const order = Array.from(el.children).map(item => item.getAttribute('data-id'));
                await fetch('update_order.php', { method: 'POST', body: JSON.stringify({ table: 'skills', order: order }), headers: { 'Content-Type': 'application/json' } });
            }
        });

        function editSkill(btn) {
            const item = btn.closest('.skill-item');
            document.getElementById('skillId').value = item.getAttribute('data-id');
            document.getElementById('skillName').value = item.getAttribute('data-name');
            document.getElementById('skillIcon').value = item.getAttribute('data-icon');
            syncColorPicker(item.getAttribute('data-color'));
            document.getElementById('skillColor').value = item.getAttribute('data-color');
            document.getElementById('formTitle').innerText = "Yeteneği Düzenle";
            document.getElementById('saveBtn').innerText = "Güncelle";
            document.getElementById('cancelEdit').style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('skillId').value = "";
            document.getElementById('skillName').value = "";
            document.getElementById('skillIcon').value = "";
            document.getElementById('formTitle').innerText = "Yeni Yetenek Ekle";
            document.getElementById('saveBtn').innerText = "Yetenek Oluştur";
            document.getElementById('cancelEdit').style.display = 'none';
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