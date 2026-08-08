<?php
require_once "includes/auth.php";
require_once "config/db.php";

$date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

/* ==========================
   Attendance Summary
========================== */

// Registered players present
$playersPresent = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM attendance
WHERE attendance_date='$date'
"))['total'];

// Non-members present
$nonMembersPresent = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM non_member_attendance
WHERE attendance_date='$date'
"))['total'];

$totalAttendance = $playersPresent + $nonMembersPresent;

/* ==========================
   Subscription Overview
========================== */

// Active
$active = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM subscriptions
WHERE is_current=1
AND end_date>=CURDATE()
"))['total'];

// Expired
$expired = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM subscriptions
WHERE is_current=1
AND end_date<CURDATE()
"))['total'];

// Trial
$trial = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM players p
LEFT JOIN subscriptions s
ON p.player_id=s.player_id
AND s.is_current=1
WHERE s.subscription_id IS NULL
AND p.is_active=1
"))['total'];
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>

Attendance Reports

</title>

<link
rel="stylesheet"
href="assets/css/style.css">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<?php include "includes/sidebar.php"; ?>

<div class="main">

<?php include "includes/header.php"; ?>

<div class="panel">

<div style="
display:flex;
justify-content:space-between;
align-items:center;">

<h2>

Attendance Reports

</h2>

<a
href="export_pdf.php?date=<?php echo $date; ?>"
class="btn">

<i class="fa-solid fa-file-pdf"></i>

Export PDF

</a>

</div>

<br>

<form method="GET">

<div style="
display:flex;
gap:10px;
align-items:center;">

<input
type="date"
name="date"
value="<?php echo $date; ?>">

<button
class="btn">

Generate Report

</button>

</div>

</form>

<br>

<?php

/* ==========================
   Registered Players
========================== */

$players = mysqli_query($conn, "

SELECT

p.player_code,

CONCAT(
p.first_name,' ',
IFNULL(p.middle_name,''),' ',
p.last_name
) AS player_name,

CASE

WHEN s.subscription_id IS NULL
THEN 'Trial'

WHEN s.end_date >= CURDATE()
THEN 'Active'

ELSE 'Expired'

END AS status

FROM attendance a

INNER JOIN players p
ON a.player_id=p.player_id

LEFT JOIN subscriptions s
ON p.player_id=s.player_id
AND s.is_current=1

WHERE a.attendance_date='$date'

ORDER BY p.player_code

");

/* ==========================
   Non Members
========================== */

$nonMembers = mysqli_query($conn, "

SELECT

full_name

FROM non_member_attendance n

INNER JOIN non_members m
ON n.non_member_id=m.non_member_id

WHERE n.attendance_date='$date'

ORDER BY full_name

");

?>

<!-- ==========================
     Attendance Summary
========================== -->

<h3>Attendance Summary</h3>

<div class="stats-grid">

<div class="stat-card">

<h1><?php echo $playersPresent; ?></h1>

<p>Registered Players Present</p>

</div>

<div class="stat-card">

<h1><?php echo $nonMembersPresent; ?></h1>

<p>Non-Members Present</p>

</div>

<div class="stat-card">

<h1><?php echo $totalAttendance; ?></h1>

<p>Total Attendance Recorded</p>

</div>

</div>

<br>

<!-- ==========================
     Subscription Overview
========================== -->

<h3>Subscription Overview</h3>

<div class="stats-grid">

<div class="stat-card">

<h1><?php echo $active; ?></h1>

<p>Active Subscriptions</p>

</div>

<div class="stat-card">

<h1><?php echo $expired; ?></h1>

<p>Expired Subscriptions</p>

</div>

<div class="stat-card">

<h1><?php echo $trial; ?></h1>

<p>Trial Players</p>

</div>

</div>

<br>

<!-- ==========================
     Registered Players
========================== -->

<h3>Registered Players Attendance</h3>

<table>

<tr>

<th>Player Code</th>

<th>Player Name</th>

<th>Subscription Status</th>

</tr>

<?php

if(mysqli_num_rows($players)>0){

while($row=mysqli_fetch_assoc($players)){

$statusColor="trial";

if($row['status']=="Active"){

$statusColor="active";

}elseif($row['status']=="Expired"){

$statusColor="expired";

}

?>

<tr>

<td><?php echo $row['player_code']; ?></td>

<td><?php echo $row['player_name']; ?></td>

<td>

<span class="badge <?php echo $statusColor; ?>">

<?php echo $row['status']; ?>

</span>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="3">

No attendance records found.

</td>

</tr>

<?php

}

?>

</table>

<br>

<!-- ==========================
     Non Members
========================== -->

<h3>Non-Members Attendance</h3>

<table>

<tr>

<th>Name</th>

</tr>

<?php

if(mysqli_num_rows($nonMembers)>0){

while($row=mysqli_fetch_assoc($nonMembers)){

?>

<tr>

<td><?php echo $row['full_name']; ?></td>

</tr>

<?php

}

}else{

?>

<tr>

<td>

No non-members attended.

</td>

</tr>

<?php

}

?>

</table>

<br>

<div class="card">

<h3>Report Summary</h3>

<p>

This report summarizes attendance for the selected date,
including registered academy players and approved non-members.
Subscription statistics are based on each player's current
subscription status.

</p>

</div>

</div>

<?php include "includes/footer.php"; ?>

</div>

</body>

</html>