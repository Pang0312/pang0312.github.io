<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../container.css">
    <link rel="stylesheet" href="dashboard-css.css">
</head>
<body>
    <div class="nav-bars">
        <?php include '../nav_bar.php'; ?>
    </div>
    <div class="display-container-view">
        <div class="col-2 col-s-2" id="class-week">
            <h3>This Week</h3> <br>
            <h3 id="amount-week">2.4KG Saved</h3>
        </div>
        <div class="col-2 col-s-2" id="class-month">
            <h3>This Month</h3> <br>
            <h3 id="amount-month">9.8KG Saved</h3>
        </div>
        <div class="col-2 col-s-2" id="class-rank">
            <h3>Your Rank</h3> <br>
            <h3 id="amount-rank">#99+ APU</h3>
        </div>
        <div class="col-2 col-s-2" id="class-points">
            <h3>Your Points</h3> <br>
            <h3 id="amount-points">1 Point</h3>
        </div>
    </div>
    <div class="display-container-list">
        <div class="col-5 col-s-5">
            <div class="event">
                <h4>Event</h4> <br>
                <p id="dashboard-p">A small placement for vendor announcement some event</p>
                <div class="event-list" id="event-list-1">
                    <h3 id="event-title">Title</h3>
                    <p id="event-desc">Description</p>
                    <h4 id="event-datetime">From Date time to Date time</h4>
                </div>
            </div>
        </div>
        <div class="col-5 col-s-5">
            <div class="feedback">
                <h4>Feedback / Rating</h4> <br>
                <p id="dashboard-p">A small placement for student feedback</p>
                <div class="feedback-list" id="feedback-list-1">
                    <h3 id="feedback-student">student</h3>
                    <p id="feedback-vendor">vendor</p>
                    <h3 id="feedback-rating">rating</h3>
                    <h3 id="feedback-food">food</h3>
                </div>
                <div class="view-all-button">
                    <button onclick="location.href='feedback.php'">View All Feedback</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>