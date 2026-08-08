<?php

require_once "config/db.php";
require_once "TCPDF/tcpdf.php";

$date = isset($_GET['date'])
        ? $_GET['date']
        : date("Y-m-d");


/* ======================================
   Attendance Summary
====================================== */

$playersPresent = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM attendance
WHERE attendance_date='$date'
"))['total'];

$nonMembersPresent = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM non_member_attendance
WHERE attendance_date='$date'
"))['total'];

$totalAttendance = $playersPresent + $nonMembersPresent;


/* ======================================
   Membership Status
====================================== */

$active = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM subscriptions
WHERE is_current=1
AND end_date>=CURDATE()
"))['total'];

$expired = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM subscriptions
WHERE is_current=1
AND end_date<CURDATE()
"))['total'];

$trial = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM players p
LEFT JOIN subscriptions s
ON p.player_id=s.player_id
AND s.is_current=1
WHERE s.subscription_id IS NULL
AND p.is_active=1
"))['total'];


/* ======================================
   TCPDF
====================================== */

$pdf = new TCPDF(
    'P',
    'mm',
    'A4',
    true,
    'UTF-8',
    false
);

$pdf->SetCreator("LIGER");
$pdf->SetAuthor("LIGER Football Academy");
$pdf->SetTitle("Daily Attendance Report");

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(15,15,15);

$pdf->SetAutoPageBreak(true,15);

$pdf->AddPage();
$logo="assets/images/liger_logo.png";

if(file_exists($logo)){

    $pdf->Image(
        $logo,
        80,
        10,
        50
    );

}

$pdf->Ln(38);
$pdf->SetFont('helvetica','B',20);

$pdf->SetTextColor(45,24,79);

$pdf->Cell(
0,
8,
'LIGER FOOTBALL ACADEMY',
0,
1,
'C'
);

$pdf->SetFont('helvetica','',13);

$pdf->Cell(
0,
7,
'Daily Attendance Report',
0,
1,
'C'
);

$pdf->SetFont('helvetica','',11);

$pdf->SetTextColor(120);

$pdf->Cell(
0,
6,
date("l, d F Y",strtotime($date)),
0,
1,
'C'
);

$pdf->Ln(8);

$pdf->SetDrawColor(91,58,158);

$pdf->SetLineWidth(.6);

$pdf->Line(
15,
$pdf->GetY(),
195,
$pdf->GetY()
);

$pdf->Ln(8);
function sectionTitle($pdf,$title){

    $pdf->SetFillColor(91,58,158);

    $pdf->SetTextColor(255);

    $pdf->SetFont('helvetica','B',12);

    $pdf->Cell(
        180,
        9,
        $title,
        0,
        1,
        'L',
        true
    );

    $pdf->Ln(2);

    $pdf->SetTextColor(0);

}

/* ======================================
   ATTENDANCE SUMMARY
====================================== */

sectionTitle($pdf," ATTENDANCE SUMMARY");

$pdf->SetFont('helvetica','B',11);

/* Header */

$pdf->SetFillColor(91,58,158);
$pdf->SetTextColor(255);

$pdf->Cell(140,10,'Category',1,0,'C',true);
$pdf->Cell(40,10,'Count',1,1,'C',true);

$pdf->SetFont('helvetica','',11);
$pdf->SetTextColor(0);
/* Registered Players */

$pdf->Cell(140,10,'Registered Players Present',1);

$pdf->SetFillColor(76,175,80);
$pdf->SetTextColor(255);

$pdf->Cell(40,10,$playersPresent,1,1,'C',true);

$pdf->SetTextColor(0);


/* Non Members */

$pdf->Cell(140,10,'Non-Members Present',1);

$pdf->SetFillColor(33,150,243);
$pdf->SetTextColor(255);

$pdf->Cell(40,10,$nonMembersPresent,1,1,'C',true);

$pdf->SetTextColor(0);


/* Total */

$pdf->Cell(140,10,'Total Attendance',1);

$pdf->SetFillColor(91,58,158);
$pdf->SetTextColor(255);

$pdf->Cell(40,10,$totalAttendance,1,1,'C',true);

$pdf->SetTextColor(0);

$pdf->Ln(10);

/* ======================================
   MEMBERSHIP STATUS
====================================== */

sectionTitle($pdf,"MEMBERSHIP STATUS");

$pdf->SetFont('helvetica','B',11);

/* Header */

$pdf->SetFillColor(91,58,158);
$pdf->SetTextColor(255);

$pdf->Cell(140,10,'Membership Type',1,0,'C',true);
$pdf->Cell(40,10,'Count',1,1,'C',true);

$pdf->SetFont('helvetica','',11);
$pdf->SetTextColor(0);


/* Active */

$pdf->Cell(140,10,'Active Membership',1);

$pdf->SetFillColor(76,175,80);
$pdf->SetTextColor(255);

$pdf->SetFont('helvetica','B',12);

$pdf->SetFont('helvetica','B',12);

$pdf->Cell(40,10,$active,1,1,'C',true);

$pdf->SetFont('helvetica','',11);

$pdf->SetFont('helvetica','',11);

$pdf->SetTextColor(0);


/* Expired */

$pdf->Cell(140,10,'Expired Membership',1);

$pdf->SetFillColor(231,76,60);
$pdf->SetTextColor(255);

$pdf->Cell(
40,
10,
$expired,
1,
1,
'C',
true
);

$pdf->SetTextColor(0);


/* Trial */

