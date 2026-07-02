<?php 

    function getLeaderboard($con , $time = 'weekly') {
        if ($time == "weekly") {
            $sql_command = "
            Select student.student_name , sum(challenge_proof.point) As points ,  COUNT(challenge_proof.chal_id) as challenge
            From challenge_proof , student 
            Where student.student_id = challenge_proof.student_id and yearweek(challenge_proof.time_submit , 3) = yearweek(now() ,3)
            Group by student.student_name
            Order by points desc
            Limit 10"; 
        } elseif ($time == "monthly") {
            $sql_command = "
            Select student.student_name , sum(challenge_proof.point) As points ,  COUNT(challenge_proof.chal_id) as challenge
            From challenge_proof , student 
            Where student.student_id = challenge_proof.student_id and year(challenge_proof.time_submit) = year(curdate()) and month(challenge_proof.time_submit) = month(curdate())
            Group by student.student_name
            Order by points desc
            Limit 10
            ";
        } else {
            $sql_command = "
            Select student.student_name , sum(challenge_proof.point) As points ,  COUNT(challenge_proof.chal_id) as challenge
            From challenge_proof , student 
            Where student.student_id = challenge_proof.student_id
            Group by student.student_name
            Order by points desc
            Limit 10"; 
        }
        $result = mysqli_query($con,$sql_command);
        $real_result = [];
        $rank = 1;

        while ( $row = mysqli_fetch_assoc($result)) {
            $real_result[] = [
                "rank" => $rank++,
                "user" => $row["student_name"],
                "challenge" => $row["challenge"],
                "points" => $row["points"]
            ];
        }

        return $real_result;
    }

    function getActiveChallenge($con ,$user_id) {
        $real_result = [];
        $sql_command = "
        select challenge.title as title , challenge.description as descrip , challenge.start_date as start , challenge.end_date as end , challenge.points as point 
        from challenge , challenge_proof , student
        where student.student_id = '$user_id' and student.student_id = challenge_proof.student_id and challenge_proof.chal_id = challenge.chal_id 
        limit 2
        ";
        $id = 1;
        $result = mysqli_query($con , $sql_command);
        while ($row = mysqli_fetch_assoc($result)) {
            $start = new DateTime($row["start"]);
            $end = new DateTime($row["end"]);
            $duration = $start->diff($end);
            $real_result[] = [
                "id" => $id++,
                "title" => $row["title"],
                "description" => $row["descrip"],
                "duration" => $duration->days ,
                "start" => $row["start"],
                "end" => $row["end"],
                "point" => $row["point"]
            ];
        }
        return $real_result;
    }

    function getAchievement($con ,$student_id) {

        $sql_command = "
        Select sum(challenge_proof.point) As points
        From challenge_proof , student 
        Where student.student_id = $student_id and student.student_id = challenge_proof.student_id 
        ";

        $search = mysqli_query($con , $sql_command);
        $row = mysqli_fetch_assoc($search);

        if ($row) {
            $point = $row["points"];
        } else {
            $point = 0;
        }
        
        $result = [];

        switch (true) {
            case $point >= 1000:
                $result[] = [
                    "icon" => "../img/achievement_5.png",
                    "title" => "Eco Legend",
                    "desciption" => "You have mastered the sustainability journey. Your achievements set the standard for others to follow."
                ];
            case $point >= 600:
                $result[] = [
                    "icon" => "../img/achievement_4.png",
                    "title" => "Green Champion",
                    "desciption" => "You lead by example. Your dedication shows strong commitment to environmental responsibility."
                ];
            case $point >= 300:
                $result[] = [
                    "icon" => "../img/achievement_3.png",
                    "title" => "Sustainability Advocate",
                    "desciption" => "Your continued efforts are inspiring change. You have committed to promoting sustainability through action."
                ];
            case $point >= 100:
                $result[] = [
                    "icon" => "../img/achievement_2.png",
                    "title" => "Eco Explorer",
                    "desciption" => "You have actively exploring sustainable habits and making a positive impact through consistent participation."
                ];
            default:
                $result[] = [
                    "icon" => "../img/achievement_1.png",
                    "title" => "Green Beginner",
                    "desciption" => "You have taken your first step toward a more sustainable lifestyle. Every journey starts with a single action."
                ];
        }
        return array_reverse($result);
    }
?>