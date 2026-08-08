<?php

require_once "includes/auth.php";
require_once "config/db.php";

/* Greeting */

$hour=date("H");

if($hour<12){

    $greeting="Good Morning";

}elseif($hour<18){

    $greeting="Good Afternoon";

}else{

    $greeting="Good Evening";

}

/* Players */

$totalPlayers=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM players
WHERE is_active=1
"));

/* Coaches */

$totalCoaches=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM coaches
"));

/* Active Subscriptions */

$totalSubscriptions=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM subscriptions s
INNER JOIN players p
    ON s.player_id = p.player_id
WHERE p.is_active = 1
  AND s.is_current = 1
  AND s.end_date >= CURDATE()
"));

/* Today's Attendance */

$todayAttendance=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT
(
SELECT COUNT(*)
FROM attendance
WHERE attendance_date=CURDATE()
)
+
(
SELECT COUNT(*)
FROM non_member_attendance
WHERE attendance_date=CURDATE()
)
AS total
"));
/* Attendance Overview */

$playersToday = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM attendance
WHERE attendance_date = CURDATE()
"));

$nonMembersToday = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM non_member_attendance
WHERE attendance_date = CURDATE()
"));

$absentPlayers = $totalPlayers['total'] - $playersToday['total'];

if($absentPlayers < 0){

    $absentPlayers = 0;

} 

/* ==========================
   Monthly Attendance
========================== */

$months=[];
$attendanceData=[];

