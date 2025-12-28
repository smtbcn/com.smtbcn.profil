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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_skill'])) {
    Security::checkCSRF();
    if (isset($_POST['id']) && $_POST['id']) {
        $stmt = $db->prepare("UPDATE skills SET name = ?, icon = ?, color = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['icon'], $_POST['color'], $_POST['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO skills (name, icon, color, sort_order) VALUES (?, ?, ?, ?)");
        $maxOrder = $db->query("SELECT MAX(sort_order) as max FROM skills")->fetch()['max'] ?? 0;
        $stmt->execute([$_POST['name'], $_POST['icon'], $_POST['color'], $maxOrder + 1]);
    }
    $success = 'Yetenek başarıyla güncellendi!';
}

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM skills WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: skills.php');
    exit;
}

$skills = $db->query("SELECT * FROM skills ORDER BY sort_order ASC")->fetchAll();
$pageTitle = 'Yetenekler - Admin';
require_once 'includes/header.php';
?>

<h1>⭐ Yetenekler</h1>
<p style="color: #94a3b8; margin-bottom: 24px;">URL tabanlı dinamik ikonlarla yönetin.</p>

<?php if ($success): ?>
    <div class="alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<button onclick="openModal()" class="btn btn-success" style="margin-bottom: 24px;">
    <i class="fas fa-plus"></i> Yeni Yetenek
</button>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 12px;">
    <?php foreach ($skills as $skill): ?>
        <div class="card" style="padding: 16px; text-align: center;">
            <div
                style="width: 50px; height: 50px; background: rgba(59,130,246,0.05); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; overflow: hidden;">
                <img src="<?php echo Security::e($skill['icon']); ?>"
                    style="width: 100%; height: 100%; object-fit: contain;"
                    onerror="this.src='https://via.placeholder.com/50?text=Skill'">
            </div>
            <h4 style="font-size: 13px; font-weight: 600; margin-bottom: 12px;"><?php echo Security::e($skill['name']); ?>
            </h4>
            <div style="display: flex; gap: 8px; justify-content: center;">
                <i onclick="editSkill(<?php echo htmlspecialchars(json_encode($skill)); ?>)" class="fas fa-edit"
                    style="color:#3b82f6; cursor:pointer;"></i>
                <a href="?delete=<?php echo $skill['id']; ?>" onclick="return confirm('Silinsin mi?')"
                    style="color:#ef4444;"><i class="fas fa-trash"></i></a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="skillModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Yetenek Düzenle</h3><button onclick="closeModal()" class="close-modal">&times;</button>
        </div>
        <form method="POST">
            <?php echo Security::csrfInput(); ?><input type="hidden" name="id" id="skill_id">
            <label>Yetenek Adı</label>
            <input type="text" name="name" id="name" required>
            <label>Icon URL (Resim Linki)</label>
            <input type="url" name="icon" id="skill_icon" required placeholder="https://cdn.worldvectorlogo.com/...">
            <label>Renk</label>
            <input type="color" name="color" id="skill_color" value="#3b82f6">
            <button type="submit" name="save_skill" class="btn btn-primary"
                style="width: 100%; margin-top: 10px;">Kaydet</button>
        </form>
    </div>
</div>

<script>
    function openModal() { document.getElementById('skillModal').classList.add('show'); document.getElementById('skill_id').value = ''; }
    function closeModal() { document.getElementById('skillModal').classList.remove('show'); }
    function editSkill(skill) {
        document.getElementById('skill_id').value = skill.id;
        document.getElementById('name').value = skill.name;
        document.getElementById('skill_icon').value = skill.icon;
        document.getElementById('skill_color').value = skill.color;
        openModal();
    }
</script>
<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
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
        border-radius: 16px;
        padding: 24px;
    }
</style>

<?php require_once 'includes/navbar.php';
require_once 'includes/footer.php'; ?>