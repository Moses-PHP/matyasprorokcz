<?php
require __DIR__ . '/config.php';
session_start();

$db = getDB();

// --- CRUD akce ---
$message = '';
$editEvent = null;

// Smazání
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare('DELETE FROM events WHERE id = ?');
    $stmt->execute([(int) $_GET['delete']]);
    header('Location: index.php?msg=deleted');
    exit;
}

// Načtení eventu pro editaci
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editEvent = $stmt->fetch();
}

// Uložení (přidání / úprava)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'           => trim($_POST['title'] ?? ''),
        'event_date'      => $_POST['event_date'] ?? '',
        'time_start'      => $_POST['time_start'] ?: null,
        'time_end'        => $_POST['time_end'] ?: null,
        'venue'           => trim($_POST['venue'] ?? ''),
        'city'            => trim($_POST['city'] ?? ''),
        'description'     => trim($_POST['description'] ?? ''),
        'admission'       => trim($_POST['admission'] ?? ''),
        'badge'           => trim($_POST['badge'] ?? ''),
        'additional_info' => trim($_POST['additional_info'] ?? ''),
        'is_upcoming'     => isset($_POST['is_upcoming']) ? 1 : 0,
    ];

    // Upload obrázku
    $imagePath = $_POST['existing_image'] ?? '';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = realpath(__DIR__ . '/../../images') . '/';
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array($ext, $allowed)) {
            $filename = 'event_' . date('Ymd_His') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $imagePath = '../images/' . $filename;
            }
        }
    }
    $data['image_path'] = $imagePath;

    if (!empty($data['title']) && !empty($data['event_date'])) {
        if (!empty($_POST['event_id'])) {
            // Úprava
            $sql = 'UPDATE events SET title=:title, event_date=:event_date, time_start=:time_start, time_end=:time_end, venue=:venue, city=:city, description=:description, admission=:admission, badge=:badge, image_path=:image_path, additional_info=:additional_info, is_upcoming=:is_upcoming WHERE id=:id';
            $data['id'] = (int) $_POST['event_id'];
            $db->prepare($sql)->execute($data);
            header('Location: index.php?msg=updated');
            exit;
        } else {
            // Přidání
            $sql = 'INSERT INTO events (title, event_date, time_start, time_end, venue, city, description, admission, badge, image_path, additional_info, is_upcoming) VALUES (:title, :event_date, :time_start, :time_end, :venue, :city, :description, :admission, :badge, :image_path, :additional_info, :is_upcoming)';
            $db->prepare($sql)->execute($data);
            header('Location: index.php?msg=created');
            exit;
        }
    } else {
        $message = 'Vyplňte alespoň název a datum.';
    }
}

// Flash zprávy
if (isset($_GET['msg'])) {
    $msgs = ['created' => 'Event vytvořen.', 'updated' => 'Event upraven.', 'deleted' => 'Event smazán.'];
    $message = $msgs[$_GET['msg']] ?? '';
}

// Filtr
$filter = $_GET['filter'] ?? 'all';
$where = '';
if ($filter === 'upcoming') $where = 'WHERE is_upcoming = 1';
elseif ($filter === 'past') $where = 'WHERE is_upcoming = 0';

$events = $db->query("SELECT * FROM events $where ORDER BY event_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gmajor6 – Správa eventů</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<style>
:root {
    --dark: #0d0d0d; --dark2: #111; --dark3: #1a1a1a;
    --gold: #c9a84c; --gold-light: #e0c97a;
    --text: #e8e4dc; --text-muted: #8a8578;
    --border-alpha: rgba(201,168,76,.15);
    --card-border: rgba(201,168,76,.08);
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; background: var(--dark); color: var(--text); min-height: 100vh; }

/* Header */
.admin-header {
    background: var(--dark2); border-bottom: 1px solid var(--border-alpha);
    padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center;
}
.admin-header h1 { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--gold); }
.admin-header a { color: var(--text-muted); text-decoration: none; font-size: .85rem; }
.admin-header a:hover { color: var(--gold); }

/* Container */
.admin-container { max-width: 1100px; margin: 0 auto; padding: 2rem; }

