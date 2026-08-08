<?php

$current = basename($_SERVER['PHP_SELF']);

?>

<div class="sidebar">

    <div class="logo">

    <img src="assets/images/liger_logo.png" alt="LIGER Logo">

    <h2>LIGER</h2>

    <p>Football Academy</p>

    <div class="logoLine"></div>

    <span>Elite Training Centre</span>

</div>

    <ul class="menu">

        <li class="<?php echo ($current=='index.php') ? 'active' : ''; ?>">
            <a href="index.php">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="<?php echo ($current=='attendance.php') ? 'active' : ''; ?>">
            <a href="attendance.php">
                <i class="fa-solid fa-calendar-check"></i>
                <span>Attendance</span>
            </a>
        </li>

        <li class="<?php echo ($current=='players.php' || $current=='player_profile.php') ? 'active' : ''; ?>">
            <a href="players.php">
                <i class="fa-solid fa-users"></i>
                <span>Players</span>
            </a>
        </li>

        <li class="<?php echo ($current=='coaches.php') ? 'active' : ''; ?>">
            <a href="coaches.php">
                <i class="fa-solid fa-user-tie"></i>
                <span>Coaches</span>
            </a>
        </li>

        <li class="<?php echo ($current=='subscriptions.php') ? 'active' : ''; ?>">
            <a href="subscriptions.php">
                <i class="fa-solid fa-credit-card"></i>
                <span>Subscriptions</span>
            </a>
        </li>

        <li class="<?php echo ($current=='reports.php') ? 'active' : ''; ?>">
            <a href="reports.php">
                <i class="fa-solid fa-chart-column"></i>
                <span>Reports</span>
            </a>
        </li>

        <li>
            <a href="logout.php"
               onclick="return confirm('Are you sure you want to logout?');">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>

    </ul>
<div class="sidebarFooter">

    <div class="footballCircle">

        <i class="fa-solid fa-futbol"></i>

    </div>

    <h3>LIGER ACADEMY</h3>

    <p>Develop • Compete • Win</p>

    <div class="status">

        <span></span>

        System Online

    </div>

</div>
</div>