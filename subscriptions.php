<?php

require_once "includes/auth.php";
require_once "config/db.php";

/* ==========================
   Search
========================== */

$search = "";

if(isset($_GET['search'])){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

}

/* ==========================
   Update Dates
========================== */

if(isset($_POST['updateDates'])){

    $id = (int)$_POST['subscription_id'];

    $start = $_POST['start_date'];

    $end = $_POST['end_date'];

    mysqli_query($conn,"
        UPDATE subscriptions
        SET
        start_date='$start',
        end_date='$end'
        WHERE subscription_id='$id'
    ");

    echo "

<script>

alert('Subscription dates updated successfully.');

window.location='subscriptions.php';

</script>

";

    exit;

}

/* ==========================
   Renew
========================== */

if(isset($_POST['renewSubscription'])){

    $player = (int)$_POST['player_id'];

    $type = (int)$_POST['subscription_type'];

    $today = date("Y-m-d");

    mysqli_query($conn,"
        UPDATE subscriptions
        SET is_current=0
        WHERE player_id='$player'
        AND is_current=1
    ");

    $typeInfo = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT duration_months
        FROM subscription_types
        WHERE subscription_type_id='$type'
    "));

    $months = (int)$typeInfo['duration_months'];

    $end = date(
        "Y-m-d",
        strtotime("+".$months." month")
    );

    mysqli_query($conn,"
        INSERT INTO subscriptions(

            player_id,
            subscription_type_id,
            start_date,
            end_date,
            is_current

        )

        VALUES(

            '$player',
            '$type',
            '$today',
            '$end',
            1

        )
    ");

    echo "

<script>

alert('Subscription renewed successfully.');

window.location='subscriptions.php';

</script>

";

    exit;

}

/* ==========================
   Load Players
========================== */

$sql = "

SELECT

p.player_id,
p.player_code,

CONCAT(

p.first_name,' ',
IFNULL(p.middle_name,''),' ',
p.last_name

) AS player_name,

s.subscription_id,
s.subscription_type_id,
s.start_date,
s.end_date,
s.is_current,

st.name,
st.duration_months

FROM players p

LEFT JOIN subscriptions s

ON p.player_id=s.player_id

AND s.is_current=1

LEFT JOIN subscription_types st

ON st.subscription_type_id=s.subscription_type_id

WHERE p.is_active=1

";

if($search!=""){

$sql.="

AND(

p.player_code LIKE '%$search%'

OR

p.first_name LIKE '%$search%'

OR

p.middle_name LIKE '%$search%'

OR

p.last_name LIKE '%$search%'

)

";

}

$sql.="

ORDER BY p.player_code ASC

";

$result = mysqli_query($conn,$sql);

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>

Subscriptions

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

<div
style="
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;">

<h2>

Subscriptions

</h2>

</div>

<input

type="text"

id="searchSub"

placeholder="Search player..."

style="margin-bottom:20px;">

<table id="subTable">

<tr>

<th width="90">

Code

</th>

<th>

Player

</th>

<th width="120">

Plan

</th>

<th width="140">

Start Date

</th>

<th width="140">

End Date

</th>

<th width="180">

Status

</th>

<th width="140">

Actions

</th>

</tr>

<?php

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

$status="Trial";
$badge="trial";
$plan="-";
$statusInfo="No Subscription";

if(!empty($row['subscription_id'])){

$plan=$row['name'];

$today=new DateTime();

$end=new DateTime($row['end_date']);

$days=$today->diff($end)->days;

if($end >= $today){

$status="Active";

$badge="active";

$statusInfo=$days==0

?

"Expires Today"

:

$days." Days Remaining";

}else{

$status="Expired";

$badge="expired";

$statusInfo="Expired ".$days." Days Ago";

}

}

?>

<tr>

<td>

<b>

<?php echo $row['player_code']; ?>

</b>

</td>

<td>

<?php echo $row['player_name']; ?>

</td>

<td>

<?php echo $plan; ?>

</td>

<td>

<?php

echo empty($row['start_date'])

?

"-"

:

date("d M Y",strtotime($row['start_date']));

?>

</td>

<td>

<?php

echo empty($row['end_date'])

?

"-"

:

date("d M Y",strtotime($row['end_date']));

?>

</td>

<td>

<span class="badge <?php echo $badge; ?>">

<?php echo $status; ?>

</span>

<br>

<small style="color:#bbb;">

<?php echo $statusInfo; ?>

</small>

</td>

<td
style="
display:flex;
gap:8px;">



<button

class="btn editBtn"

data-id="<?php echo $row['subscription_id']; ?>"

data-start="<?php echo $row['start_date']; ?>"

data-end="<?php echo $row['end_date']; ?>"

style="
background:#f1c40f;
color:#171321;
padding:8px 12px;">

<i class="fa-solid fa-pen"></i>

</button>



<button

class="btn renewBtn"

data-player="<?php echo $row['player_id']; ?>"

data-name="<?php echo $row['player_name']; ?>"

style="padding:8px 12px;">

<i class="fa-solid fa-rotate-right"></i>

</button>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7"

style="
text-align:center;
color:#aaa;">

No Players Found

</td>

</tr>

<?php

}

?>

</table>

</div>
    
    <!-- ==========================================
     EDIT SUBSCRIPTION MODAL
========================================== -->

<div id="editModal" class="modalOverlay">

<div class="modalBox">

<h2>Edit Subscription Dates</h2>

<form method="POST">

<input
type="hidden"
name="subscription_id"
id="edit_subscription_id">

<label>Start Date</label>

<input
type="date"
name="start_date"
id="edit_start"
required>

<br><br>

<label>End Date</label>

<input
type="date"
name="end_date"
id="edit_end"
required>

<br><br>

<div style="display:flex;justify-content:flex-end;gap:10px;">

<button
type="button"
class="btn"
style="background:#777;color:white;"
onclick="closeEditModal()">

Cancel

</button>

<button
type="submit"
class="btn"
name="updateDates">

Save Changes

</button>

</div>

</form>

</div>

</div>



<!-- ==========================================
     RENEW SUBSCRIPTION MODAL
========================================== -->

<div id="renewModal" class="modalOverlay">

<div class="modalBox">

<h2>Renew Subscription</h2>

<form method="POST">

<input
type="hidden"
name="player_id"
id="renew_player">

<label>Player</label>

<input
id="renew_name"
readonly>

<br><br>

<label>Subscription Plan</label>

<select
name="subscription_type"
required>

<option value="1">

1 Month

</option>

<option value="2">

3 Months

</option>

</select>

<br><br>

<div style="display:flex;justify-content:flex-end;gap:10px;">

<button
type="button"
class="btn"
style="background:#777;color:white;"
onclick="closeRenewModal()">

Cancel

</button>

<button
type="submit"
class="btn"
name="renewSubscription">

Renew

</button>

</div>

</form>

</div>

</div>

<script>

/* ==========================
   Renew Modal
========================== */

document.querySelectorAll(".renewBtn").forEach(function(btn){

    btn.onclick=function(){

        document.getElementById("renewModal").style.display="flex";

        document.getElementById("renew_player").value=this.dataset.player;

        document.getElementById("renew_name").value=this.dataset.name;

    };

});


/* ==========================
   Edit Modal
========================== */

document.querySelectorAll(".editBtn").forEach(function(btn){

    btn.onclick=function(){

        document.getElementById("editModal").style.display="flex";

        document.getElementById("edit_subscription_id").value=this.dataset.id;

        document.getElementById("edit_start").value=this.dataset.start;

        document.getElementById("edit_end").value=this.dataset.end;

    };

});


/* ==========================
   Close Modals
========================== */

function closeEditModal(){

    document.getElementById("editModal").style.display="none";

}

function closeRenewModal(){

    document.getElementById("renewModal").style.display="none";

}


/* ==========================
   Close Outside
========================== */

window.onclick=function(e){

    let edit=document.getElementById("editModal");

    let renew=document.getElementById("renewModal");

    if(e.target==edit){

        closeEditModal();

    }

    if(e.target==renew){

        closeRenewModal();

    }

}


/* ==========================
   Live Search
========================== */

document.getElementById("searchSub").addEventListener("keyup",function(){

    let filter=this.value.toUpperCase();

    let rows=document.querySelectorAll("#subTable tr");

    for(let i=1;i<rows.length;i++){

        let txt=rows[i].textContent.toUpperCase();

        rows[i].style.display=txt.includes(filter)?"":"none";

    }

});

</script>

