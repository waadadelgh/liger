<?php

$today = date("l, d F Y");
$admin = explode(" ", $_SESSION['admin_name'])[0];

?>

<div class="header">

    <div class="headerLeft">

        <h1>

            Welcome,

            <span><?php echo $admin; ?></span>

            👋

        </h1>

        <p><?php echo $today; ?></p>

    </div>

    <div class="headerRight">

        <div class="avatar">

            <i class="fa-solid fa-user"></i>

        </div>

        <span><?php echo $admin; ?></span>

    </div>

</div>