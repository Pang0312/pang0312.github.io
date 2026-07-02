
<?php
session_start()
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #eaf7ef;
        }

        .container {
            display: flex;
            padding: 20px;
            gap: 20px;
        }

        .left {
            width: 65%;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        .right {
            width: 35%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .box {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        .required {
            color: red;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            background-color: #f2f2f2;
            border: none;
            border-radius: 5px;
        }

        .photo-box {
            width: 100px;
            height: 100px;
            border: 1px solid #ccc;
            text-align: center;
            line-height: 100px;
            margin-bottom: 5px;
        }

        .photo-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .photo-info p {
            margin: 5px 0 0;
            font-size: 12px;
            color: gray;
        }

        .status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .label {
            color: black;
        }

        .stat-value {
            color: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 14px;
        }

        .role { background-color: #f4fbf7; }
        .challenges { background-color: #faf4fd; }
        .points { background-color: #fffaf0; }

        .role .stat-value { background-color: #5ccf9a; }
        .challenges .stat-value { background-color: #b38adf; }
        .points .stat-value { background-color: #f1b44c; }

        button {
            padding: 8px 16px;
            margin-top: 15px;
        }

        .save {
            background-color: #2ecc71;
            color: white;
            border: none;
        }
        
        @media (max-width: 768px) {

            .box {
                margin-bottom: 15px;
            }

            .container {
                flex-direction: column;
            }

            .left,
            .right {
                width: 100%;
            }

            .right {
                margin-top: 20px;
            }
        }
    </style>

    <script>
        function validateForm() {
            let name = document.getElementById("name").value.trim();
            let email = document.getElementById("email").value.trim();
            let password = document.getElementById("password").value;
            let confirm = document.getElementById("confirm").value;

            let namePattern = /^[A-Za-z ]+$/;
            let emailPattern = /@(gmail\.com|hotmail\.com|outlook\.com)$/;

            // Name
            if (name === "") {
                alert("Name is needed");
                return false;
            }
            if (!namePattern.test(name)) {
                alert("Name must contain only letters");
                return false;
            }

            // Email
            if (email === "") {
                alert("Email is needed");
                return false;
            }
            if (!emailPattern.test(email)) {
                alert("Email format invalid");
                return false;
            }

            // Password
            if (password === "") {
                alert("Password needed");
                return false;
            }
            if (password !== confirm) {
                alert("Password incorrect");
                return false;
            }

            alert("Profile updated successfully!");
            return true;
        }
    </script>

    <script>
        function previewPhoto(input) {
            const file = input.files[0];

            if (!file) return;

            // File size check (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert("File size must be less than 2MB");
                input.value = "";
                return;
            }

            // File type check
            if (!file.type.match("image.*")) {
                alert("Only JPG or PNG images are allowed");
                input.value = "";
                return;
            }

            // Preview image
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById("preview");
                preview.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:5px;">`;
            };
            reader.readAsDataURL(file);
        }
    </script>

</head>
<body>

<div class="nav-bars">
    <?php 
        include '../nav_bar.php'; 
    ?>
</div>

<div class="container">
    
    <div class="left">
        <h3>Profile Information</h3>
        <p>Update your personal information details</p>

        <form onsubmit="return validateForm();">

            <div class="photo-row">
                <div class="photo-box" id="preview">photo</div>

                <div class="photo-info">
                    <input type="file" id="photoInput" accept="image/png, image/jpeg" hidden onchange="previewPhoto(this)">
                    <button type="button" onclick="document.getElementById('photoInput').click()">
                        Change photo
                    </button>

                    <p>JPG or PNG, max 2MB</p>
                </div>
            </div>

            <label>Full Name <span class="required">*</span></label>
            <input type="text" id="name">

            <label>Email <span class="required">*</span></label>
            <input type="email" id="email">

            <label>Password <span class="required">*</span></label>
            <input type="password" id="password">

            <label>Confirm Password <span class="required">*</span></label>
            <input type="password" id="confirm">

            <button type="submit" class="save">Save Changes</button>
            <button type="reset">Cancel</button>

        </form>
    </div>

    <div class="right">

        <div class="box">
            <h3>Stats Overview</h3>

            <div class="status role">
                <span class="label">Role</span>
                <span class="stat-value">Student</span>
            </div>

            <div class="status challenges">
                <span class="label">Challenges Completed</span>
                <span class="stat-value">8</span>
            </div>

            <div class="status points">
                <span class="label">Points</span>
                <span class="stat-value">10,000 PS</span>
            </div>
        </div>

        <div class="box">
            <h3>Achievements</h3>
            <p>Badges earned through your journey</p>

            <div class="badge"></div>
            <div class="badge"></div>
            <div class="badge"></div><br>
            <div class="badge"></div>
            <div class="badge"></div>
            <div class="badge"></div>

            <br><br>
            <button type="button">View all</button>
        </div>

    </div>
</div>

</body>
</html>
