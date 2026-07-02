<?php
require_once '../../back_end/controller/leaderboard.php';
include "../../back_end/config/conn.php";
$time = "weekly";
$time = $_GET['time'] ?? 'weekly';
$leaderboard = getLeaderboard($con,$time);
session_start();
// once the login page and session/ cookie setup already pls delete //  only
// if (!$_SESSION["user_id"]) {
//     header('Location: ../login.php');
// } else {
//     $challenge = getActiveChallenge($con, $_SESSION["user_id"]);
//   $achievement = getAchievement($con ,$_SESSION["user_id"]);
// };
$challenge = getActiveChallenge($con,"2");
$achievement = getAchievement($con,"2");  // testing
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="leaderboard_css.css">
    <link rel="stylesheet" href="../container.css">
    <script src="leaderboard.js"></script>
</head>

<body>
    <div class="nav-bars">
        <?php include '../nav_bar.php'; ?>
    </div>
    <div class="container-leaderboard-view">
        <div class="left-column">
            <div class="leaderboard-top-container">
                <h3>Leaderboard Rankings</h3> <br>
                <p class="leaderboard-desc">See how you compare with other sustainability champions</p>
                <div class="col-6 col-s-6" id="leaderboard-time-list">
                    <button class="leaderboard-time-button" value="weekly">Weekly</button>
                    <button class="leaderboard-time-button" value="monthly">Monthly</button>
                    <button class="leaderboard-time-button" value="alltime">All Time</button>
                </div>
            </div>
            <div class="leaderboard-rank-list">
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th class="table-rank">Rank</th>
                            <th class="table-name">Student Name</th>
                            <th class="table-challenge">Challenge</th>
                            <th class="table-point">Points</th>
                        </tr>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($leaderboard)) {
                            echo "<tr> 
                            <td colspan='4' style='text-align: center;'>Empty</td>
                             </tr>";
                        } else {
                            foreach ($leaderboard as $row) {
                                echo "<tr>";
                                echo "<td>" . $row['rank'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['user']) . "</td>";
                                echo "<td>" . $row['challenge'] . "</td>";
                                echo "<td>" . $row['points'] . " pts</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="right-column">
            <div class="leaderboard-active-challenge-side">
                <h3>Active Challenges</h3>
                <?php if (empty($challenge)): ?>

                    <div class="leaderboard-active-challenge" style="text-align: center;">
                        <h4>You haven't chosen any challenge</h4>
                    </div>
                <?php else: ?>
                    <?php foreach ($challenge as $row): ?>
                        <div class="leaderboard-active-challenge" id="active-challenge-<?= $row['id']?>">
                            
                            <h4 class="active-challenge-title"><?= htmlspecialchars($row['title']) ?></h4> <br>
                            
                            <p class="active-challenge-desc"><?= htmlspecialchars($row['description']) ?></p>
                            
                            <div class="container-next">
                                <div class="active-challenge-duration-list">
                                    <h4 class="active-challenge-fix">Duration: </h4>
                                    <h4 class="active-challenge-duration"><?= $row['duration'] ?> days</h4>
                                </div>
                                <div class="active-challenge-date-list">
                                    <h4 class="active-challenge-fix">Start Date:</h4>
                                    <h4 class="active-challenge-startdate">
                                        <?= $row['start'] ?> to <?= $row['end'] ?>
                                    </h4>
                                </div>
                            </div>
                            
                            <div class="point-container-side">
                                <h4 class="active-challenge-points">
                                    🏆 0/<?= $row['point'] ?> pts
                                </h4>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
                <div class="leaderboard-view-all-button">
                    <button id="button-view-all-challenges" onclick="location.href='challenge.php'">View All Challenges</button>
                </div>
            </div>
            <div class="leaderboard-recent-achievements">
                <h3>Recent Achievements</h3> <br>
                <?php if (empty($achievement)): ?>
                    <div class="leaderboard-active-achivement" style="text-align: center;">
                        <h4>You have 0 achievement :/</h4>
                    </div>
                <?php else: ?>
                    <?php $id = 1 ?>
                    <?php foreach ($achievement as $row): ?>
                        <div class="leaderboard-recent-achievement" id="recent-achievement-<?= $id++ ?>">
                            <img src="<?= $row["icon"] ?>" alt="placeholder-image" class="recent-achievement-image">
                            <div class="image-container-detail">
                                <h4 class="recent-achievement-title"><?= $row["title"] ?></h4> <br>
                                <p class="recent-achievement-desc"><?= $row["desciption"] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
</body>
</html>