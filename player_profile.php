<?php
require_once "includes/auth.php";
require_once "config/db.php";

if(!isset($_GET['id'])){
    header("Location: players.php");
    exit();
}

$id = (int)$_GET['id'];

/* ==========================
   Player Information
========================== */

$sql = "
SELECT

p.*,

s.subscription_id,
s.start_date,
s.end_date,

st.name AS subscription_name,
st.duration_months

FROM players p

LEFT JOIN subscriptions s
ON p.player_id=s.player_id
AND s.is_current=1

LEFT JOIN subscription_types st
ON st.subscription_type_id=s.subscription_type_id

WHERE p.player_id='$id'
LIMIT 1
";

$result=mysqli_query($conn,$sql);

if(mysqli_num_rows($result)==0){

header("Location: players.php");
exit();

}

$player=mysqli_fetch_assoc($result);

/* ==========================
   Subscription Status
========================== */

$status="Trial";
$badge="trial";
$statusInfo="No Subscription";

if(!empty($player['subscription_id'])){

    $today=new DateTime();
    $end=new DateTime($player['end_date']);

    $days=$today->diff($end)->days;

    if($end >= $today){

        $status="Active";
        $badge="active";

        if($days==0){

            $statusInfo="Expires Today";

        }elseif($days==1){

            $statusInfo="1 Day Remaining";

        }else{

            $statusInfo=$days." Days Remaining";

        }

    }else{

        $status="Expired";
        $badge="expired";

        if($days==1){

            $statusInfo="Expired 1 Day Ago";

        }else{

            $statusInfo="Expired ".$days." Days Ago";

        }

    }

}

/* ==========================
   Attendance Statistics
========================== */

$totalAttendance=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM attendance
WHERE player_id='$id'
"));

$total=$totalAttendance['total'];

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>

Player Profile

</title>

<link rel="stylesheet"
href="assets/css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<?php include "includes/sidebar.php"; ?>

<div class="main">

<?php include "includes/header.php"; ?>

<div class="panel">

<h2>

Player Profile

</h2>

<br>

<div class="card">

<h3>

Player Information

</h3>

<table>

<tr>

<td><b>Player Code</b></td>

<td><?php echo $player['player_code']; ?></td>

</tr>

<tr>

<td><b>Name</b></td>

<td>

<?php

echo

$player['first_name']." ".

$player['middle_name']." ".

$player['last_name'];

?>

</td>

</tr>

<tr>

<td><b>Gender</b></td>

<td><?php echo $player['gender']; ?></td>

</tr>

<tr>

<td><b>Birth Date</b></td>

<td><?php echo date("d M Y",strtotime($player['birth_date'])); ?></td>

</tr>

<tr>

<td><b>Phone</b></td>

<td><?php echo $player['phone']; ?></td>

</tr>

<tr>

<td><b>Join Date</b></td>

<td><?php echo date("d M Y",strtotime($player['join_date'])); ?></td>

</tr>

</table>

</div>

<br>

<div class="card">

<h3>

Current Subscription

</h3>

<br>

<span class="badge <?php echo $badge; ?>">

<?php echo $status; ?>

</span>

<br><br>

<b>Plan:</b>

<?php

echo empty($player['subscription_name'])

?

"Trial"

:

$player['subscription_name'];

?>

<br><br>

<b>Expires:</b>

<?php

echo empty($player['end_date'])

?

"-"

:

date("d M Y",strtotime($player['end_date']));

?>

<br><br>

<b>

<?php echo $statusInfo; ?>

</b>

</div>

<br>

<div class="card">

<h3>

Attendance Summary

</h3>

<br>

<h1>

<?php echo $total; ?>

</h1>

<p>

Attendance Records

</p>

</div>

<?php

/* ==========================
   Attendance History
========================== */

$attendance = mysqli_query($conn, "
SELECT attendance_date
FROM attendance
WHERE player_id='$id'
ORDER BY attendance_date DESC
");

/* ==========================
   Subscription History
========================== */

$history = mysqli_query($conn, "
SELECT
st.name,
s.start_date,
s.end_date
FROM subscriptions s
LEFT JOIN subscription_types st
ON st.subscription_type_id=s.subscription_type_id
WHERE s.player_id='$id'
ORDER BY s.start_date DESC
");

/* ==========================
   Attendance Percentage
========================== */

$academyDays = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(DISTINCT attendance_date) total
FROM attendance
"));

$totalDays = (int)$academyDays['total'];

$percentage = 0;

if($totalDays > 0){

    $percentage = round(($total / $totalDays) * 100);

}

?>

<br>

<div class="card">

<h3>

Attendance Percentage

</h3>

<br>

<h1>

<?php echo $percentage; ?>%

</h1>

<p>

Overall Attendance

</p>

</div>

<br>

<div class="card">

<h3>

Attendance History

</h3>

<br>

<?php

if(mysqli_num_rows($attendance)>0){

echo "<table>";

echo "<tr>";

echo "<th>Date</th>";

echo "</tr>";

while($row=mysqli_fetch_assoc($attendance)){

echo "<tr>";

echo "<td>✔ ".date("d M Y",strtotime($row['attendance_date']))."</td>";

echo "</tr>";

}

echo "</table>";

}else{

echo "<p>No attendance records.</p>";

}

?>

</div>

<br>

<div class="card">

<h3>

Subscription History

</h3>

<br>

<?php

if(mysqli_num_rows($history)>0){

echo "<table>";

echo "<tr>";

echo "<th>Plan</th>";

echo "<th>Start</th>";

echo "<th>End</th>";

echo "</tr>";

while($row=mysqli_fetch_assoc($history)){

echo "<tr>";

echo "<td>".$row['name']."</td>";

echo "<td>".date("d M Y",strtotime($row['start_date']))."</td>";

echo "<td>".date("d M Y",strtotime($row['end_date']))."</td>";

echo "</tr>";

}

echo "</table>";

}else{

echo "<p>No subscription history.</p>";

}

?>

</div>

<br>

<div
style="display:flex;justify-content:flex-end;">

<a
href="players.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Players

</a>

</div>

</div>

<?php include "includes/footer.php"; ?>

</div>

</body>

</html>