<?php
session_start();
require_once "../../back_end/config/conn.php";

/* ========= CONFIG: change if your DB uses different names ========= */
$T_VENDOR   = "vendor";
$F_ID       = "vendor_id";
$F_NAME     = "vendor_name";
$F_EMAIL    = "email";
$F_PASS     = "password";       // if you store hashed, keep using password_hash
$F_PHOTO    = "photo";          // or image
$F_LOCATION = "location";       // optional
$F_RATING   = "rating";         // optional
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

  $newName = "vendor_" . time() . "_" . rand(1000,9999) . "." . $ext;
  $absPath = $absDir . $newName;

  if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $absPath)) return null;
  return "img/uploads/" . $newName; // stored relative to /front_end
}

$msg = "";

/* Fetch current vendor */
$vendor = null;
$stmt = mysqli_prepare($con, "SELECT * FROM $T_VENDOR WHERE $F_ID=? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $vendor_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$vendor = mysqli_fetch_assoc($res);

if (!$vendor) {
  die("Vendor not found. Check vendor_id session or DB data.");
}

/* Update profile */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
  $name  = trim($_POST['vendor_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = trim($_POST['password'] ?? '');
  $pass2 = trim($_POST['confirm_password'] ?? '');

  // keep old photo if not upload
  $newPhoto = saveUpload('photo');
  $photoPath = $newPhoto ?? ($vendor[$F_PHOTO] ?? null);

  if ($name === '' || $email === '') {
    $msg = "Name and Email are required.";
  } else if ($pass !== '' && $pass !== $pass2) {
    $msg = "Password and Confirm Password not match.";
  } else {
    // password: only update if user filled it
    if ($pass !== '') {
      $hashed = password_hash($pass, PASSWORD_DEFAULT);
      $sql = "UPDATE $T_VENDOR SET $F_NAME=?, $F_EMAIL=?, $F_PASS=?, $F_PHOTO=? WHERE $F_ID=?";
      $stmt = mysqli_prepare($con, $sql);
      mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $hashed, $photoPath, $vendor_id);
      mysqli_stmt_execute($stmt);
    } else {
      $sql = "UPDATE $T_VENDOR SET $F_NAME=?, $F_EMAIL=?, $F_PHOTO=? WHERE $F_ID=?";
      $stmt = mysqli_prepare($con, $sql);
      mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $photoPath, $vendor_id);
      mysqli_stmt_execute($stmt);
    }

    header("Location: profile.php?saved=1");
    exit;
  }
}

/* Re-fetch after update or for display */
$stmt = mysqli_prepare($con, "SELECT * FROM $T_VENDOR WHERE $F_ID=? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $vendor_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$vendor = mysqli_fetch_assoc($res);

if (isset($_GET['saved'])) $msg = "Saved successfully!";
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Profile</title>
<link rel="stylesheet" href="../container.css">
<style>
  body{margin:0;font-family:Arial;background:#f3fbf3;}
  .page{padding:18px;}
  .layout{display:grid;grid-template-columns:1.25fr .75fr;gap:16px;}
  @media(max-width:900px){ .layout{grid-template-columns:1fr;} }

  .card{
    background:#fff;border-radius:10px;padding:16px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
  }
  .muted{color:#777;font-size:13px;}
  .field{margin-top:12px;}
  .label{font-size:13px;color:#333;margin-bottom:6px;}
  input[type=text],input[type=email],input[type=password]{
    width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;
  }
  .btn{border:0;border-radius:6px;padding:10px 14px;cursor:pointer;font-weight:700;}
  .btn-green{background:#47a447;color:#fff;}
  .btn-gray{background:#ddd;color:#000;}
  .row2{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
  @media(max-width:700px){ .row2{grid-template-columns:1fr;} }

  .photoRow{display:flex;gap:12px;align-items:center;}
  .photoBox{
    width:80px;height:80px;border:1px solid #ddd;border-radius:6px;
    overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f7f7f7;
  }
  .photoBox img{width:100%;height:100%;object-fit:cover;}
  .statRow{display:flex;align-items:center;justify-content:space-between;background:#f5f8ff;border-radius:10px;padding:12px;margin-top:10px;}
  .pill{padding:4px 10px;border-radius:999px;background:#dff7ef;color:#1f7a58;font-size:12px;font-weight:800;}
</style>
</head>
<body>

<?php include "../nav_bar.php"; ?>

<div class="page">
  <div class="layout">

    <!-- Left: Profile info -->
    <div class="card">
      <div style="font-size:18px;font-weight:900;">Profile information</div>
      <div class="muted" style="margin-top:6px;">Manage your business information and contact details</div>

      <?php if($msg): ?>
        <div style="margin-top:12px;padding:10px;border-radius:8px;background:#f3f7ff;">
          <?= h($msg) ?>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" style="margin-top:14px;">
        <input type="hidden" name="action" value="save">

        <div class="photoRow">
          <div class="photoBox">
            <?php if(!empty($vendor[$F_PHOTO])): ?>
              <img src="../<?= h($vendor[$F_PHOTO]) ?>" alt="photo">
            <?php else: ?>
              <div class="muted">photo</div>
            <?php endif; ?>
          </div>
          <div>
            <input type="file" name="photo" accept="image/*">
            <div class="muted" style="margin-top:6px;">JPG or PNG, max 2MB</div>
          </div>
        </div>

        <div class="field">
          <div class="label">Vendor Name *</div>
          <input type="text" name="vendor_name" required value="<?= h($vendor[$F_NAME] ?? '') ?>">
        </div>

        <div class="field">
          <div class="label">Email *</div>
          <input type="email" name="email" required value="<?= h($vendor[$F_EMAIL] ?? '') ?>">
        </div>

        <div class="row2">
          <div class="field">
            <div class="label">Password *</div>
            <input type="password" name="password" placeholder="Leave blank to keep current">
          </div>
          <div class="field">
            <div class="label">Confirm Password *</div>
            <input type="password" name="confirm_password" placeholder="Repeat password">
          </div>
        </div>

        <div class="row2" style="margin-top:14px;">
          <button class="btn btn-green" type="submit">Save Changes</button>
          <a class="btn btn-gray" style="text-decoration:none;text-align:center;" href="profile.php">Cancel</a>
        </div>
      </form>
    </div>

    <!-- Right: Stats -->
    <div class="card">
      <div style="font-size:16px;font-weight:900;">Stats Overview</div>

      <div class="statRow">
        <div>Role</div>
        <span class="pill">Vendor</span>
      </div>

      <div class="statRow" style="background:#f3fff3;">
        <div>Location</div>
        <span class="pill" style="background:#e4ffe8;color:#1f7a2d;">
          <?= h($vendor[$F_LOCATION] ?? 'APU Cafeteria') ?>
        </span>
      </div>

      <div class="statRow" style="background:#f7f3ff;">
        <div>Rating</div>
        <span class="pill" style="background:#eadcff;color:#5a2bbd;">
          <?= h($vendor[$F_RATING] ?? '0') ?>
        </span>
      </div>

      <div class="muted" style="margin-top:10px;">* Connected to database.</div>
    </div>

  </div>
</div>

</body>
</html>