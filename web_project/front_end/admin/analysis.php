<?php
    session_start();
    require_once "../../back_end/config/conn.php";

/* ===== TEMP ADMIN LOGIN FOR TESTING ===== */
    if (!isset($_SESSION['role'])) {
        $_SESSION['role'] = 'admin';
    }

/* ===== PROTECT PAGE ===== */
    if ($_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }

/* =======================
   DATA QUERIES
======================= */

/* Total users by role */
    $userData = ['student'=>0,'vendor'=>0,'admin'=>0];
    $q = mysqli_query($con,"SELECT role, COUNT(*) total FROM user GROUP BY role");
    while($r=mysqli_fetch_assoc($q)){
        $userData[$r['role']] = (int)$r['total'];
    }

/* System activity (last 7 days) */
    $activityLabels=[]; $activityData=[];
    $q=mysqli_query($con,"
        SELECT DATE(date) d, COUNT(*) total
        FROM waste_log
        GROUP BY DATE(date)
        ORDER BY d DESC LIMIT 7
    ");
    while($r=mysqli_fetch_assoc($q)){
        $activityLabels[]=$r['d'];
        $activityData[]=$r['total'];
    }
    $activityLabels=array_reverse($activityLabels);
    $activityData=array_reverse($activityData);

/* Challenge proof status */
    $proofStatus=['Approved'=>0,'Pending'=>0,'Rejected'=>0];
    $q=mysqli_query($con,"SELECT status,COUNT(*) total FROM challenge_proof GROUP BY status");
    while($r=mysqli_fetch_assoc($q)){
        $proofStatus[$r['status']]=$r['total'];
    }

/* Completion rate */
    $completed=mysqli_fetch_assoc(mysqli_query($con,"
        SELECT COUNT(DISTINCT student_id) total
        FROM challenge_proof WHERE status='Approved'
    "))['total'];

    $total=mysqli_fetch_assoc(mysqli_query($con,"
        SELECT COUNT(DISTINCT student_id) total FROM challenge_proof
    "))['total'];

    $incomplete=max($total-$completed,0);

/* Top students */
    $topStudents=mysqli_query($con,"
        SELECT s.student_name, SUM(cp.point) total_points
        FROM challenge_proof cp
        JOIN student s ON s.student_id=cp.student_id
        WHERE cp.status='Approved'
        GROUP BY cp.student_id
        ORDER BY total_points DESC LIMIT 5
    ");

/* Leftover stats */
    $posted=mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) total FROM leftover"))['total'];
    $claimed=mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) total FROM leftover_claim"))['total'];

/* Claim timeline */
    $claimLabels=[]; $claimData=[];
    $q=mysqli_query($con,"
        SELECT WEEK(claim_time) wk, COUNT(*) total
        FROM leftover_claim
        GROUP BY WEEK(claim_time)
        ORDER BY wk DESC LIMIT 4
    ");
    while($r=mysqli_fetch_assoc($q)){
        $claimLabels[]="Week ".$r['wk'];
        $claimData[]=$r['total'];
    }
    $claimLabels=array_reverse($claimLabels);
    $claimData=array_reverse($claimData);

/* Top vendors */
    $topVendors=mysqli_query($con,"
        SELECT v.vendor_name,
               COUNT(l.leftover_id) posts,
               COUNT(lc.left_claim_id) claims
        FROM vendor v
        LEFT JOIN leftover l ON v.vendor_id=l.vendor_id
        LEFT JOIN leftover_claim lc ON l.leftover_id=lc.leftover_id
        GROUP BY v.vendor_id
        ORDER BY posts DESC LIMIT 5
    ");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Analytics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="../container.css">
<style>
    body{
        margin:0;font-family:Arial;background:#f6fbf6
    }

    .tab {
        display: none;
    }

    .tab.active {
        display: block;
    }

    .tab-wrapper{
        padding:15px;
    }

    .tab-pill{
        background:#e9ecef;
        border-radius:30px;
        padding:6px;
        display:flex;
        gap:6px;
    }

    .tab-item{
        flex:1;
        border:none;
        background:transparent;
        padding:12px 18px;
        border-radius:25px;
        cursor:pointer;
        font-weight:600;
        color:#333;
    }

    .tab-item.active{
        background:#ffffff;
        box-shadow:0 2px 6px rgba(0,0,0,0.15);
    }
    
    .card{
        background:#fff;
        padding:15px;
        border-radius:8px;
        box-shadow:0 2px 6px rgba(0,0,0,.1);
    }

    .chart-box{
        height:260px
    }

    canvas{
        width:100%!important;height:100%!important
    }

    table{
        width:100%;border-collapse:collapse
    }

    th,td{
        padding:8px;border-bottom:1px solid #ddd;text-align:left
    }
</style>

<script>
    function openTab(id,btn){
        document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.tab-item').forEach(b=>b.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        btn.classList.add('active');
    }
</script>
</head>

<body>

<!-- TAB BAR -->
 <div class="tab-wrapper">
    <div class="tab-pill">
        <button class="tab-item active" onclick="openTab('users', this)">
            User & System
        </button>
        <button class="tab-item" onclick="openTab('challenges', this)">
            Challenge Participation & Performance
        </button>
        <button class="tab-item" onclick="openTab('vendors', this)">
            Leftover Sharing & Vendor Activity
        </button>
    </div>
 </div>

<!-- USERS TAB -->
 <div id="users" class="tab active">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <h3>Total Users</h3>
                <div class="chart-box">
                    <canvas id="userChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <h3>System Activity Count</h3>
                <div class="chart-box">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CHALLENGE TAB -->
 <div id="challenges" class="tab">

    <!-- FULL WIDTH CARD -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <h3>Proof Submission Status</h3>
                <div class="chart-box">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- TWO COLUMNS ROW -->
    <div class="row">
        <div class="col-6 col-s-12">
            <div class="card">
                <h3>Challenge Completion Rate</h3>
                <div class="chart-box">
                    <canvas id="completionChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-6 col-s-12">
            <div class="card">
                <h3>Top Students by Challenge Points</h3>
                <table>
                    <tr><th>Name</th><th>Points</th></tr> 
                    <?php while($s=mysqli_fetch_assoc($topStudents)){ ?> 
                    <tr><td><?= $s['student_name'] ?></td><td><?=
                    $s['total_points'] ?></td></tr> <?php } ?>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- VENDOR TAB -->
 <div id="vendors" class="tab">

    <!-- FULL WIDTH -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <h3>Leftovers Posted vs Claimed</h3>
                <div class="chart-box">
                    <canvas id="leftoverChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- TWO COLUMN ROW -->
    <div class="row">
        <div class="col-6 col-s-12">
            <div class="card">
                <h3>Top Vendors</h3>
                <table>
                    <tr><th>Vendor</th><th>Posts</th><th>Claims</th></tr>
                    <?php while($v=mysqli_fetch_assoc($topVendors)){ ?>
                    <tr><td><?= $v['vendor_name'] ?></td><td><?= $v['posts'] ?></td><td><?= $v['claims'] ?></td></tr>
                    <?php } ?>
                </table>
            </div>
        </div>

        <div class="col-6 col-s-12">
            <div class="card">
                <h3>Claim Activity Timeline</h3>
                <div class="chart-box">
                    <canvas id="claimChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    const opt={responsive:true,maintainAspectRatio:false};

    new Chart(userChart,{
        type:'pie',
        data:{
            labels:['Students','Vendors','Admins'],
            datasets:[{
                data:[
                    <?= $userData['student'] ?>,
                    <?= $userData['vendor'] ?>,
                    <?= $userData['admin'] ?>
                ]
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            scales: {
                x: { display: false },
                y: { display: false }
            }
        }
    });


    new Chart(activityChart,{type:'line',
    data:{labels:<?= json_encode($activityLabels) ?>,
    datasets:[{label:'Logs',data:<?= json_encode($activityData) ?>}]},options:opt});

    new Chart(statusChart,{type:'bar',
    data:{labels:['Approved','Pending','Rejected'],
    datasets:[{data:[
    <?= $proofStatus['Approved'] ?>,
    <?= $proofStatus['Pending'] ?>,
    <?= $proofStatus['Rejected'] ?>
    ]}]},options:opt});

    new Chart(completionChart,{type:'bar',
    data:{labels:['Completed','Incomplete'],
    datasets:[{data:[<?= $completed ?>,<?= $incomplete ?>]}]},options:opt});

    new Chart(leftoverChart,{type:'bar',
    data:{labels:['Posted','Claimed'],
    datasets:[{data:[<?= $posted ?>,<?= $claimed ?>]}]},options:opt});

    new Chart(claimChart,{type:'line',
    data:{labels:<?= json_encode($claimLabels) ?>,
    datasets:[{label:'Claims',data:<?= json_encode($claimData) ?>}]},options:opt});
</script>

</body>
</html>
