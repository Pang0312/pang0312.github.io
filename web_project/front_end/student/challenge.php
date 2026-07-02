<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenge</title>
    <link rel="stylesheet" href="../container.css">
    <link rel="stylesheet" href="challenge.css">
</head>

<body>
    <div class="nav-bars">
        <?php include '../nav_bar.php'; ?>
    </div>
    <div class="challenge-top-container-view">
        <div class="col-11 col-s-11" id="challenge-week">
            <h3>Weekly Challenges</h3> <br>
            <p id="challenge-desc">Complete challenges to earn points, badges, and climb the leaderboard!</p>
        </div>
    </div>
    <div class="challenge-container-list">
        <div class="challenge-event" id="challenge-list-1">
            <h3 class="challenge-title">Title</h3> <br>
            <p class="challenge-desc">Description</p>
            <div class="container-next">
                <div class="challenge-duration-list">
                    <h4 class="challenge-fix">Duration: </h4>
                    <h4 class="challenge-duration">1 days</h4>
                </div>
                <div class="challenge-date-list">
                    <h4 class="challenge-fix">Start Date:</h4>
                    <h4 class="challenge-startdate">Date 0001-11-22 to 0001-11-23</h4>
                </div>
            </div>
            <div class="point-container">
                <h4 class="challenge-points">🏆 0/100 pts</h4>
            </div>
            <div class="challenge-button">
                <button id="button-accept">Accept</button>
            </div>
            <div class="challenge-accepted-button" style="display: none;">
                <button class="button-upload-proof">Upload Proof</button>
                <button class="button-cancel">Cancel</button>
            </div>
        </div>
    </div>
</body>

</html>