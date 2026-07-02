<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "ecoeats");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <style>
        body {
            font-family: Arial;
            background: #eaffea;
            margin: 0;
        }

        .container {
            width: 80%;
            margin: 30px auto;
            background: #ffffff;
            padding: 20px;
        }

        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            background-color: #f0f0f0;   /* grey background */
            border: 1px solid #ccc;
        }

        button {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        .rating-location {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .location-box {
            margin-left: 80px;
        }

        .location-select {
            width: 300px;
            padding: 8px;
        }

        .star {
            font-size: 30px;
            cursor: pointer;
            color: white;
            -webkit-text-stroke: 2px black;
            user-select: none;
        }

        .star.active {
            color: gold;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const stars = document.querySelectorAll(".star");
            const ratingInput = document.getElementById("rating");

            function updateStars(value) {
                stars.forEach(star => {
                    star.classList.toggle("active", star.dataset.value <= value);
                });
            }

            updateStars(1);

            stars.forEach(star => {
                star.addEventListener("click", function () {
                    const value = this.dataset.value;
                    ratingInput.value = value;
                    updateStars(value);
                });
            });

        });
    </script>

</head>
<body>
    
<div class="nav-bars">
    <?php include '../nav_bar.php';
    ?>
</div>

<div class="container">
    <h3>Feedback / Rating</h3>
    <p>A place where you give feedback anything</p>

    <form method="post">
        Title<br>
        <input type="text" name="title" required><br><br>
        <div class="rating-location">
            <div class="rating-box">
                <label>Rating</label><br>
                <div id="stars">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <input type="hidden" name="rating" id="rating" value="1">
            </div>

            <div class="location-box">
                <label>Location</label><br>
                <select name="location" class="location-select" required>
                    <option value="">Select location</option>
                    <option>Shared Food</option>
                    <option>APU Cafeteria</option>
                    <option>Food Court</option>
                </select>
            </div>
        </div>

        Description<br>
        <textarea name="description" rows="4" required></textarea><br><br>

        <button name="submit">Submit Feedback</button>
    </form>
</div>

<div class="container">
    <h3>Student Feedback</h3>
    <p>See what others are saying about shared food</p>

    <?php
        $result = mysqli_query($conn, "SELECT * FROM feedback ORDER BY date_post DESC");
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<hr>";
            echo "<b>Title:</b> ".$row['title']."<br>";
            echo "<b>Rating:</b> ".$row['rating']." / 5<br>";
            echo "<small>Time Posted: ".$row['date_post']."</small>";
            echo "<b>Description:</b> ".$row['description']."<br>";
        }
    ?>
</div>

</body>
</html>