<?php
require_once "includes/auth.php";
require_once "config/db.php";

/* -------------------------
   Search
--------------------------*/

$search = "";

if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn,$_GET['search']);
}

/* -------------------------
   Players
--------------------------*/

$sql = "

SELECT

p.player_id,
p.player_code,
p.first_name,
p.middle_name,
p.last_name,
p.birth_date,
p.gender,
p.phone,
p.join_date,

s.subscription_id,
s.end_date,

st.name AS subscription_name

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

$result=mysqli_query($conn,$sql);

/* Next Player Code */

$next=mysqli_fetch_assoc(

mysqli_query($conn,"

SELECT

IFNULL(MAX(player_code)+1,1001)

AS code

FROM players

")

);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>

Players

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

<div
style="
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;">

<h2>

Players

</h2>

<button
class="btn"
type="button"
onclick="openAddPlayer()">

<i class="fa-solid fa-user-plus"></i>

Add Player

</button>

</div>

<input
type="text"
id="searchPlayer"
placeholder="Search player..."
style="margin-bottom:20px;">

<table id="playersTable">

<tr>

<th width="90">

Code

</th>

<th>

Player

</th>

<th width="220">

Subscription

</th>

<th>

Phone

</th>

<th width="140">

Join Date

</th>

<th width="180">

Actions

</th>

</tr>

<?php

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

$status="Trial";
$badge="trial";
$statusInfo="No Subscription";

if(!empty($row['subscription_id'])){

    $today=new DateTime();
    $end=new DateTime($row['end_date']);

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

?>

<tr>

<td>

<b>

<?php echo $row['player_code']; ?>

</b>

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

<br>

<small
style="color:#bbb;">

<?php echo $statusInfo; ?>

</small>

</td>

<td>

<?php

echo empty($row['phone'])

?

"-"

:

$row['phone'];

?>

</td>

<td>

<?php echo date("d M Y",strtotime($row['join_date'])); ?>

</td>

<td>

<div
style="
display:flex;
gap:8px;">

<a
href="player_profile.php?id=<?php echo $row['player_id']; ?>"
class="btn"
style="padding:8px 12px;">

<i class="fa-solid fa-eye"></i>

</a>

<button
class="btn editBtn"
style="padding:8px 12px;"

data-id="<?php echo $row['player_id']; ?>"

data-code="<?php echo $row['player_code']; ?>"

data-first="<?php echo htmlspecialchars($row['first_name']); ?>"

data-middle="<?php echo htmlspecialchars($row['middle_name']); ?>"

data-last="<?php echo htmlspecialchars($row['last_name']); ?>"

data-phone="<?php echo htmlspecialchars($row['phone']); ?>"

data-birth="<?php echo $row['birth_date']; ?>"

data-gender="<?php echo $row['gender']; ?>"

data-join="<?php echo $row['join_date']; ?>">

<i class="fa-solid fa-pen"></i>

</button>

<button
class="btn deleteBtn"
style="
padding:8px 12px;
background:#E74C3C;
color:white;"

data-id="<?php echo $row['player_id']; ?>">

<i class="fa-solid fa-trash"></i>

</button>

</div>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="6"
style="
text-align:center;
color:#999;">

No players found.

</td>

</tr>

<?php

}

?>

</table>

</div>
    
    <!-- =========================
     ADD PLAYER MODAL
========================== -->

<div
id="addPlayerModal"
style="
display:none;
position:fixed;
left:0;
top:0;
width:100%;
height:100%;
background:rgba(0,0,0,.65);
justify-content:center;
align-items:center;
z-index:999;">

<div
style="
background:#2D1B51;
width:650px;
max-width:95%;
padding:30px;
border-radius:18px;">

<h2>Add Player</h2>

<br>

<form method="POST">

<div
style="
display:grid;
grid-template-columns:1fr 1fr;
gap:15px;">

<div>

<label>Player Code</label>

<input
type="text"
name="player_code"
readonly
value="<?php echo $next['code']; ?>">

</div>

<div>

<label>Join Date</label>

<input
type="date"
name="join_date"
value="<?php echo date('Y-m-d'); ?>"
required>

</div>

<div>

<label>First Name</label>

<input
type="text"
name="first_name"
required>

</div>

<div>

<label>Middle Name</label>

<input
type="text"
name="middle_name">

</div>

<div>

<label>Last Name</label>

<input
type="text"
name="last_name"
required>

</div>

<div>

<label>Birth Date</label>

<input
type="date"
name="birth_date"
required>

</div>

<div>

<label>Gender</label>

<select name="gender">

<option value="Male">Male</option>

<option value="Female">Female</option>

</select>

</div>

<div>

<label>Phone</label>

<input
type="text"
name="phone">

</div>

</div>

<br>

<div
style="
display:flex;
justify-content:flex-end;
gap:10px;">

<button
type="button"
class="btn"
style="background:#777;color:white;"
onclick="closeAddPlayer()">

Cancel

</button>

<button
class="btn"
name="savePlayer">

Save Player

</button>

</div>

</form>

</div>

</div>
    
    <?php

if(isset($_POST['savePlayer'])){

$code=mysqli_real_escape_string($conn,$_POST['player_code']);
$first=mysqli_real_escape_string($conn,$_POST['first_name']);
$middle=mysqli_real_escape_string($conn,$_POST['middle_name']);
$last=mysqli_real_escape_string($conn,$_POST['last_name']);
$birth=$_POST['birth_date'];
$gender=$_POST['gender'];
$phone=mysqli_real_escape_string($conn,$_POST['phone']);
$join=$_POST['join_date'];

mysqli_query($conn,"

INSERT INTO players(

player_code,
first_name,
middle_name,
last_name,
birth_date,
gender,
phone,
join_date

)

VALUES(

'$code',
'$first',
'$middle',
'$last',
'$birth',
'$gender',
'$phone',
'$join'

)

");

echo "

<script>

alert('Player added successfully.');

window.location='players.php';

</script>

";

}

?>

    <script>

function openAddPlayer(){

document.getElementById("addPlayerModal").style.display="flex";

}

function closeAddPlayer(){

document.getElementById("addPlayerModal").style.display="none";

}

window.onclick=function(e){

let modal=document.getElementById("addPlayerModal");

if(e.target==modal){

modal.style.display="none";

}

}

</script>

<?php

if(isset($_POST['updatePlayer'])){

$id=(int)$_POST['player_id'];

$first=mysqli_real_escape_string($conn,$_POST['first_name']);
$middle=mysqli_real_escape_string($conn,$_POST['middle_name']);
$last=mysqli_real_escape_string($conn,$_POST['last_name']);

$birth=$_POST['birth_date'];
$gender=$_POST['gender'];

$phone=mysqli_real_escape_string($conn,$_POST['phone']);

$join=$_POST['join_date'];

mysqli_query($conn,"

UPDATE players SET

first_name='$first',

middle_name='$middle',

last_name='$last',

birth_date='$birth',

gender='$gender',

phone='$phone',

join_date='$join',

updated_at=NOW()

WHERE player_id='$id'

");

echo "

<script>

alert('Player Updated Successfully');

window.location='players.php';

</script>

";

}

?>

<?php

if(isset($_POST['deletePlayer'])){

$id=(int)$_POST['player_id'];

mysqli_query($conn,"

UPDATE players

SET is_active=0

WHERE player_id='$id'

");

echo "

<script>

alert('Player Deleted Successfully');

window.location='players.php';

</script>

";

}

?>

<div
id="editPlayerModal"
style="
display:none;
position:fixed;
left:0;
top:0;
width:100%;
height:100%;
background:rgba(0,0,0,.65);
justify-content:center;
align-items:center;
z-index:999;">

<div
style="
background:#2D1B51;
width:650px;
max-width:95%;
padding:30px;
border-radius:18px;">

<h2>Edit Player</h2>

<br>

<form method="POST">

<input type="hidden" name="player_id" id="edit_id">

<div
style="
display:grid;
grid-template-columns:1fr 1fr;
gap:15px;">

<div>

<label>Player Code</label>

<input
id="edit_code"
readonly>

</div>

<div>

<label>Join Date</label>

<input
type="date"
name="join_date"
id="edit_join">

</div>

<div>

<label>First Name</label>

<input
name="first_name"
id="edit_first">

</div>

<div>

<label>Middle Name</label>

<input
name="middle_name"
id="edit_middle">

</div>

<div>

<label>Last Name</label>

<input
name="last_name"
id="edit_last">

</div>

<div>

<label>Birth Date</label>

<input
type="date"
name="birth_date"
id="edit_birth">

</div>

<div>

<label>Gender</label>

<select
name="gender"
id="edit_gender">

<option>Male</option>

<option>Female</option>

</select>

</div>

<div>

<label>Phone</label>

<input
name="phone"
id="edit_phone">

</div>

</div>

<br>

<div
style="
display:flex;
justify-content:flex-end;
gap:10px;">

<button
type="button"
class="btn"
style="background:#777;color:white;"
onclick="closeEditPlayer()">

Cancel

</button>

<button
class="btn"
name="updatePlayer">

Update

</button>

</div>

</form>

</div>

</div>

<script>

function openAddPlayer(){

document.getElementById("addPlayerModal").style.display="flex";

}

function closeAddPlayer(){

document.getElementById("addPlayerModal").style.display="none";

}

/* Edit */

const editButtons=document.querySelectorAll(".editBtn");

editButtons.forEach(function(btn){

btn.onclick=function(){

document.getElementById("editPlayerModal").style.display="flex";

document.getElementById("edit_id").value=this.dataset.id;
document.getElementById("edit_code").value=this.dataset.code;
document.getElementById("edit_first").value=this.dataset.first;
document.getElementById("edit_middle").value=this.dataset.middle;
document.getElementById("edit_last").value=this.dataset.last;
document.getElementById("edit_phone").value=this.dataset.phone;
document.getElementById("edit_birth").value=this.dataset.birth;
document.getElementById("edit_gender").value=this.dataset.gender;
document.getElementById("edit_join").value=this.dataset.join;

}

});

function closeEditPlayer(){

document.getElementById("editPlayerModal").style.display="none";

}

/* Delete */

const deleteButtons=document.querySelectorAll(".deleteBtn");

deleteButtons.forEach(function(btn){

btn.onclick=function(){

if(confirm("Delete this player?")){

let form=document.createElement("form");

form.method="POST";

let id=document.createElement("input");

id.type="hidden";

id.name="player_id";

id.value=this.dataset.id;

form.appendChild(id);

let action=document.createElement("input");

action.type="hidden";

action.name="deletePlayer";

action.value="1";

form.appendChild(action);

document.body.appendChild(form);

form.submit();

}

}

});

/* Live Search */

document.getElementById("searchPlayer").addEventListener("keyup",function(){

let filter=this.value.toUpperCase();

let rows=document.querySelectorAll("#playersTable tr");

for(let i=1;i<rows.length;i++){

let text=rows[i].textContent.toUpperCase();

rows[i].style.display=text.includes(filter)?"":"none";

}

});

/* Close Modals */

window.onclick=function(e){

let add=document.getElementById("addPlayerModal");

let edit=document.getElementById("editPlayerModal");

if(e.target==add){

closeAddPlayer();

}

if(e.target==edit){

closeEditPlayer();

}

}

</script>

<?php include "includes/footer.php"; ?>
