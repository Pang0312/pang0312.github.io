<?php
session_start();
require_once "../../back_end/config/conn.php";

/* ========= CONFIG: change if your DB uses different names ========= */
$T_EVENT    = "event";          // or vendor_event
$F_ID       = "event_id";
$F_VENDORID = "vendor_id";
$F_TITLE    = "title";
$F_DESC     = "description";
$F_START    = "start_date";
$F_END      = "end_date";
$F_STATUS   = "status";         // optional
/* ================================================================ */

/* ===== TEMP VENDOR LOGIN FOR TESTING (remove when login is ready) ===== */
if (!isset($_SESSION['role'])) $_SESSION['role'] = 'vendor';
if (!isset($_SESSION['vendor_id'])) $_SESSION['vendor_id'] = 1;
/* ===================================================================== */

if ($_SESSION['role'] !== 'vendor') {
  header("Location: ../login.php");
  exit;
}

$vendor_id = (int)$_SESSION['vendor_id'];
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* Create */
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $desc  = trim($_POST['description'] ?? '');
  $start = trim($_POST['start_date'] ?? '');
  $end   = trim($_POST['end_date'] ?? '');
  $status= trim($_POST['status'] ?? 'Active');

  if ($title !== '' && $desc !== '' && $start !== '' && $end !== '') {
    $sql = "INSERT INTO $T_EVENT ($F_VENDORID,$F_TITLE,$F_DESC,$F_START,$F_END,$F_STATUS)
            VALUES (?,?,?,?,?,?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "isssss", $vendor_id, $title, $desc, $start, $end, $status);
    mysqli_stmt_execute($stmt);
  }
  header("Location: event.php");
  exit;
}

/* Update */
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id    = (int)($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $desc  = trim($_POST['description'] ?? '');
  $start = trim($_POST['start_date'] ?? '');
  $end   = trim($_POST['end_date'] ?? '');
  $status= trim($_POST['status'] ?? 'Active');

  if ($id>0 && $title!=='' && $desc!=='' && $start!=='' && $end!=='') {
    $sql = "UPDATE $T_EVENT
            SET $F_TITLE=?, $F_DESC=?, $F_START=?, $F_END=?, $F_STATUS=?
            WHERE $F_ID=? AND $F_VENDORID=?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "sssssii", $title,$desc,$start,$end,$status,$id,$vendor_id);
    mysqli_stmt_execute($stmt);
  }
  header("Location: event.php");
  exit;
}

