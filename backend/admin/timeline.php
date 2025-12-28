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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_timeline'])) {
    Security::checkCSRF();
    if (isset($_POST['id']) && $_POST['id']) {
        $stmt = $db->prepare("UPDATE timeline SET title_tr = ?, desc_tr = ?, event_date = ?, type = ?, icon = ?, color = ? WHERE id = ?");
        $stmt->execute([$_POST['title_tr'], $_POST['desc_tr'], $_POST['event_date'], $_POST['type'], $_POST['icon'], $_POST['color'], $_POST['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO timeline (title_tr, desc_tr, event_date, type, icon, color, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $maxOrder = $db->query("SELECT MAX(sort_order) as max FROM timeline")->fetch()['max'] ?? 0;
        $stmt->execute([$_POST['title_tr'], $_POST['desc_tr'], $_POST['event_date'], $_POST['type'], $_POST['icon'], $_POST['color'], $maxOrder + 1]);
    }
    $success = 'Zaman çizelgesi güncellendi!';
}

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM timeline WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: timeline.php');
    exit;
}

$timeline = $db->query("SELECT * FROM timeline ORDER BY sort_order ASC")->fetchAll();
$pageTitle = 'Timeline - Admin';
require_once 'includes/header.php';
?>

<h1>⏱️ Timeline</h1>
<?php if ($success): ?>
    <div class="alert-success"><?php echo $success; ?></div><?php endif; ?>

<button onclick="openModal()" class="btn btn-success" style="margin-bottom: 20px;">Yeni Etkinlik</button>

<?php foreach ($timeline as $item): ?>
    <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 16px;">
        <div
            style="width: 44px; height: 44px; background: rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <img src="<?php echo Security::e($item['icon']); ?>" style="width: 100%; height: 100%; object-fit: contain;"
                onerror="this.src='https://via.placeholder.com/44?text=Item'">
        </div>
        <div style="flex: 1;">
            <h4 style="margin: 0; font-size: 15px;"><?php echo Security::e($item['title_tr']); ?></h4>
            <p style="color: #94a3b8; font-size: 13px; margin: 4px 0;"><?php echo Security::e($item['event_date']); ?></p>
        </div>
        <div style="display: flex; gap: 10px;">
            <i onclick="editTimeline(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="fas fa-edit"
                style="color:#3b82f6; cursor:pointer;"></i>
            <a href="?delete=<?php echo $item['id']; ?>" onclick="return confirm('Silinsin mi?')" style="color:#ef4444;"><i
                    class="fas fa-trash"></i></a>
        </div>
    </div>
<?php endforeach; ?>

<!-- Modal -->
<div id="timelineModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Etkinlik Düzenle</h3><button onclick="closeModal()" class="close-modal">&times;</button>
        </div>
        <form method="POST">
            <?php echo Security::csrfInput(); ?><input type="hidden" name="id" id="item_id">
            <label>Başlık</label><input type="text" name="title_tr" id="title_tr" required>
            <label>Tarih/Dönem</label><input type="text" name="event_date" id="event_date" required
                placeholder="Ocak 2024">
            <label>İkon URL</label><input type="url" name="icon" id="item_icon" required>
            <div class="form-row">
                <div style="flex: 2;"><label>Tip</label><select name="type" id="item_type">
                        <option value="project">Proje</option>
                        <option value="work">İş</option>
                    </select></div>
                <div style="flex: 1;"><label>Renk</label><input type="color" name="color" id="item_color"
                        value="#3b82f6" style="height: 45px; padding: 2px;"></div>
            </div>
            <label>Açıklama</label><textarea name="desc_tr" id="desc_tr" rows="2"></textarea>
            <button type="submit" name="save_timeline" class="btn btn-primary"
                style="width: 100%; margin-top: 10px;">Kaydet</button>
        </form>
    </div>
</div>

<script>
    function openModal() { document.getElementById('timelineModal').classList.add('show'); document.getElementById('item_id').value = ''; }
    function closeModal() { document.getElementById('timelineModal').classList.remove('show'); }
    function editTimeline(item) {
        document.getElementById('item_id').value = item.id;
        document.getElementById('title_tr').value = item.title_tr;
        document.getElementById('event_date').value = item.event_date;
        document.getElementById('item_icon').value = item.icon;
        document.getElementById('item_type').value = item.type;
        document.getElementById('item_color').value = item.color;
        document.getElementById('desc_tr').value = item.desc_tr;
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
        width: 90%;
        max-width: 500px;
    }
</style>

<?php require_once 'includes/navbar.php';
require_once 'includes/footer.php'; ?>