/* Message */
.msg { padding: .8rem 1.2rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: .9rem; border: 1px solid var(--gold); background: rgba(201,168,76,.1); color: var(--gold-light); }
.msg.error { border-color: #ff5050; background: rgba(255,80,80,.1); color: #ff5050; }

/* Filters */
.filters { display: flex; gap: .5rem; margin-bottom: 1.5rem; }
.filters a {
    padding: .5rem 1rem; border-radius: 4px; text-decoration: none; font-size: .82rem; font-weight: 500;
    border: 1px solid var(--border-alpha); color: var(--text-muted); transition: all .2s;
}
.filters a:hover, .filters a.active { background: rgba(201,168,76,.15); border-color: var(--gold); color: var(--gold); }

/* Table */
.events-table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
.events-table th { text-align: left; padding: .8rem 1rem; font-size: .72rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-muted); border-bottom: 1px solid var(--border-alpha); }
.events-table td { padding: .8rem 1rem; font-size: .9rem; border-bottom: 1px solid var(--card-border); vertical-align: middle; }
.events-table tr:hover td { background: rgba(201,168,76,.03); }
.events-table .thumb { width: 50px; height: 35px; object-fit: cover; border-radius: 4px; }
.badge-upcoming { background: rgba(201,168,76,.15); border: 1px solid var(--gold); color: var(--gold); padding: .2rem .6rem; border-radius: 3px; font-size: .7rem; font-weight: 600; }
.badge-past { background: rgba(138,133,120,.15); border: 1px solid var(--text-muted); color: var(--text-muted); padding: .2rem .6rem; border-radius: 3px; font-size: .7rem; font-weight: 600; }

/* Action buttons */
.btn { display: inline-block; padding: .4rem .8rem; border-radius: 4px; text-decoration: none; font-size: .8rem; font-weight: 500; transition: all .2s; cursor: pointer; border: none; }
.btn-edit { background: rgba(201,168,76,.15); border: 1px solid var(--gold); color: var(--gold); }
.btn-edit:hover { background: rgba(201,168,76,.3); }
.btn-delete { background: rgba(255,80,80,.1); border: 1px solid #ff5050; color: #ff5050; }
.btn-delete:hover { background: rgba(255,80,80,.25); }
.btn-add { background: var(--gold); color: var(--dark); font-weight: 600; padding: .6rem 1.2rem; font-size: .85rem; }
.btn-add:hover { background: var(--gold-light); }

/* Form */
.form-section { background: var(--dark3); border: 1px solid var(--card-border); border-radius: 8px; padding: 2rem; margin-bottom: 2rem; }
.form-section h2 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--gold); margin-bottom: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: .3rem; }
.form-group.full { grid-column: 1 / -1; }
.form-group label { font-size: .75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }
.form-group input, .form-group textarea, .form-group select {
    background: var(--dark2); border: 1px solid var(--border-alpha); border-radius: 4px;
    padding: .6rem .8rem; color: var(--text); font-family: 'Inter', sans-serif; font-size: .9rem;
    transition: border-color .2s;
}
.form-group input:focus, .form-group textarea:focus, .form-group select:focus {
    outline: none; border-color: var(--gold);
}
.form-group textarea { resize: vertical; min-height: 80px; }
.form-actions { display: flex; gap: .8rem; margin-top: 1rem; }
.form-actions .btn-add { border: none; cursor: pointer; }
.form-actions .btn-cancel { background: transparent; border: 1px solid var(--border-alpha); color: var(--text-muted); text-decoration: none; padding: .6rem 1.2rem; border-radius: 4px; font-size: .85rem; }

.checkbox-group { flex-direction: row; align-items: center; gap: .6rem; }
.checkbox-group input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--gold); }

.current-img { display: flex; align-items: center; gap: .8rem; margin-top: .3rem; }
.current-img img { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; }
.current-img span { font-size: .8rem; color: var(--text-muted); }

/* Empty */
.empty { text-align: center; padding: 3rem; color: var(--text-muted); font-size: .95rem; }

/* Responsive */
@media (max-width: 768px) {
    .form-grid { grid-template-columns: 1fr; }
    .events-table { font-size: .8rem; }
    .events-table th:nth-child(4), .events-table td:nth-child(4) { display: none; }
    .admin-container { padding: 1rem; }
}
</style>
</head>
<body>

<header class="admin-header">
    <h1>Gmajor6 — Správa eventů</h1>
    <a href="../index.html">← Zpět na web</a>
</header>

<div class="admin-container">

<?php if ($message): ?>
    <div class="msg <?= strpos($message, 'Vyplňte') !== false ? 'error' : '' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- FORMULÁŘ -->
