<style>
    * {
        margin: 0;
    }

    .app_icon {
        width: 32px;
        height: 32px;
        vertical-align: middle;
        padding-left: 10px;
        padding-bottom: 5px;
    }

    .logo_icon {
        width: 48px;
        height: 48px;
        margin-left: 10px;
        vertical-align: middle;
        padding-bottom: 5px;
    }

    .nav-bar {
        background-color: #ECECF0;
        height: 10%;
        display: flex;
    }

    .dashboard-button {
        background-color: #ECECF0;
        border: none;
        color: black;
        text-align: center;
        font-size: 24px;
        margin-left: 10px;
        margin-top: 10px;
    }

    button.menu {
        background-color: #ECECF0;
        border: none;
        padding: 5px 10px;
        text-align: center;
        cursor: pointer;
    }

    .nav-bar-content {
        margin-top: 10px;
        background-color: #ECECF0;
        height: 8%;
        display: flex;
        align-items: center;
        padding-left: 20px;
    }

    .nav-bar-list {
        list-style-type: none;
        margin: 0 10px;
        padding: 10px;

    }

    .nav-items {
        display: flex;
        gap: 40px;
        list-style: none;
    }

    .nav-link {
        text-decoration: none;
        color: black;
        font-size: 16px;
        padding: 10px 20px;
        border: 1px solid black;
        background-color: #ffffffff;
        flex-wrap: nowrap;
        display: inline-block;
    }

    .nav-link:hover {
        background-color: #D3D3D3;
    }

    .nav-link:active {
        background-color: #A9A9A9;
        color: white;
    }

    @media only screen and (max-width: 1000px) {
        .nav-items {
            flex-direction: column;
            display: inline-block;
            padding-left: 10px;
            margin-bottom: 10px;
        }

        .nav-bar-content {
            padding: 0 0px;
            margin-top: 0px;
        }

        .nav-link {
            display: block;
            text-align: center;
            width: auto;
            white-space: nowrap;
            margin-bottom: 20px;
        }

        .nav-bar-list {
            padding: 0px;
            margin: 10px 0px;
            padding-right: 30px;
            height: 100vh;
        }
        #overlay[hidden] {
            display: none;
        }
        #overlay {
            position: fixed;
            top: 56px;
            background-color: rgba(236, 236, 240, 0.6);
            z-index: 3;
            width: fit-content;
        }


    }
</style>

<script>
    function toggleMenu() {
        const navBarContent = document.querySelector('.nav-bar-list').parentElement;
        if (navBarContent.hasAttribute('hidden')) {
            navBarContent.removeAttribute('hidden');
        } else {
            navBarContent.setAttribute('hidden', '');
        }
    }
</script>


<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['role'] = "student"; // For testing purpose only

?>
<div class="nav-bar">
    <div class="col-12 col-s-12">
        <!-- This based on your folder name -->
        <button class="menu" onclick="toggleMenu()"> <img src="../img/menu.png" alt="menu" class="app_icon"></button>
        <img src="../img/app_logo.png" alt="logo" class="logo_icon">
        <button class="dashboard-button" onclick="location.href='dashboard.php'">EcoEats APU</button>
    </div>
</div>

<!-- This based on user role -->


<div class="nav-bar-content">
    <div class="col-12 col-s-12" id="overlay" hidden>
        <?php
        echo '<nav class="nav-bar-list">';
        echo '<ul class="nav-items">';
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'student') {
            echo '<li><a class="nav-link" href="../student/dashboard.php">Dashboard</a></li>';
            echo '<li><a class="nav-link" href="../student/feedback.php">Feedback</a></li>';
            echo '<li><a class="nav-link" href="../student/leaderboard.php">Leaderboard</a></li>';
            echo '<li><a class="nav-link" href="../student/challenge.php">Challenge</a></li>';
            echo '<li><a class="nav-link" href="../student/sharing_hub.php">Sharing Hub</a></li>';
            echo '<li><a class="nav-link" href="../student/profile.php">Profile</a></li>';
        } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
            echo '<li><a class="nav-link" href="../admin/dashboard.php">Dashboard</a></li>';
            echo '<li><a class="nav-link" href="../admin/manage_users.php">Manage Users</a></li>';
            echo '<li><a class="nav-link" href="../admin/challenge.php">Challenge</a></li>';
            echo '<li><a class="nav-link" href="../admin/moderation.php">Moderation</a></li>';
            echo '<li><a class="nav-link" href="../admin/analysis.php">Analysis</a></li>';
        } else if (isset($_SESSION['role']) && $_SESSION['role'] == 'vendor') {
            echo '<li><a class="nav-link" href="../vendor/dashboard.php">Dashboard</a></li>';
            echo '<li><a class="nav-link" href="../vendor/event.php">Event</a></li>';
            echo '<li><a class="nav-link" href="../vendor/food_listing.php">Food Listing</a></li>';
            echo '<li><a class="nav-link" href="../vendor/profile.php">Profile</a></li>';
        } else {
            echo '<li><a class="nav-link" href="index.php">Home</a></li>';
        }

        echo '</ul>';
        echo '</nav>';
        ?>
    </div>
</div>