$pdf->Cell(140,10,'Trial Membership',1);

$pdf->SetFillColor(241,196,15);
$pdf->SetTextColor(0);

$pdf->Cell(
40,
10,
$trial,
1,
1,
'C',
true
);

$pdf->Ln(10);
/* ======================================
   REGISTERED PLAYERS
====================================== */

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

END AS membership

FROM attendance a

INNER JOIN players p
ON a.player_id = p.player_id

LEFT JOIN subscriptions s
ON p.player_id = s.player_id
AND s.is_current = 1

WHERE a.attendance_date='$date'

ORDER BY p.player_code

");

sectionTitle($pdf,"REGISTERED PLAYERS");


$pdf->SetFillColor(91,58,158);
$pdf->SetTextColor(255);
$pdf->SetFont('helvetica','B',11);

$pdf->Cell(30,10,'Code',1,0,'C',true);
$pdf->Cell(105,10,'Player Name',1,0,'C',true);
$pdf->Cell(45,10,'Membership',1,1,'C',true);


$pdf->SetFont('helvetica','',10);
$pdf->SetTextColor(0);

$fill=false;

while($row=mysqli_fetch_assoc($players)){

    // Alternate row color

    if($fill){

        $pdf->SetFillColor(247,247,247);

    }else{

        $pdf->SetFillColor(255,255,255);

    }

    $pdf->Cell(
        30,
        9,
        $row['player_code'],
        1,
        0,
        'C',
        true
    );

    $pdf->Cell(
        105,
        9,
        trim($row['player_name']),
        1,
        0,
        'L',
        true
    );

    // Membership Badge

    switch($row['membership']){

        case "Active":

            $pdf->SetFillColor(76,175,80);
            $pdf->SetTextColor(255);

        break;

        case "Expired":

            $pdf->SetFillColor(231,76,60);
            $pdf->SetTextColor(255);

        break;

        default:

            $pdf->SetFillColor(255,193,7);
            $pdf->SetTextColor(0);

    }

    $pdf->SetFont('helvetica','B',9);

    $pdf->Cell(
        45,
        9,
        strtoupper($row['membership']),
        1,
        1,
        'C',
        true
    );

    $pdf->SetFont('helvetica','',10);
    $pdf->SetTextColor(0);

    $fill=!$fill;

}

$pdf->Ln(10);

/* ======================================
   NON-MEMBERS PRESENT
====================================== */

$nonMembers = mysqli_query($conn, "

SELECT full_name

FROM non_member_attendance n

INNER JOIN non_members m
ON n.non_member_id = m.non_member_id

WHERE attendance_date='$date'

ORDER BY full_name

");

sectionTitle($pdf,"NON-MEMBERS PRESENT");


$pdf->SetFillColor(33,150,243);
$pdf->SetTextColor(255);
$pdf->SetFont('helvetica','B',11);

$pdf->Cell(
180,
10,
'Name',
1,
1,
'C',
true
);

$pdf->SetFont('helvetica','',10);
$pdf->SetTextColor(0);

$fill=false;

while($row=mysqli_fetch_assoc($nonMembers)){

    if($fill){

        $pdf->SetFillColor(247,247,247);

    }else{

        $pdf->SetFillColor(255,255,255);

    }

    $pdf->Cell(
        180,
        9,
        strtoupper(trim($row['full_name'])),
        1,
        1,
        'L',
        true
    );

    $fill=!$fill;

}

$pdf->Ln(10);
/* ======================================
   LEGEND
====================================== */

sectionTitle($pdf,"REPORT LEGEND");

$pdf->SetFont('helvetica','',10);


/* Active */

$pdf->SetFillColor(76,175,80);
$pdf->Cell(12,8,'',1,0,'C',true);

$pdf->SetFillColor(255,255,255);
$pdf->Cell(168,8,'Active Membership',1,1);


/* Expired */

$pdf->SetFillColor(231,76,60);
$pdf->Cell(12,8,'',1,0,'C',true);

$pdf->SetFillColor(255,255,255);
$pdf->Cell(168,8,'Expired Membership',1,1);


/* Trial */

$pdf->SetFillColor(241,196,15);
$pdf->Cell(12,8,'',1,0,'C',true);

$pdf->SetFillColor(255,255,255);
$pdf->Cell(168,8,'Trial Membership',1,1);


/* Non Member */

$pdf->SetFillColor(33,150,243);
$pdf->Cell(12,8,'',1,0,'C',true);

$pdf->SetFillColor(255,255,255);
$pdf->Cell(168,8,'Non-Member Attendance',1,1);

$pdf->Ln(12);
/* ======================================
   FOOTER
====================================== */

$pdf->SetDrawColor(91,58,158);

$pdf->Line(
15,
$pdf->GetY(),
195,
$pdf->GetY()
);

$pdf->Ln(5);

$pdf->SetFont('helvetica','B',11);
$pdf->SetTextColor(91,58,158);

$pdf->Cell(
0,
6,
'LIGER FOOTBALL ACADEMY',
0,
1,
'C'
);

$pdf->SetFont('helvetica','',9);
$pdf->SetTextColor(120);

$pdf->Cell(
0,
5,
'Daily Attendance Report',
0,
1,
'C'
);

$pdf->Cell(
0,
5,
'Generated on '.date("l, d F Y"),
0,
1,
'C'
);

$pdf->Output(
'LIGER_Daily_Attendance_'.$date.'.pdf',
'I'
);

