

<?php

require_once "includes/auth.php";
require_once "config/db.php";

$today = date("Y-m-d");
$search = "";

/* ==========================
   Search
========================== */

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
}

/* ==========================
   Save Attendance
========================== */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /* ---------- Academy Players ---------- */

    if (!empty($_POST['attendance'])) {

        foreach ($_POST['attendance'] as $player_id) {

            $player_id = (int)$player_id;

            $check = mysqli_query($conn,"
                SELECT attendance_id
                FROM attendance
                WHERE player_id = $player_id
                AND attendance_date = '$today'
            ");

            if(mysqli_num_rows($check)==0){

                mysqli_query($conn,"
                    INSERT INTO attendance
                    (
                        player_id,
                        attendance_date,
                        check_in_time
                    )
                    VALUES
                    (
                        $player_id,
                        '$today',
                        CURTIME()
                    )
                ");

            }

        }

    }

    /* ---------- Non Members ---------- */

    if (!empty($_POST['non_members'])) {

        foreach ($_POST['non_members'] as $member_id) {

            $member_id = (int)$member_id;

            $check = mysqli_query($conn,"
                SELECT attendance_id
                FROM non_member_attendance
                WHERE non_member_id = $member_id
                AND attendance_date = '$today'
            ");

            if(mysqli_num_rows($check)==0){

                mysqli_query($conn,"
                    INSERT INTO non_member_attendance
                    (
                        non_member_id,
                        attendance_date,
                        check_in_time
                    )
                    VALUES
                    (
                        $member_id,
                        '$today',
                        CURTIME()
                    )
                ");

            }

        }

    }

    header("Location: attendance.php?success=1");
    exit;

}

/* ==========================
   Players
========================== */

$sql = "

SELECT

p.player_id,
p.player_code,
p.first_name,
p.middle_name,
p.last_name,

s.subscription_id,
s.end_date

FROM players p

LEFT JOIN subscriptions s
ON s.subscription_id=(

SELECT subscription_id
FROM subscriptions
WHERE player_id=p.player_id
AND is_current=1
LIMIT 1

)

WHERE p.is_active=1

";

if($search!=""){

$sql.="

AND
(

player_code LIKE '%$search%'

OR first_name LIKE '%$search%'

OR middle_name LIKE '%$search%'

OR last_name LIKE '%$search%'

)

";

}

$sql.=" ORDER BY player_code";

$players=mysqli_query($conn,$sql);

/* ==========================
   Non Members
========================== */

$nonMembers=mysqli_query($conn,"
SELECT *
FROM non_members
ORDER BY full_name
");
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Attendance</title>

<link rel="stylesheet" href="assets/css/style.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<?php include "includes/sidebar.php"; ?>

<div class="main">

<?php include "includes/header.php"; ?>

<div class="panel">

<?php if(isset($_GET['success'])){ ?>

<div style="
background:#198754;
color:white;
padding:12px 18px;
border-radius:8px;
margin-bottom:20px;
">

Attendance saved successfully.

</div>

<?php } ?>

<div style="
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
">

<h2>Today's Attendance</h2>

<p><?php echo date("d F Y"); ?></p>

</div>

<!-- Search -->

<form method="GET">

<div style="
display:flex;
gap:10px;
margin-bottom:25px;
">

<input
type="text"
name="search"
placeholder="Search player..."
value="<?php echo $search; ?>">

<button class="btn">

<i class="fa-solid fa-magnifying-glass"></i>

Search

</button>

</div>

</form>

<form method="POST">

<h3>Academy Players</h3>

<br>

<table>

<tr>

<th width="60">

<input
type="checkbox"
id="checkPlayers">

</th>

<th width="90">Code</th>

<th>Player</th>

<th width="180">Subscription</th>

</tr>

<?php while($row=mysqli_fetch_assoc($players)){ ?>

<?php

$status="Trial";
$badge="trial";

if(!empty($row['subscription_id'])){

if(strtotime($row['end_date'])>=strtotime($today)){

$status="Active";
$badge="active";

}else{

$status="Expired";
$badge="expired";

}

}

?>

<tr>

<td>

<input
type="checkbox"
class="playerBox"
name="attendance[]"
value="<?php echo $row['player_id']; ?>">

</td>

<td>

<?php echo $row['player_code']; ?>

</td>

<td>

<?php

echo

$row['first_name']." ".
$row['middle_name']." ".
$row['last_name'];

?>

</td>

<td>

<span class="badge <?php echo $badge; ?>">

<?php echo $status; ?>

</span>

</td>

</tr>

<?php } ?>

</table>

<hr style="margin:35px 0;">

<h3>Non-Members</h3>

<br>

<table>

<tr>

<th width="60">

<input
type="checkbox"
id="checkNonMembers">

</th>

<th>Name</th>

</tr>

<?php while($row=mysqli_fetch_assoc($nonMembers)){ ?>

<tr>

<td>

<input
type="checkbox"
class="nonMemberBox"
name="non_members[]"
value="<?php echo $row['non_member_id']; ?>">

</td>

<td>

<?php echo $row['full_name']; ?>

</td>

</tr>

<?php } ?>

</table>

<br>

<div style="
background:#2D1B51;
padding:20px;
border-radius:12px;
">

<h3>Today's Summary</h3>

<br>

Academy Players:
<b id="playerCount">0</b>

<br><br>

Non-Members:
<b id="nonCount">0</b>

<br><br>

Total Attendance:
<b id="totalCount">0</b>

</div>

<br>

<div style="
display:flex;
justify-content:flex-end;
">

<button
class="btn"
type="submit">

<i class="fa-solid fa-floppy-disk"></i>

Save Attendance

</button>

</div>

</form>

</div>

<?php include "includes/footer.php"; ?>

</body>

</html>

<script>

/* ==========================
   Select All Players
========================== */

document.getElementById("checkPlayers").addEventListener("change", function(){

    document.querySelectorAll(".playerBox").forEach(function(box){

        box.checked = document.getElementById("checkPlayers").checked;

    });

    updateSummary();

});

/* ==========================
   Select All Non Members
========================== */

document.getElementById("checkNonMembers").addEventListener("change", function(){

    document.querySelectorAll(".nonMemberBox").forEach(function(box){

        box.checked = document.getElementById("checkNonMembers").checked;

    });

    updateSummary();

});

/* ==========================
   Update Summary
========================== */

function updateSummary(){

    let players =
    document.querySelectorAll(".playerBox:checked").length;

    let nonMembers =
    document.querySelectorAll(".nonMemberBox:checked").length;

    document.getElementById("playerCount").innerHTML = players;

    document.getElementById("nonCount").innerHTML = nonMembers;

    document.getElementById("totalCount").innerHTML =
    players + nonMembers;

}

/* ==========================
   Individual Checkbox Change
========================== */

document.querySelectorAll(".playerBox,.nonMemberBox").forEach(function(box){

    box.addEventListener("change", updateSummary);

});

updateSummary();

/* ==========================
   Confirm Before Saving
========================== */

document.querySelector("form[method='POST']").addEventListener("submit", function(e){

    let players =
    document.querySelectorAll(".playerBox:checked").length;

    let nonMembers =
    document.querySelectorAll(".nonMemberBox:checked").length;

    let total = players + nonMembers;

    if(total == 0){

        alert("Please select at least one attendee.");

        e.preventDefault();

        return;

    }

    if(!confirm(

        "Save today's attendance?\n\n" +

        "Academy Players: " + players +

        "\nNon-Members: " + nonMembers +

        "\nTotal Attendance: " + total

    )){

        e.preventDefault();

    }

});

</script>

<?php include "includes/footer.php"; ?>