for($i=5;$i>=0;$i--){

$date=date("Y-m",strtotime("-$i month"));

$months[]=date("M",strtotime($date));

$row=mysqli_fetch_assoc(mysqli_query($conn,"

SELECT COUNT(*) total

FROM attendance

WHERE DATE_FORMAT(attendance_date,'%Y-%m')='$date'

"));

$attendanceData[]=$row['total'];

}

/* ==========================
   Expired Subscriptions
========================== */
/* Expired Subscriptions - Active Players Only */

$expiredCount=mysqli_fetch_assoc(mysqli_query($conn,"

SELECT COUNT(*) AS total
FROM subscriptions s
INNER JOIN players p
    ON s.player_id = p.player_id
WHERE p.is_active = 1
  AND s.is_current = 1
  AND s.end_date < CURDATE()

"));
/* ==========================
   Non Members
========================== */

$totalNonMembers=mysqli_fetch_assoc(mysqli_query($conn,"

SELECT COUNT(*) total

FROM non_members

"));
?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">

<title>LIGER Dashboard</title>

<link rel="stylesheet" href="assets/css/style.css">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<?php include "includes/sidebar.php"; ?>

<div class="main">
    <div class="hero">

<div>

<h1 class="heroTitle">

    👋 <?php echo $greeting; ?>,

    <span class="adminName">
        <?php echo htmlspecialchars(explode(' ', $_SESSION['admin_name'])[0]); ?>
    </span>

</h1>

<p>

Welcome back to

<strong>LIGER Football Academy</strong>

</p>

</div>

<div>

<h2 id="clock"></h2>

<p>

<?php echo date("l, d F Y"); ?>

</p>

</div>

</div>
    <div class="stats">

<div class="card">

<i class="fa-solid fa-users"></i>

<h3>

<?php echo $totalPlayers['total']; ?>

</h3>

<p>Players</p>

</div>

<div class="card">

<i class="fa-solid fa-user-tie"></i>

<h3>

<?php echo $totalCoaches['total']; ?>

</h3>

<p>Coaches</p>

</div>

<div class="card">

<i class="fa-solid fa-credit-card"></i>

<h3>

<?php echo $totalSubscriptions['total']; ?>

</h3>

<p>Subscriptions</p>

</div>

<div class="card">

<i class="fa-solid fa-calendar-check"></i>

<h3>

<?php echo $todayAttendance['total']; ?>

</h3>

<p>Today's Attendance</p>

</div>

</div>
    <div class="dashboardRow">

<div class="panel">

<div class="panelTitle">

<h2>

<i class="fa-solid fa-futbol"></i>

Attendance Overview

</h2>

</div>

<div class="attendanceContent">

<div class="attendanceNumbers">

<div>

<p>Players</p>

<h2><?php echo $playersToday['total']; ?></h2>

</div>

<div>

<p>Non Members</p>

<h2><?php echo $nonMembersToday['total']; ?></h2>

</div>

<div>

<p>Absent</p>

<h2><?php echo $absentPlayers; ?></h2>

</div>

</div>

<div class="attendanceChart">

<canvas id="attendanceChart"></canvas>

</div>

</div>

</div>
        <div class="panel">

<div class="panelTitle">

<h2>

<i class="fa-solid fa-clock"></i>

Recent Attendance

</h2>

</div>

<table class="dashboardTable">

<tr>

<th>Code</th>

<th>Name</th>

<th>Time</th>

</tr>

<?php

$recent=mysqli_query($conn,"

SELECT

player_code,

CONCAT(first_name,' ',last_name) player,

check_in_time

FROM attendance

INNER JOIN players

ON attendance.player_id=players.player_id

WHERE attendance_date=CURDATE()

ORDER BY check_in_time DESC

LIMIT 8

");

while($row=mysqli_fetch_assoc($recent)){

?>

<tr>

<td>

<?php echo $row['player_code']; ?>

</td>

<td>

<?php echo $row['player']; ?>

</td>

<td>

<?php echo date("h:i A",strtotime($row['check_in_time'])); ?>

</td>

</tr>

<?php

}

?>

</table>

</div>

</div>
    <script>

const attendanceChart=document.getElementById("attendanceChart");

new Chart(attendanceChart,{

type:"doughnut",

data:{

labels:[

"Players",

"Non Members",

"Absent"

],

datasets:[{

data:[

<?php echo $playersToday['total']; ?>,

<?php echo $nonMembersToday['total']; ?>,

<?php echo $absentPlayers; ?>

],

backgroundColor:[

"#B7FF00",

"#3498DB",

"#E74C3C"

],

borderWidth:0

}]

},

options:{

plugins:{

legend:{

position:"bottom",

labels:{

color:"#ffffff"

}

}

},

cutout:"72%"

}

});

</script>
<script>

function updateClock(){

const now=new Date();

document.getElementById("clock").innerHTML=

now.toLocaleTimeString([],{

hour:"2-digit",

minute:"2-digit",

second:"2-digit"

});

}

updateClock();

setInterval(updateClock,1000);

</script>

<div class="dashboardRow">

    <!-- Subscription Alerts -->

    <div class="panel">

        <div class="panelHeader">

            <h2>

                <i class="fa-solid fa-triangle-exclamation"></i>

                Subscription Alerts

            </h2>

        </div>

        <table class="dashboardTable">

            <thead>

                <tr>

                    <th>Code</th>

                    <th>Player</th>

                    <th>Expires</th>

                </tr>

            </thead>

            <tbody>

            <?php

            $alerts=mysqli_query($conn,"

            SELECT

            player_code,

            CONCAT(first_name,' ',last_name) player,

            end_date

            FROM subscriptions

            INNER JOIN players

            ON subscriptions.player_id=players.player_id

            WHERE is_current=1

            ORDER BY end_date ASC

            LIMIT 6

            ");

            while($row=mysqli_fetch_assoc($alerts)){

            ?>

            <tr>

                <td><?php echo $row['player_code']; ?></td>

                <td><?php echo $row['player']; ?></td>

                <td>

                    <?php

                    if(strtotime($row['end_date'])<time()){

                        echo "<span class='expired'>Expired</span>";

                    }else{

                        echo date("d M Y",strtotime($row['end_date']));

                    }

                    ?>

                </td>

            </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>



    <!-- Quick Actions -->

    <div class="panel">

        <div class="panelHeader">

            <h2>

                <i class="fa-solid fa-bolt"></i>

                Quick Actions

            </h2>

        </div>

        <div class="quickGrid">

            <a href="players.php" class="quickCard">

                <i class="fa-solid fa-user-plus"></i>

                <span>Players</span>

            </a>

            <a href="attendance.php" class="quickCard">

                <i class="fa-solid fa-calendar-check"></i>

                <span>Attendance</span>

            </a>

            <a href="subscriptions.php" class="quickCard">

                <i class="fa-solid fa-credit-card"></i>

                <span>Subscriptions</span>

            </a>

            <a href="coaches.php" class="quickCard">

                <i class="fa-solid fa-user-tie"></i>

                <span>Coaches</span>

            </a>

            <a href="reports.php" class="quickCard">

                <i class="fa-solid fa-chart-column"></i>

                <span>Reports</span>

            </a>

            <a href="non_members.php" class="quickCard">

                <i class="fa-solid fa-users"></i>

                <span>Non Members</span>

            </a>

        </div>

    </div>

</div>
    
<div class="dashboardRow">

<div class="panel">

<div class="panelHeader">

<h2>

<i class="fa-solid fa-chart-line"></i>

Monthly Attendance

</h2>

</div>

<canvas id="monthlyChart" height="120"></canvas>

</div>

<div class="panel">

<div class="panelHeader">

<h2>

<i class="fa-solid fa-shield-halved"></i>

Academy Summary

</h2>

</div>

<div class="summaryGrid">

<div class="summaryCard">

<h1>

<?php echo $totalPlayers['total']; ?>

</h1>

<p>Players</p>

</div>

<div class="summaryCard">

<h1>

<?php echo $totalCoaches['total']; ?>

</h1>

<p>Coaches</p>

</div>

<div class="summaryCard">

<h1>

<?php echo $expiredCount['total']; ?>

</h1>

<p>Expired Subs</p>

</div>

<div class="summaryCard">

<h1>

<?php echo $totalNonMembers['total']; ?>

</h1>

<p>Non Members</p>

</div>

</div>

</div>

</div>

<script>

new Chart(

document.getElementById("monthlyChart"),

{

type:"line",

data:{

labels:[

<?php

foreach($months as $m){

echo "'$m',";

}

?>

],

datasets:[{

label:"Attendance",

data:[

<?php

foreach($attendanceData as $a){

echo "$a,";

}

?>

],

borderColor:"#B7FF00",

backgroundColor:"rgba(183,255,0,.15)",

fill:true,

tension:.4

}]

},

options:{

plugins:{

legend:{

labels:{

color:"#ffffff"

}

}

},

scales:{

x:{

ticks:{

color:"#fff"

},

grid:{

color:"rgba(255,255,255,.05)"

}

},

y:{

ticks:{

color:"#fff"

},

grid:{

color:"rgba(255,255,255,.05)"

}

}

}

}

});

</script>

<footer class="footer">

LIGER Football Academy © <?php echo date("Y"); ?>

</footer>