/* Delete */
if ($action === 'delete') {
  $id = (int)($_GET['id'] ?? 0);
  if ($id > 0) {
    $stmt = mysqli_prepare($con, "DELETE FROM $T_EVENT WHERE $F_ID=? AND $F_VENDORID=?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $vendor_id);
    mysqli_stmt_execute($stmt);
  }
  header("Location: event.php");
  exit;
}

/* Fetch events */
$events = [];
$stmt = mysqli_prepare($con, "SELECT * FROM $T_EVENT WHERE $F_VENDORID=? ORDER BY $F_ID DESC");
mysqli_stmt_bind_param($stmt, "i", $vendor_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while($r=mysqli_fetch_assoc($res)) $events[]=$r;

/* Edit modal */
$editRow = null;
if (isset($_GET['edit'])) {
  $eid = (int)$_GET['edit'];
  $q = mysqli_prepare($con, "SELECT * FROM $T_EVENT WHERE $F_ID=? AND $F_VENDORID=? LIMIT 1");
  mysqli_stmt_bind_param($q, "ii", $eid, $vendor_id);
  mysqli_stmt_execute($q);
  $rr = mysqli_stmt_get_result($q);
  $editRow = mysqli_fetch_assoc($rr) ?: null;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Event</title>
<link rel="stylesheet" href="../container.css">
<style>
  body{margin:0;font-family:Arial;background:#f3fbf3;}
  .page{padding:18px;}
  .top-card{
    background:#fff;border-radius:10px;padding:16px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
    display:flex;align-items:center;justify-content:space-between;gap:12px;
  }
  .subtitle{color:#666;margin-top:6px;font-size:14px;}
  .btn{border:0;border-radius:6px;padding:10px 14px;cursor:pointer;font-weight:700;}
  .btn-green{background:#47a447;color:#fff;}
  .btn-blue{background:#5a92ff;color:#fff;}
  .btn-gray{background:#ddd;color:#000;}
  .list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:16px;}
  @media(max-width:900px){ .list{grid-template-columns:1fr;} }
  .card{
    background:#fff;border-radius:10px;padding:16px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
  }
  .muted{color:#777;font-size:13px;}
  .actions{display:flex;gap:10px;margin-top:14px;}
  /* modal */
  .modal-backdrop{
    position:fixed;inset:0;background:rgba(0,0,0,.35);
    display:flex;align-items:center;justify-content:center;padding:18px;
  }
  .modal{
    width:min(720px,100%);background:#fff;border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,.25);padding:18px;position:relative;
  }
  .close{position:absolute;right:12px;top:10px;border:0;background:transparent;font-size:20px;cursor:pointer;}
  .field{margin-top:10px;}
  .label{font-size:13px;color:#333;margin-bottom:6px;}
  input[type=text],textarea,input[type=date],select{
    width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;
  }
  textarea{min-height:90px;resize:vertical;}
  .two{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
  @media(max-width:700px){ .two{grid-template-columns:1fr;} }
</style>
</head>
<body>

<?php include "../nav_bar.php"; ?>

<div class="page">
  <div class="top-card">
    <div>
      <div style="font-size:18px;font-weight:800;">Vendor Events</div>
      <div class="subtitle">Create and manage sustainability events for your APU</div>
    </div>
    <a class="btn btn-green" href="event.php?modal=create">Create Event</a>
  </div>

  <div class="list">
    <?php if(empty($events)): ?>
      <div class="card" style="grid-column:1/-1;">
        <b>No events yet.</b>
        <div class="muted">Click “Create Event” to add one.</div>
      </div>
    <?php endif; ?>

    <?php foreach($events as $e): ?>
      <div class="card">
        <div style="font-size:16px;font-weight:900;"><?= h($e[$F_TITLE] ?? '-') ?></div>
        <div class="muted" style="margin-top:6px;"><?= h($e[$F_DESC] ?? '') ?></div>

        <div style="margin-top:10px;font-size:14px;">
          <div><b>Start Date:</b> <?= h($e[$F_START] ?? '-') ?></div>
          <div><b>End Date:</b> <?= h($e[$F_END] ?? '-') ?></div>
          <div><b>Status:</b> <?= h($e[$F_STATUS] ?? 'Active') ?></div>
        </div>

        <div class="actions">
          <a class="btn btn-blue" style="text-decoration:none;" href="event.php?edit=<?= (int)$e[$F_ID] ?>">Edit Event</a>
          <a class="btn btn-gray" style="text-decoration:none;"
             href="event.php?action=delete&id=<?= (int)$e[$F_ID] ?>"
             onclick="return confirm('Delete this event?')">Delete</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
$showCreate = (isset($_GET['modal']) && $_GET['modal']==='create');
$showEdit   = (!empty($editRow));
if($showCreate || $showEdit):
  $isEdit = $showEdit;
?>
<div class="modal-backdrop" onclick="location.href='event.php'">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="close" onclick="location.href='event.php'">×</button>

    <div style="font-size:18px;font-weight:900;"><?= $isEdit ? "Edit Event" : "Create New Event" ?></div>
    <div class="muted" style="margin-top:6px;">Create and manage sustainability events for your APU</div>

    <form method="post" style="margin-top:10px;">
      <input type="hidden" name="action" value="<?= $isEdit ? "update" : "create" ?>">
      <?php if($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$editRow[$F_ID] ?>">
      <?php endif; ?>

      <div class="field">
        <div class="label">Event Title *</div>
        <input type="text" name="title" required
          value="<?= $isEdit ? h($editRow[$F_TITLE] ?? '') : '' ?>">
      </div>

      <div class="field">
        <div class="label">Description *</div>
        <textarea name="description" required><?= $isEdit ? h($editRow[$F_DESC] ?? '') : '' ?></textarea>
      </div>

      <div class="two">
        <div class="field">
          <div class="label">Start Date *</div>
          <input type="date" name="start_date" required
            value="<?= $isEdit ? h($editRow[$F_START] ?? '') : '' ?>">
        </div>
        <div class="field">
          <div class="label">End Date *</div>
          <input type="date" name="end_date" required
            value="<?= $isEdit ? h($editRow[$F_END] ?? '') : '' ?>">
        </div>
      </div>

      <div class="field">
        <div class="label">Status</div>
        <?php $cur = $isEdit ? ($editRow[$F_STATUS] ?? 'Active') : 'Active'; ?>
        <select name="status">
          <option value="Active" <?= $cur==='Active'?'selected':'' ?>>Active</option>
          <option value="Inactive" <?= $cur==='Inactive'?'selected':'' ?>>Inactive</option>
        </select>
      </div>

      <div style="margin-top:14px;">
        <button class="btn btn-green" style="width:100%;padding:12px 16px;">
          <?= $isEdit ? "Edit Event" : "Create Event" ?>
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

</body>
</html>