<div class="form-section">
    <h2><?= $editEvent ? 'Upravit event' : 'Přidat nový event' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <?php if ($editEvent): ?>
            <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?>">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editEvent['image_path'] ?? '') ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-group full">
                <label>Název *</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($editEvent['title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Datum *</label>
                <input type="date" name="event_date" required value="<?= $editEvent['event_date'] ?? '' ?>">
            </div>
            <div class="form-group">
                <label>Město</label>
                <input type="text" name="city" value="<?= htmlspecialchars($editEvent['city'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Začátek</label>
                <input type="time" name="time_start" value="<?= $editEvent['time_start'] ? substr($editEvent['time_start'], 0, 5) : '' ?>">
            </div>
            <div class="form-group">
                <label>Konec</label>
                <input type="time" name="time_end" value="<?= $editEvent['time_end'] ? substr($editEvent['time_end'], 0, 5) : '' ?>">
            </div>
            <div class="form-group full">
                <label>Místo konání</label>
                <input type="text" name="venue" value="<?= htmlspecialchars($editEvent['venue'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Vstupné</label>
                <input type="text" name="admission" value="<?= htmlspecialchars($editEvent['admission'] ?? '') ?>" placeholder="Vstupné dobrovolné · Open door od 19:00">
            </div>
            <div class="form-group">
                <label>Odznak (badge)</label>
                <input type="text" name="badge" value="<?= htmlspecialchars($editEvent['badge'] ?? '') ?>" placeholder="Volný vstup / Sold out">
            </div>
            <div class="form-group full">
                <label>Popis</label>
                <textarea name="description"><?= htmlspecialchars($editEvent['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group full">
                <label>Doplňující info</label>
                <input type="text" name="additional_info" value="<?= htmlspecialchars($editEvent['additional_info'] ?? '') ?>" placeholder="Hudební doprovod: ...">
            </div>
            <div class="form-group">
                <label>Obrázek (plakát)</label>
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($editEvent['image_path'])): ?>
                    <div class="current-img">
                        <img src="<?= htmlspecialchars($editEvent['image_path']) ?>" alt="Aktuální obrázek">
                        <span>Aktuální obrázek</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group checkbox-group" style="align-self:end;">
                <input type="checkbox" name="is_upcoming" id="is_upcoming" <?= ($editEvent['is_upcoming'] ?? 1) ? 'checked' : '' ?>>
                <label for="is_upcoming" style="text-transform:none;letter-spacing:0;font-size:.9rem;">Nadcházející akce</label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-add"><?= $editEvent ? 'Uložit změny' : 'Přidat event' ?></button>
            <?php if ($editEvent): ?>
                <a href="index.php" class="btn-cancel">Zrušit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- FILTRY -->
<div class="filters">
    <a href="index.php" class="<?= $filter === 'all' ? 'active' : '' ?>">Všechny</a>
    <a href="index.php?filter=upcoming" class="<?= $filter === 'upcoming' ? 'active' : '' ?>">Nadcházející</a>
    <a href="index.php?filter=past" class="<?= $filter === 'past' ? 'active' : '' ?>">Uplynulé</a>
</div>

<!-- TABULKA -->
<?php if (empty($events)): ?>
    <div class="empty">Žádné eventy k zobrazení.</div>
<?php else: ?>
<table class="events-table">
    <thead>
        <tr>
            <th></th>
            <th>Datum</th>
            <th>Název</th>
            <th>Místo</th>
            <th>Status</th>
            <th>Akce</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($events as $ev): ?>
        <tr>
            <td>
                <?php if ($ev['image_path']): ?>
                    <img src="<?= htmlspecialchars($ev['image_path']) ?>" alt="" class="thumb">
                <?php endif; ?>
            </td>
            <td><?= date('j. n. Y', strtotime($ev['event_date'])) ?></td>
            <td><strong><?= htmlspecialchars($ev['title']) ?></strong></td>
            <td><?= htmlspecialchars($ev['venue'] ?: $ev['city'] ?: '—') ?></td>
            <td>
                <span class="<?= $ev['is_upcoming'] ? 'badge-upcoming' : 'badge-past' ?>">
                    <?= $ev['is_upcoming'] ? 'Nadcházející' : 'Uplynulá' ?>
                </span>
            </td>
            <td>
                <a href="index.php?edit=<?= $ev['id'] ?>" class="btn btn-edit">Upravit</a>
                <a href="index.php?delete=<?= $ev['id'] ?>" class="btn btn-delete" onclick="return confirm('Opravdu smazat tento event?')">Smazat</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

</div>
</body>
</html>
