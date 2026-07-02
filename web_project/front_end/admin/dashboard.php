<?php
session_start();
require_once "../../back_end/config/conn.php";

/* ===== TEMP ADMIN LOGIN ===== */
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'admin';
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ===== DASHBOARD STATS ===== */
$totalUsers = mysqli_fetch_assoc(mysqli_query($con,
    "SELECT COUNT(*) total FROM user"
))['total'];

$totalVendors = mysqli_fetch_assoc(mysqli_query($con,
    "SELECT COUNT(*) total FROM user WHERE role='vendor'"
))['total'];

$pendingActions = mysqli_fetch_assoc(mysqli_query($con,
    "SELECT COUNT(*) total FROM challenge_proof WHERE status='Pending'"
))['total'];

$totalChallenges = mysqli_fetch_assoc(mysqli_query($con,
    "SELECT COUNT(*) total FROM challenge"
))['total'];

/* ===== USER DISTRIBUTION ===== */
$userRoles = ['student'=>0,'vendor'=>0,'admin'=>0];
$q = mysqli_query($con,"SELECT role, COUNT(*) total FROM user GROUP BY role");
while($r=mysqli_fetch_assoc($q)){
    $userRoles[$r['role']] = $r['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="../container.css">

<style>
body{
    background:#EFFDF4;
    font-family:Arial;
    margin:0;
}

/* ===== STATS CARD ===== */
.stat-card{
    color:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,.15);
}

.green{background:#5cbf75;}
.blue{background:#3f8ad8;}
.orange{background:#d87b2e;}
.purple{background:#8a2be2;}

.stat-card h4{
    margin:0 0 10px;
    font-weight:normal;
}
.stat-card h2{
    margin:0;
}

/* ===== CARD ===== */
.card{
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,.15);
}

.card small{color:#777}

.moderation-item{
    background:#eee;
    height:60px;
    border-radius:6px;
    margin:10px 0;
}

.view-btn{
    background:#6c9cff;
    color:#fff;
    border:none;
    padding:10px;
    width:100%;
    border-radius:6px;
    cursor:pointer;
}
</style>
</head>

<body>

<!-- ===== DASHBOARD STATS ===== -->
<div class="row" style="padding:20px">
    <div class="col-3 col-s-6">
        <div class="stat-card green">
            <h4>Total Users</h4>
            <h2><?= $totalUsers ?></h2>
        </div>
    </div>

    <div class="col-3 col-s-6">
        <div class="stat-card blue">
            <h4>Total Vendors</h4>
            <h2><?= $totalVendors ?></h2>
        </div>
    </div>

    <div class="col-3 col-s-6">
        <div class="stat-card orange">
            <h4>Pending Action</h4>
            <h2><?= $pendingActions ?></h2>
        </div>
    </div>

    <div class="col-3 col-s-6">
        <div class="stat-card purple">
            <h4>Challenge</h4>
            <h2><?= $totalChallenges ?></h2>
        </div>
    </div>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="row" style="padding:20px">

    <!-- USER DISTRIBUTION -->
    <div class="col-6 col-s-12">
        <div class="card">
            <h3>User Distribution</h3>
            <small>Breakdown by user role</small>

            <div style="height:260px;margin-top:15px">
                <canvas id="userChart"></canvas>
            </div>
        </div>
    </div>

    <!-- CONTENT MODERATION -->
    <div class="col-6 col-s-12">
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <h3>Content Moderation</h3>
                <span style="background:#f57c00;color:#fff;padding:4px 10px;border-radius:20px">
                    <?= $pendingActions ?>
                </span>
            </div>
            <small>Challenge proofs and user submissions</small>

            <div class="moderation-item"></div>
            <div class="moderation-item"></div>
            <div class="moderation-item"></div>

            <button class="view-btn" onclick="location.href='moderation.php'">View all</button>
        </div>
    </div>

</div>

<script>
new Chart(userChart,{
    type:'pie',
    data:{
        labels:['Student','Vendor','Admin'],
        datasets:[{
            data:[
                <?= $userRoles['student'] ?>,
                <?= $userRoles['vendor'] ?>,
                <?= $userRoles['admin'] ?>
            ],
            backgroundColor:['#5cbf75','#3f8ad8','#8a2be2']
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false
    }
});
</script>

</body>
</html>
