<?php
    session_start();
    require_once "../../back_end/config/conn.php";    

    /* ===== TEMP ADMIN LOGIN ===== */
    if (!isset($_SESSION['role'])) {
        $_SESSION['role'] = 'admin';
    }    

    /* ===== PROTECT PAGE ===== */
    if ($_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }    

    /* ===== CREATE USER ===== */
    if (isset($_POST['create_user'])) {    

        $fullname = mysqli_real_escape_string($con, $_POST['fullname']);
        $email    = mysqli_real_escape_string($con, $_POST['email']);
        $password = $_POST['password'];
        $confirm  = $_POST['confirm_password'];
        $role     = $_POST['role'];    

        if ($password !== $confirm) {
            echo "<script>alert('Password not match');</script>";
        } else {    

            $hash = password_hash($password, PASSWORD_DEFAULT);    

            mysqli_query($con, "
                INSERT INTO user (email, password, role, status)
                VALUES ('$email', '$hash', '$role', 'active')
            ") or die(mysqli_error($con));    

            $user_id = mysqli_insert_id($con);    

            if ($role === 'student') {
                mysqli_query($con,"
                    INSERT INTO student (user_id, student_name)
                    VALUES ($user_id, '$fullname')
                ");
            }    

            if ($role === 'vendor') {
                mysqli_query($con,"
                    INSERT INTO vendor (user_id, vendor_name)
                    VALUES ($user_id, '$fullname')
                ");
            }    

            echo "<script>
                alert('Account created successfully');
                window.location.href='manage_user.php';
            </script>";
        }
    }    

    /* ===== SUSPEND / BAN ===== */
    if (isset($_POST['action'], $_POST['user_id'])) {    

        $user_id = (int)$_POST['user_id'];
        $action  = $_POST['action'];    

        if ($action === 'suspend') {
            $status = 'suspended';
        } elseif ($action === 'ban') {
            $status = 'banned';
        } else {
            exit;
        }    

        mysqli_query($con,"
            UPDATE user
            SET status = '$status'
            WHERE user_id = $user_id
        ");    

        header("Location: manage_user.php");
        exit;
    }    

    /* ===== FETCH USERS ===== */
    $where = [];    

    if (!empty($_GET['keyword'])) {
        $kw = mysqli_real_escape_string($con, $_GET['keyword']);
        $where[] = "(u.email LIKE '%$kw%' 
                     OR s.student_name LIKE '%$kw%' 
                     OR v.vendor_name LIKE '%$kw%')";
    }    

    if (!empty($_GET['role'])) {
        $role = mysqli_real_escape_string($con, $_GET['role']);
        $where[] = "u.role = '$role'";
    }    

    $whereSQL = '';
    if (!empty($where)) {
        $whereSQL = 'WHERE ' . implode(' AND ', $where);
    }    

    $users = mysqli_query($con, "
        SELECT 
            u.user_id,
            u.email,
            u.role,
            u.status,
            COALESCE(s.student_name, v.vendor_name, 'Admin') AS fullname
        FROM user u
        LEFT JOIN student s ON u.user_id = s.user_id
        LEFT JOIN vendor v ON u.user_id = v.user_id
        $whereSQL
    ");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>    

    <link rel="stylesheet" href="../container.css">    

    <style>
        body {
            background:#EFFDF4;
            font-family: Arial;
            margin:0;
        }        

        /* PAGE CARD */
        .page-card {
            background:#fff;
            margin:20px;
            padding:20px;
            border-radius:10px;
            box-shadow:0 2px 6px rgba(0,0,0,.1);
        }        

        /* HEADER */
        .page-header {
            display:flex;
            justify-content:space-between;
            align-items:center;
        }        

        .create-btn {
            background:#4CAF50;
            color:#fff;
            border:none;
            padding:10px 20px;
            border-radius:6px;
            cursor:pointer;
        }        

        /* SEARCH */
        .search-bar {
            display:flex;
            gap:10px;
            margin-top:15px;
        }        

        .search-bar input,
        .search-bar select {
            padding:8px;
            border-radius:6px;
            border:1px solid #ccc;
            width:100%;
        }        

        /* USER CARD */
        .user-card {
            background:#fff;
            margin:20px;
            padding:20px;
            border-radius:10px;
            box-shadow:0 2px 6px rgba(0,0,0,.1);
            display:grid;
            grid-template-columns: 1fr 180px 220px;
            align-items:center;
        }        

        .user-middle {
            text-align:center;
            font-weight:600;
        }        

        .user-actions {
            text-align:right;
        }        

        .user-actions .challenge-link {
            display:block;
            margin-bottom:8px;
            color:#4CAF50;
            font-weight:600;
            text-decoration:none;
        }        

        .btn-group button {
            margin-left:6px;
            padding:8px 14px;
            border-radius:6px;
            border:1px solid #ccc;
            cursor:pointer;
        }        

        .user-info small {
            color:#666;
        }        

        .status {
            display:inline-block;
            padding:4px 10px;
            border-radius:20px;
            font-size:12px;
        }        

        .active {
            background:#d4edda;
            color:#2e7d32;
        }        

        .suspended {
            background:#fff3cd;
            color:#856404;
        }        

        .banned {
            background:#f8d7da;
            color:#721c24;
        }        

        /* RIGHT SIDE */
        .user-right {
            display:flex;
            flex-direction:column;
            align-items:flex-end;
            gap:8px;
            min-width:200px;
        }        

        .role-text {
            font-weight:600;
        }        

        .challenge-link {
            color:#4CAF50;
            font-size:13px;
            font-weight:600;
            text-decoration:none;
        }        

        .suspend { color:#ff9800; }
        .ban { color:#f44336; }        

        /* MODAL */
        .modal {
            display:none;
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.4);
            justify-content:center;
            align-items:center;
        }        

        .modal-content {
            background:#fff;
            padding:25px;
            border-radius:10px;
            width:400px;
            position:relative;
        }        

        .modal-content input,
        .modal-content select {
            width:100%;
            padding:8px;
            margin:8px 0 15px;
            border-radius:6px;
            border:1px solid #ccc;
        }        

        .close {
            position:absolute;
            top:10px;
            right:15px;
            font-size:20px;
            cursor:pointer;
        }
    </style>
</head>

<body>

<div class="page-card">
    <div class="page-header">
        <h2>User Management</h2>
        <button class="create-btn" onclick="openModal()">Create Account</button>
    </div>

    <form method="GET" class="search-bar">
        <input 
            type="text" 
            name="keyword" 
            placeholder="Search by name or email..."
            value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>"
        >

        <select name="role">
            <option value="">All Roles</option>
            <option value="student" <?= ($_GET['role'] ?? '')==='student'?'selected':'' ?>>Student</option>
            <option value="vendor" <?= ($_GET['role'] ?? '')==='vendor'?'selected':'' ?>>Vendor</option>
            <option value="admin" <?= ($_GET['role'] ?? '')==='admin'?'selected':'' ?>>Admin</option>
        </select>

        <button type="submit" class="create-btn">Search</button>
    </form>

</div>

<?php while($u = mysqli_fetch_assoc($users)) { ?>
    <div class="user-card">

    <div class="user-info">
        <strong><?= htmlspecialchars($u['fullname']) ?></strong><br>
        <small><?= htmlspecialchars($u['email']) ?></small><br>
        <span class="status <?= $u['status'] ?>">
            <?= ucfirst($u['status']) ?>
        </span>
    </div>

    <!-- MIDDLE ROLE -->
    <div class="user-middle">
        Role: <?= ucfirst($u['role']) ?>
    </div>

    <!-- RIGHT SIDE -->
    <div class="user-actions">

        <?php if ($u['role'] === 'student') { ?>
            <a href="challenge_log.php?user_id=<?= $u['user_id'] ?>" class="challenge-link">
                View Challenge Logs
            </a>
        <?php } ?>

        <div class="btn-group">
            <?php if ($u['role'] === 'admin') { ?>
                <small style="color:#999;font-style:italic;">Admin Protected</small>
            <?php } else { ?>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to suspend this account?');">
                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                    <input type="hidden" name="action" value="suspend">
                    <button type="submit" class="suspend">Suspend</button>
                </form>

                <form method="POST" style="display:inline;" onsubmit="return confirm('⚠️ Are you sure you want to BAN this account? This action is serious.');">
                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                    <input type="hidden" name="action" value="ban">
                    <button type="submit" class="ban">Ban</button>
                </form>
            <?php } ?>
        </div>

    </div>

</div>

<?php } ?>

<div class="modal" id="createModal">
    <div class="modal-content">
    <span class="close" onclick="closeModal()">×</span>
    <h3>Create New Account</h3>

    <form method="POST">
        <input type="text" name="fullname" placeholder="Full Name *" required>
        <input type="email" name="email" placeholder="Email *" required>
        <input type="password" name="password" placeholder="Password *" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password *" required>    

        <select name="role" required>
            <option value="">Role *</option>
            <option value="student">Student</option>
            <option value="vendor">Vendor</option>
            <option value="admin">Admin</option>
        </select>    

        <button type="submit" name="create_user" class="create-btn" style="width:100%">
            Create Account
        </button>
    </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('createModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('createModal').style.display = 'none';
}
</script>

</body>
</html>
