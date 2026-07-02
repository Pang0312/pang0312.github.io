<?php
session_start();
require_once "../../back_end/config/conn.php";

/* ========= CONFIG: change if your DB uses different names ========= */
$T_LEFTOVER = "leftover";
$T_CLAIM    = "leftover_claim";

$F_ID       = "leftover_id";
$F_VENDORID = "vendor_id";
$F_NAME     = "food_name";
$F_QTY      = "quantity";       // or qty_available
$F_IMG      = "img";            // or image_path
$F_STATUS   = "status";         // optional: 'Available'/'Unavailable'
$F_CREATED  = "created_at";     // optional
/* ================================================================ */

/* ===== TEMP VENDOR LOGIN FOR TESTING (remove when login is ready) ===== */
if (!isset($_SESSION['role'])) $_SESSION['role'] = 'vendor';
if (!isset($_SESSION['vendor_id'])) $_SESSION['vendor_id'] = 1; // change to an existing vendor_id
/* ===================================================================== */

if ($_SESSION['role'] !== 'vendor') {
  header("Location: ../login.php");
  exit;
}

$vendor_id = (int)$_SESSION['vendor_id'];

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ---------- helpers: upload image ---------- */
function saveUpload($inputName, $targetDirRel = "../img/uploads/") {
  if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) return null;
  if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) return null;

  $maxBytes = 2 * 1024 * 1024; // 2MB
  if ($_FILES[$inputName]['size'] > $maxBytes) return null;

  $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
  $allow = ["jpg","jpeg","png","webp"];
  if (!in_array($ext, $allow, true)) return null;

  $absDir = __DIR__ . "/" . $targetDirRel;
  if (!is_dir($absDir)) @mkdir($absDir, 0777, true);

  $newName = "food_" . time() . "_" . rand(1000,9999) . "." . $ext;
  $absPath = $absDir . $newName;

  if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $absPath)) return null;

  // store as web path relative to /front_end
  return "img/uploads/" . $newName;
}

/* =======================
   HANDLE ACTIONS
======================= */
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* Create */
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['food_name'] ?? '');
  $qty  = (int)($_POST['quantity'] ?? 0);
  $status = trim($_POST['status'] ?? 'Available');
  $imgPath = saveUpload('photo'); // can be null

  if ($name !== '' && $qty > 0) {
    $sql = "INSERT INTO $T_LEFTOVER ($F_VENDORID, $F_NAME, $F_QTY, $F_STATUS, $F_IMG)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "isiss", $vendor_id, $name, $qty, $status, $imgPath);
    mysqli_stmt_execute($stmt);
  }
  header("Location: food_listing.php");
  exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id   = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['food_name'] ?? '');
  $qty  = (int)($_POST['quantity'] ?? 0);
  $status = trim($_POST['status'] ?? 'Available');

  // fetch current img (so edit without re-upload can keep it)
  $currentImg = null;
  $q = mysqli_prepare($con, "SELECT $F_IMG FROM $T_LEFTOVER WHERE $F_ID=? AND $F_VENDORID=? LIMIT 1");
  mysqli_stmt_bind_param($q, "ii", $id, $vendor_id);
  mysqli_stmt_execute($q);
  $res = mysqli_stmt_get_result($q);
  if ($row = mysqli_fetch_assoc($res)) $currentImg = $row[$F_IMG] ?? null;

  $newImg = saveUpload('photo');
  $imgPath = $newImg ?? $currentImg;

  if ($id > 0 && $name !== '' && $qty > 0) {
    $sql = "UPDATE $T_LEFTOVER
            SET $F_NAME=?, $F_QTY=?, $F_STATUS=?, $F_IMG=?
            WHERE $F_ID=? AND $F_VENDORID=?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "sissii", $name, $qty, $status, $imgPath, $id, $vendor_id);
    mysqli_stmt_execute($stmt);
  }
  header("Location: food_listing.php");
  exit;
}

