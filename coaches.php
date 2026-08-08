<?php

require_once "includes/auth.php";
require_once "config/db.php";

/* ==========================================
   SEARCH
========================================== */

$search="";

if(isset($_GET['search'])){

    $search=mysqli_real_escape_string($conn,$_GET['search']);

}

/* ==========================================
   ADD COACH
========================================== */

if(isset($_POST['addCoach'])){

    $first=mysqli_real_escape_string($conn,$_POST['first_name']);
    $middle=mysqli_real_escape_string($conn,$_POST['middle_name']);
    $last=mysqli_real_escape_string($conn,$_POST['last_name']);

    $team=mysqli_real_escape_string($conn,$_POST['team']);

    $phone=mysqli_real_escape_string($conn,$_POST['phone']);

    $email=mysqli_real_escape_string($conn,$_POST['email']);

    $hire=$_POST['hire_date'];

    mysqli_query($conn,"

        INSERT INTO coaches(

            first_name,
            middle_name,
            last_name,
            team,
            phone,
            email,
            hire_date

        )

        VALUES(

            '$first',
            '$middle',
            '$last',
            '$team',
            '$phone',
            '$email',
            '$hire'

        )

    ");

    echo "

    <script>

    alert('Coach added successfully.');

    window.location='coaches.php';

    </script>

    ";

    exit;

}

/* ==========================================
   UPDATE COACH
========================================== */

if(isset($_POST['updateCoach'])){

    $id=(int)$_POST['coach_id'];

    $first=mysqli_real_escape_string($conn,$_POST['first_name']);
    $middle=mysqli_real_escape_string($conn,$_POST['middle_name']);
    $last=mysqli_real_escape_string($conn,$_POST['last_name']);

    $team=mysqli_real_escape_string($conn,$_POST['team']);

    $phone=mysqli_real_escape_string($conn,$_POST['phone']);

    $email=mysqli_real_escape_string($conn,$_POST['email']);

    $hire=$_POST['hire_date'];

    mysqli_query($conn,"

        UPDATE coaches

        SET

        first_name='$first',

        middle_name='$middle',

        last_name='$last',

        team='$team',

        phone='$phone',

        email='$email',

        hire_date='$hire'

        WHERE coach_id='$id'

    ");

    echo "

    <script>

    alert('Coach updated successfully.');

    window.location='coaches.php';

    </script>

    ";

    exit;

}

/* ==========================================
   DELETE
========================================== */

if(isset($_GET['delete'])){

    $id=(int)$_GET['delete'];

    mysqli_query($conn,"
        DELETE FROM coaches
        WHERE coach_id='$id'
    ");

    echo "

    <script>

    alert('Coach deleted.');

    window.location='coaches.php';

    </script>

    ";

    exit;

}

/* ==========================================
   LOAD COACHES
========================================== */

$sql="

SELECT *

FROM coaches

";

if($search!=""){

$sql.="

WHERE

first_name LIKE '%$search%'

OR

middle_name LIKE '%$search%'

OR

last_name LIKE '%$search%'

OR

team LIKE '%$search%'

OR

phone LIKE '%$search%'

OR

email LIKE '%$search%'

";

}

$sql.="

ORDER BY first_name ASC

";

$coaches=mysqli_query($conn,$sql);

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width,initial-scale=1">

<title>

Coaches

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

Coaches

</h2>

<button
class="btn"
id="addCoachBtn">

<i class="fa-solid fa-plus"></i>

Add Coach

</button>

</div>

<input

type="text"

id="searchCoach"

placeholder="Search coach..."

style="margin-bottom:20px;">

<table id="coachTable">

<tr>

<th width="80">

ID

</th>

<th>

Coach

</th>

<th width="150">

Team

</th>

<th width="150">

Phone

</th>

<th>

Email

</th>

<th width="140">

Hire Date

</th>

<th width="180">

Actions

</th>

</tr>

<?php

if(mysqli_num_rows($coaches)>0){

while($row=mysqli_fetch_assoc($coaches)){

?>

<tr>

<td>

<?php echo $row['coach_id']; ?>

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

<?php echo $row['team']; ?>

</td>

<td>

<?php echo $row['phone']; ?>

</td>

<td>

<?php echo $row['email']; ?>

</td>

<td>

<?php echo date("d M Y",strtotime($row['hire_date'])); ?>

</td>

<td
style="
display:flex;
gap:8px;">

<button

class="btn viewBtn"

data-name="<?php echo $row['first_name']." ".$row['middle_name']." ".$row['last_name']; ?>"

data-team="<?php echo $row['team']; ?>"

data-phone="<?php echo $row['phone']; ?>"

data-email="<?php echo $row['email']; ?>"

data-hire="<?php echo $row['hire_date']; ?>"

style="padding:8px 12px;background:#3498db;">

<i class="fa-solid fa-eye"></i>

</button>

<button

class="btn editBtn"

data-id="<?php echo $row['coach_id']; ?>"

data-first="<?php echo $row['first_name']; ?>"

data-middle="<?php echo $row['middle_name']; ?>"

data-last="<?php echo $row['last_name']; ?>"

data-team="<?php echo $row['team']; ?>"

data-phone="<?php echo $row['phone']; ?>"

data-email="<?php echo $row['email']; ?>"

data-hire="<?php echo $row['hire_date']; ?>"

style="padding:8px 12px;background:#f1c40f;color:black;">

<i class="fa-solid fa-pen"></i>

</button>

<a

class="btn"

href="coaches.php?delete=<?php echo $row['coach_id']; ?>"

onclick="return confirm('Delete this coach?')"

style="padding:8px 12px;background:#e74c3c;">

<i class="fa-solid fa-trash"></i>

</a>

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

No Coaches Found

</td>

</tr>

<?php

}

?>

</table>

</div>
    
    <!-- ==========================
     ADD COACH
========================== -->

<div id="addModal" class="modalOverlay">

<div class="modalBox">

<h2>Add Coach</h2>

<form method="POST">

<label>First Name</label>
<input type="text" name="first_name" required>

<br><br>

<label>Middle Name</label>
<input type="text" name="middle_name">

<br><br>

<label>Last Name</label>
<input type="text" name="last_name" required>

<br><br>

<label>Team</label>
<input type="text" name="team" required>

<br><br>

<label>Phone</label>
<input type="text" name="phone" required>

<br><br>

<label>Email</label>
<input type="email" name="email">

<br><br>

<label>Hire Date</label>
<input type="date" name="hire_date" required>

<br><br>

<div style="display:flex;justify-content:flex-end;gap:10px;">

<button
type="button"
class="btn"
style="background:#777;color:white;"
onclick="closeAddModal()">

Cancel

</button>

<button
type="submit"
class="btn"
name="addCoach">

Save

</button>

</div>

</form>

</div>

</div>
    
    <!-- ==========================
     VIEW COACH
========================== -->

<div id="viewModal" class="modalOverlay">

<div class="modalBox">

<h2>Coach Information</h2>

<p><b>Name:</b> <span id="view_name"></span></p>

<p><b>Team:</b> <span id="view_team"></span></p>

<p><b>Phone:</b> <span id="view_phone"></span></p>

<p><b>Email:</b> <span id="view_email"></span></p>

<p><b>Hire Date:</b> <span id="view_hire"></span></p>

<br>

<div style="text-align:right;">

<button
class="btn"
onclick="closeViewModal()">

Close

</button>

</div>

</div>

</div>
    
    <!-- ==========================
     EDIT COACH
========================== -->

<div id="editModal" class="modalOverlay">

<div class="modalBox">

<h2>Edit Coach</h2>

<form method="POST">

<input
type="hidden"
name="coach_id"
id="edit_id">

<label>First Name</label>

<input
type="text"
name="first_name"
id="edit_first"
required>

<br><br>

<label>Middle Name</label>

<input
type="text"
name="middle_name"
id="edit_middle">

<br><br>

<label>Last Name</label>

<input
type="text"
name="last_name"
id="edit_last"
required>

<br><br>

<label>Team</label>

<input
type="text"
name="team"
id="edit_team"
required>

<br><br>

<label>Phone</label>

<input
type="text"
name="phone"
id="edit_phone"
required>

<br><br>

<label>Email</label>

<input
type="email"
name="email"
id="edit_email">

<br><br>

<label>Hire Date</label>

<input
type="date"
name="hire_date"
id="edit_hire"
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
name="updateCoach">

Save Changes

</button>

</div>

</form>

</div>

</div>
    
    <script>

/* ==========================
   Add Coach
========================== */

document.getElementById("addCoachBtn").onclick=function(){

    document.getElementById("addModal").style.display="flex";

};

function closeAddModal(){

    document.getElementById("addModal").style.display="none";

}


/* ==========================
   View Coach
========================== */

document.querySelectorAll(".viewBtn").forEach(function(btn){

    btn.onclick=function(){

        document.getElementById("viewModal").style.display="flex";

        document.getElementById("view_name").innerHTML=this.dataset.name;

        document.getElementById("view_team").innerHTML=this.dataset.team;

        document.getElementById("view_phone").innerHTML=this.dataset.phone;

        document.getElementById("view_email").innerHTML=this.dataset.email;

        document.getElementById("view_hire").innerHTML=this.dataset.hire;

    };

});

function closeViewModal(){

    document.getElementById("viewModal").style.display="none";

}


/* ==========================
   Edit Coach
========================== */

document.querySelectorAll(".editBtn").forEach(function(btn){

    btn.onclick=function(){

        document.getElementById("editModal").style.display="flex";

        document.getElementById("edit_id").value=this.dataset.id;

        document.getElementById("edit_first").value=this.dataset.first;

        document.getElementById("edit_middle").value=this.dataset.middle;

        document.getElementById("edit_last").value=this.dataset.last;

        document.getElementById("edit_team").value=this.dataset.team;

        document.getElementById("edit_phone").value=this.dataset.phone;

        document.getElementById("edit_email").value=this.dataset.email;

        document.getElementById("edit_hire").value=this.dataset.hire;

    };

});

function closeEditModal(){

    document.getElementById("editModal").style.display="none";

}


/* ==========================
   Close When Clicking Outside
========================== */

window.onclick=function(e){

    if(e.target==document.getElementById("addModal")){

        closeAddModal();

    }

    if(e.target==document.getElementById("viewModal")){

        closeViewModal();

    }

    if(e.target==document.getElementById("editModal")){

        closeEditModal();

    }

}


/* ==========================
   Live Search
========================== */

document.getElementById("searchCoach").addEventListener("keyup",function(){

    let filter=this.value.toUpperCase();

    let rows=document.querySelectorAll("#coachTable tr");

    for(let i=1;i<rows.length;i++){

        let txt=rows[i].textContent.toUpperCase();

        rows[i].style.display=txt.includes(filter) ? "" : "none";

    }

});

</script>