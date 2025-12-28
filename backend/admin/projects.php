<?php
require_once '../core/Session.php';
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Security.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

Security::initCSRF();
$db = Database::getInstance();
$success = '';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_project'])) {
    Security::checkCSRF();

    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $stmt = $db->prepare("UPDATE projects SET name = ?, description = ?, html_url = ?, language = ?, stargazers_count = ?, forks_count = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['html_url'],
            $_POST['language'],
            $_POST['stargazers_count'],
            $_POST['forks_count'],
            $_POST['id']
        ]);
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO projects (name, description, html_url, language, stargazers_count, forks_count, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $maxOrder = $db->query("SELECT MAX(sort_order) as max FROM projects")->fetch()['max'] ?? 0;
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['html_url'],
            $_POST['language'],
            $_POST['stargazers_count'],
            $_POST['forks_count'],
            $maxOrder + 1
        ]);
    }

    $success = 'Proje kaydedildi!';
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: projects.php');
    exit;
}

// Get all projects
$projects = $db->query("SELECT * FROM projects ORDER BY sort_order ASC")->fetchAll();

$pageTitle = 'Projeler - Admin';
require_once 'includes/header.php';
?>

<h1>💻 Projeler</h1>
<p style="color: #94a3b8; margin-bottom: 24px;">GitHub projelerinizi yönetin.</p>

<?php if ($success): ?>
    <div class="alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<button onclick="openModal()" class="btn btn-success" style="margin-bottom: 20px;">
    <i class="fas fa-plus"></i> Yeni Proje
</button>

<?php foreach ($projects as $project): ?>
    <div class="card">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="flex: 1;">
                <h3 style="margin: 0;"><?php echo Security::e($project['name']); ?></h3>
                <p style="color: #94a3b8; margin: 4px 0; font-size: 14px;">
                    <?php echo Security::e($project['description']); ?></p>
                <div style="display: flex; gap: 16px; margin-top: 8px; font-size: 13px; color: #94a3b8;">
                    <?php if ($project['language']): ?>
                        <span>🔵 <?php echo Security::e($project['language']); ?></span>
                    <?php endif; ?>
                    <span>⭐ <?php echo $project['stargazers_count']; ?></span>
                    <span>🔀 <?php echo $project['forks_count']; ?></span>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="editProject(<?php echo htmlspecialchars(json_encode($project)); ?>)"
                    class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i>
                </button>
                <a href="?delete=<?php echo $project['id']; ?>"
                    onclick="return confirm('Silmek istediğinize emin misiniz?')" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal -->
<div id="projectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Proje Ekle/Düzenle</h3>
            <button onclick="closeModal()" class="close-modal">&times;</button>
        </div>

        <form method="POST" id="projectForm">
            <?php echo Security::csrfInput(); ?>
            <input type="hidden" name="id" id="project_id">

            <label>Proje Adı</label>
            <input type="text" name="name" id="name" required>

            <label>Açıklama</label>
            <textarea name="description" id="description" rows="3"></textarea>

            <label>GitHub URL</label>
            <input type="url" name="html_url" id="html_url" placeholder="https://github.com/...">

            <label>Dil</label>
            <input type="text" name="language" id="language" placeholder="JavaScript">

            <div style="display: flex; gap: 12px;">
                <div style="flex: 1;">
                    <label>⭐ Yıldızlar</label>
                    <input type="number" name="stargazers_count" id="stargazers_count" value="0">
                </div>
                <div style="flex: 1;">
                    <label>🔀 Fork'lar</label>
                    <input type="number" name="forks_count" id="forks_count" value="0">
                </div>
            </div>

            <button type="submit" name="save_project" class="btn btn-primary" style="width: 100%; margin-top: 16px;">
                <i class="fas fa-save"></i> Kaydet
            </button>
        </form>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('projectModal').classList.add('show');
        document.getElementById('projectForm').reset();
        document.getElementById('project_id').value = '';
    }

    function closeModal() {
        document.getElementById('projectModal').classList.remove('show');
    }

    function editProject(project) {
        document.getElementById('project_id').value = project.id;
        document.getElementById('name').value = project.name;
        document.getElementById('description').value = project.description || '';
        document.getElementById('html_url').value = project.html_url;
        document.getElementById('language').value = project.language || '';
        document.getElementById('stargazers_count').value = project.stargazers_count;
        document.getElementById('forks_count').value = project.forks_count;
        openModal();
    }

    document.getElementById('projectModal').addEventListener('click', function (e) {
        if (e.target === this) closeModal();
    });
</script>

<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: #151932;
        border: 1px solid #2a2f4a;
        border-radius: 16px;
        padding: 24px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .close-modal {
        background: #1a1f3a;
        border: none;
        color: #e4e4e7;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 20px;
    }
</style>

<?php
require_once 'includes/navbar.php';
require_once 'includes/footer.php';
?>