if ($action === 'delete') {
  $id = (int)($_GET['id'] ?? 0);
  if ($id > 0) {
    $stmt = mysqli_prepare($con, "DELETE FROM $T_LEFTOVER WHERE $F_ID=? AND $F_VENDORID=?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $vendor_id);
    mysqli_stmt_execute($stmt);
  }
  header("Location: food_listing.php");
  exit;
}

/* =======================
   FETCH LISTINGS
======================= */
$listings = [];
$sql = "
  SELECT l.*,
         (SELECT COUNT(*) FROM $T_CLAIM c WHERE c.$F_ID = l.$F_ID) AS claimed_count
  FROM $T_LEFTOVER l
  WHERE l.$F_VENDORID=?
  ORDER BY l.$F_ID DESC
";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $vendor_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while($r = mysqli_fetch_assoc($res)) $listings[] = $r;


$editRow = null;
if (isset($_GET['edit'])) {
  $eid = (int)$_GET['edit'];
  $q = mysqli_prepare($con, "SELECT * FROM $T_LEFTOVER WHERE $F_ID=? AND $F_VENDORID=? LIMIT 1");
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
<title>Food Listing</title>
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
  .btn{
    border:0;border-radius:6px;padding:10px 14px;cursor:pointer;font-weight:700;
  }
  .btn-green{background:#47a447;color:#fff;}
  .btn-blue{background:#5a92ff;color:#fff;}
  .btn-gray{background:#ddd;color:#000;}
  .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:16px;}
  @media(max-width:900px){ .grid{grid-template-columns:1fr;} }
  .card{
    background:#fff;border-radius:10px;padding:16px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
  }
  .row2{display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;}
  .muted{color:#777;font-size:13px;}
  .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;background:#eef5ff;}
  .actions{display:flex;gap:10px;margin-top:14px;}
  .imgbox{width:100%;height:150px;border-radius:8px;background:#f1f1f1;display:flex;align-items:center;justify-content:center;overflow:hidden;}
  .imgbox img{width:100%;height:100%;object-fit:cover;}
  

  .modal-backdrop{
    position:fixed;inset:0;background:rgba(0,0,0,.35);
    display:flex;align-items:center;justify-content:center;padding:18px;
  }
  .modal{
    width:min(720px,100%);background:#fff;border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,.25);padding:18px;position:relative;
  }
  .close{
    position:absolute;right:12px;top:10px;border:0;background:transparent;
    font-size:20px;cursor:pointer;
  }
  .field{margin-top:10px;}
  .label{font-size:13px;color:#333;margin-bottom:6px;}
  input[type=text],input[type=number],select{
    width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;
  }
  .two{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
  @media(max-width:700px){ .two{grid-template-columns:1fr;} }
</style>
</head>
<body>

<?php include "../nav_bar.php"; ?>

<div class="page">
  <div class="top-card">
    <div>
      <div style="font-size:18px;font-weight:800;">Food Availability</div>
      <div class="subtitle">Manage your food listings and share with the community</div>
    </div>
    <a class="btn btn-green" href="food_listing.php?modal=create">Add Listing</a>
  </div>

  <div class="grid">
    <?php if(empty($listings)): ?>
      <div class="card" style="grid-column:1/-1;">
        <b>No listing yet.</b>
        <div class="muted">Click “Add Listing” to create one.</div>
      </div>
    <?php endif; ?>

    <?php foreach($listings as $l): ?>
      <div class="card">
        <div class="imgbox">
          <?php if(!empty($l[$F_IMG])): ?>
            <img src="../<?= h($l[$F_IMG]) ?>" alt="food">
          <?php else: ?>
            <div class="muted">No photo</div>
          <?php endif; ?>
        </div>

        <div style="margin-top:12px;font-size:16px;font-weight:800;">
          <?= h($l[$F_NAME] ?? '-') ?>
        </div>

        <div class="row2" style="margin-top:10px;">
          <div>
            <div class="muted">Available</div>
            <div style="font-weight:800;"><?= (int)($l[$F_QTY] ?? 0) ?></div>
          </div>
          <div>
            <div class="muted">Claimed</div>
            <div style="font-weight:800;"><?= (int)($l['claimed_count'] ?? 0) ?></div>
          </div>
          <div>
            <div class="muted">Status</div>
            <div class="pill"><?= h($l[$F_STATUS] ?? 'Available') ?></div>
          </div>
        </div>

        <div class="actions">
          <a class="btn btn-blue" style="text-decoration:none;" href="food_listing.php?edit=<?= (int)$l[$F_ID] ?>">Edit</a>
          <a class="btn btn-gray" style="text-decoration:none;"
             href="food_listing.php?action=delete&id=<?= (int)$l[$F_ID] ?>"
             onclick="return confirm('Delete this listing?')">Delete</a>
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
  $actionUrl = $isEdit ? "food_listing.php" : "food_listing.php";
?>
<div class="modal-backdrop" onclick="location.href='food_listing.php'">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="close" onclick="location.href='food_listing.php'">×</button>

    <div style="font-size:18px;font-weight:900;"><?= $isEdit ? "Edit Food Listing" : "Create Food Listing" ?></div>
    <div class="muted" style="margin-top:6px;">Share food available with students</div>

    <form method="post" enctype="multipart/form-data" style="margin-top:10px;">
      <input type="hidden" name="action" value="<?= $isEdit ? "update" : "create" ?>">
      <?php if($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$editRow[$F_ID] ?>">
      <?php endif; ?>

      <div class="field">
        <div class="label">Food Name *</div>
        <input type="text" name="food_name" required
               value="<?= $isEdit ? h($editRow[$F_NAME] ?? '') : '' ?>"
               placeholder="e.g. Chicken Sandwiches">
      </div>

      <div class="two">
        <div class="field">
          <div class="label">Upload photo <?= $isEdit ? "" : "*" ?></div>
          <input type="file" name="photo" accept="image/*">
          <div class="muted" style="margin-top:6px;">JPG/PNG/WebP, max 2MB</div>
        </div>

        <div class="field">
          <div class="label">Quantity Available *</div>
          <input type="number" name="quantity" min="1" required
                 value="<?= $isEdit ? (int)($editRow[$F_QTY] ?? 1) : 10 ?>">
        </div>
      </div>

      <div class="field">
        <div class="label">Status</div>
        <select name="status">
          <?php $cur = $isEdit ? ($editRow[$F_STATUS] ?? 'Available') : 'Available'; ?>
          <option value="Available"   <?= $cur==='Available'?'selected':'' ?>>Available</option>
          <option value="Unavailable" <?= $cur==='Unavailable'?'selected':'' ?>>Unavailable</option>
        </select>
      </div>

      <div style="margin-top:14px;">
        <button class="btn btn-green" style="width:100%;padding:12px 16px;">
          <?= $isEdit ? "Save Changes" : "Create Listing" ?>
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

</body>
</html>