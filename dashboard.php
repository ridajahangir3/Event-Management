<?php

require_once "db.php";

$page_title = "Dashboard";
$active_page = "dashboard";


/* =====================================================
   DASHBOARD DATA
===================================================== */

$totalEvents = 0;
$totalUsers = 0;
$totalRegistrations = 0;
$totalAttendance = 0;
$totalPresent = 0;


/* =====================================================
   TOTAL EVENTS
===================================================== */

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM events"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalEvents = (int)$row["total"];
}


/* =====================================================
   TOTAL USERS
===================================================== */

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM users"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalUsers = (int)$row["total"];
}


/* =====================================================
   TOTAL REGISTRATIONS
===================================================== */

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM registrations"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalRegistrations = (int)$row["total"];
}


/* =====================================================
   TOTAL ATTENDANCE
===================================================== */

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM attendance"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalAttendance = (int)$row["total"];
}


/* =====================================================
   PRESENT ATTENDANCE
===================================================== */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM attendance
     WHERE status = 'Present'"
);

if ($result) {
    $row = $result->fetch_assoc();
    $totalPresent = (int)$row["total"];
}


/* =====================================================
   ATTENDANCE PERCENTAGE
===================================================== */

$attendancePercentage = 0;

if ($totalAttendance > 0) {
    $attendancePercentage =
        round(($totalPresent / $totalAttendance) * 100);
}


/* =====================================================
   UPCOMING EVENTS
===================================================== */

$upcomingEvents = $conn->query(
    "SELECT
        id,
        title,
        event_date,
        event_time,
        location
     FROM events
     WHERE event_date >= CURDATE()
     ORDER BY event_date ASC, event_time ASC
     LIMIT 6"
);


/* =====================================================
   RECENT EVENTS
===================================================== */

$recentEvents = $conn->query(
    "SELECT
        id,
        title,
        event_date,
        event_time,
        location
     FROM events
     ORDER BY id DESC
     LIMIT 6"
);


/* =====================================================
   TODAY EVENTS
===================================================== */

$todayEvents = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM events
     WHERE event_date = CURDATE()"
);

if ($result) {
    $row = $result->fetch_assoc();
    $todayEvents = (int)$row["total"];
}


/* =====================================================
   LOAD SOUND & LIGHTING SETTINGS
===================================================== */

$sound_required = 0;
$microphone_required = 0;
$speakers_required = 0;
$microphone_count = 1;
$sound_provider = "";

$lighting_required = 0;
$stage_lighting = 0;
$decorative_lighting = 0;
$emergency_lighting = 0;
$lighting_provider = "";


/* =====================================================
   GET SETTINGS
===================================================== */

$settingsResult = $conn->query(
    "SELECT
        sound_required,
        microphone_required,
        speakers_required,
        microphone_count,
        sound_provider,
        lighting_required,
        stage_lighting,
        decorative_lighting,
        emergency_lighting,
        lighting_provider
     FROM settings
     WHERE id = 1
     LIMIT 1"
);

if ($settingsResult && $settingsResult->num_rows > 0) {

    $settings = $settingsResult->fetch_assoc();

    $sound_required =
        (int)($settings["sound_required"] ?? 0);

    $microphone_required =
        (int)($settings["microphone_required"] ?? 0);

    $speakers_required =
        (int)($settings["speakers_required"] ?? 0);

    $microphone_count =
        (int)($settings["microphone_count"] ?? 1);

    $sound_provider =
        $settings["sound_provider"] ?? "";

    $lighting_required =
        (int)($settings["lighting_required"] ?? 0);

    $stage_lighting =
        (int)($settings["stage_lighting"] ?? 0);

    $decorative_lighting =
        (int)($settings["decorative_lighting"] ?? 0);

    $emergency_lighting =
        (int)($settings["emergency_lighting"] ?? 0);

    $lighting_provider =
        $settings["lighting_provider"] ?? "";
}


/* =====================================================
   DISPLAY HELPERS
===================================================== */

$soundItems = [];

if ($sound_required) {
    $soundItems[] = "🔊 Sound System";
}

if ($microphone_required) {
    $soundItems[] =
        "🎤 Microphones (" . $microphone_count . ")";
}

if ($speakers_required) {
    $soundItems[] = "🔊 Speakers";
}


$lightingItems = [];

if ($lighting_required) {
    $lightingItems[] = "💡 Lighting System";
}

if ($stage_lighting) {
    $lightingItems[] = "🎭 Stage Lighting";
}

if ($decorative_lighting) {
    $lightingItems[] = "✨ Decorative / Fairy Lighting";
}

if ($emergency_lighting) {
    $lightingItems[] = "🚨 Emergency Lighting";
}


require "header.php";

?>


<style>

/* =====================================================
   DASHBOARD
===================================================== */

.dashboard-page {
    padding-bottom: 40px;
}


/* =====================================================
   HERO
===================================================== */

.dashboard-hero {
    background:
        linear-gradient(
            135deg,
            #0f766e,
            #155e75
        );

    border-radius: 24px;

    padding: 32px;

    color: white;

    margin-bottom: 28px;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 18px 45px rgba(15,118,110,.18);
}


.dashboard-hero::after {
    content: "";

    position: absolute;

    width: 220px;
    height: 220px;

    border-radius: 50%;

    background: rgba(255,255,255,.08);

    right: -70px;
    top: -80px;
}


.dashboard-hero::before {
    content: "";

    position: absolute;

    width: 130px;
    height: 130px;

    border-radius: 50%;

    background: rgba(255,255,255,.06);

    right: 100px;
    bottom: -70px;
}


.hero-content {
    position: relative;
    z-index: 2;
}


.hero-label {
    font-size: 12px;

    font-weight: 800;

    letter-spacing: 2px;

    text-transform: uppercase;

    opacity: .75;

    margin-bottom: 10px;
}


.dashboard-hero h1 {
    margin: 0;

    font-size: 34px;

    font-weight: 800;
}


.dashboard-hero p {
    margin: 10px 0 0;

    max-width: 650px;

    color: rgba(255,255,255,.82);

    line-height: 1.6;
}


.hero-buttons {
    margin-top: 22px;

    display: flex;

    gap: 12px;

    flex-wrap: wrap;
}


.hero-btn {
    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 11px 18px;

    border-radius: 12px;

    text-decoration: none;

    font-weight: 700;

    font-size: 14px;

    transition: .2s;
}


.hero-btn:hover {
    transform: translateY(-2px);
}


.hero-primary {
    background: white;

    color: #0f766e;
}


.hero-secondary {
    background: rgba(255,255,255,.13);

    color: white;

    border: 1px solid rgba(255,255,255,.22);
}


/* =====================================================
   STATISTICS
===================================================== */

.dashboard-stats {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0,1fr));

    gap: 18px;

    margin-bottom: 28px;
}


.dashboard-stat {
    background: white;

    border: 1px solid #e8edf2;

    border-radius: 18px;

    padding: 22px;

    box-shadow:
        0 8px 25px rgba(15,23,42,.05);

    transition: .2s;

    position: relative;

    overflow: hidden;
}


.dashboard-stat:hover {
    transform: translateY(-3px);

    box-shadow:
        0 14px 32px rgba(15,23,42,.08);
}


.stat-top {
    display: flex;

    align-items: center;

    justify-content: space-between;
}


.stat-icon {
    width: 48px;

    height: 48px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 22px;
}


.stat-number {
    font-size: 31px;

    font-weight: 800;

    color: #172033;

    margin-top: 16px;
}


.stat-title {
    font-size: 13px;

    font-weight: 700;

    color: #64748b;

    margin-top: 3px;
}


.stat-note {
    margin-top: 13px;

    font-size: 12px;

    color: #94a3b8;
}


.icon-event {
    background: #e6fffb;

    color: #0f766e;
}


.icon-user {
    background: #eaf2ff;

    color: #2563eb;
}


.icon-registration {
    background: #fff4df;

    color: #d97706;
}


.icon-attendance {
    background: #f3e8ff;

    color: #7e22ce;
}


/* =====================================================
   MAIN GRID
===================================================== */

.dashboard-grid {
    display: grid;

    grid-template-columns:
        minmax(0,1.5fr)
        minmax(300px,1fr);

    gap: 22px;

    margin-bottom: 22px;
}


/* =====================================================
   PANEL
===================================================== */

.dashboard-panel {
    background: white;

    border: 1px solid #e8edf2;

    border-radius: 20px;

    padding: 24px;

    box-shadow:
        0 8px 25px rgba(15,23,42,.04);
}


.panel-header {
    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;

    margin-bottom: 20px;
}


.panel-title {
    margin: 0;

    font-size: 19px;

    color: #172033;
}


.panel-subtitle {
    margin: 6px 0 0;

    color: #94a3b8;

    font-size: 13px;
}


.panel-link {
    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

    color: #0f766e;
}


/* =====================================================
   EVENTS
===================================================== */

.event-row {
    display: flex;

    align-items: center;

    gap: 14px;

    padding: 14px 0;

    border-bottom: 1px solid #edf1f5;
}


.event-row:last-child {
    border-bottom: 0;
}


.event-date-box {
    width: 52px;

    min-width: 52px;

    height: 55px;

    border-radius: 13px;

    background: #ecfdf5;

    display: flex;

    flex-direction: column;

    justify-content: center;

    align-items: center;

    color: #0f766e;
}


.event-day {
    font-size: 18px;

    font-weight: 800;

    line-height: 1;
}


.event-month {
    font-size: 9px;

    font-weight: 800;

    text-transform: uppercase;

    margin-top: 4px;
}


.event-info {
    min-width: 0;

    flex: 1;
}


.event-name {
    font-weight: 750;

    color: #172033;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


.event-meta {
    margin-top: 5px;

    font-size: 12px;

    color: #94a3b8;
}


.event-badge {
    padding: 6px 9px;

    border-radius: 20px;

    background: #f0fdf4;

    color: #15803d;

    font-size: 10px;

    font-weight: 800;
}


/* =====================================================
   TIMELINE
===================================================== */

.timeline {
    position: relative;

    padding-left: 24px;
}


.timeline::before {
    content: "";

    position: absolute;

    left: 7px;

    top: 6px;

    bottom: 6px;

    width: 2px;

    background: #dbeafe;
}


.timeline-item {
    position: relative;

    padding-bottom: 21px;
}


.timeline-item:last-child {
    padding-bottom: 0;
}


.timeline-dot {
    position: absolute;

    left: -22px;

    top: 4px;

    width: 11px;

    height: 11px;

    border-radius: 50%;

    background: #0f766e;

    border: 3px solid #ccfbf1;

    box-sizing: content-box;
}


.timeline-title {
    font-weight: 750;

    color: #172033;

    font-size: 14px;
}


.timeline-details {
    margin-top: 6px;

    color: #94a3b8;

    font-size: 12px;

    line-height: 1.7;
}


/* =====================================================
   ATTENDANCE
===================================================== */

.attendance-panel {
    display: grid;

    grid-template-columns: 170px 1fr;

    gap: 28px;

    align-items: center;
}


.attendance-circle {
    width: 150px;

    height: 150px;

    border-radius: 50%;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    margin: auto;

    background:
        conic-gradient(
            #0f766e <?= $attendancePercentage ?>%,
            #e8f0f2 <?= $attendancePercentage ?>%
        );

    position: relative;
}


.attendance-circle::after {
    content: "";

    position: absolute;

    width: 112px;

    height: 112px;

    border-radius: 50%;

    background: white;
}


.attendance-value {
    position: relative;

    z-index: 2;

    font-size: 28px;

    font-weight: 800;

    color: #172033;
}


.attendance-label {
    position: relative;

    z-index: 2;

    font-size: 11px;

    color: #94a3b8;

    margin-top: 3px;
}


.attendance-info h3 {
    margin: 0;

    font-size: 20px;

    color: #172033;
}


.attendance-info p {
    color: #64748b;

    font-size: 13px;

    line-height: 1.6;
}


.progress-line {
    height: 9px;

    background: #edf2f4;

    border-radius: 20px;

    overflow: hidden;

    margin-top: 15px;
}


.progress-fill {
    height: 100%;

    width: <?= $attendancePercentage ?>%;

    background:
        linear-gradient(
            90deg,
            #0f766e,
            #14b8a6
        );

    border-radius: 20px;
}


.attendance-stats {
    display: flex;

    gap: 28px;

    margin-top: 14px;
}


.attendance-stat strong {
    display: block;

    font-size: 19px;

    color: #172033;
}


.attendance-stat span {
    font-size: 11px;

    color: #94a3b8;
}


/* =====================================================
   SOUND & LIGHTING
===================================================== */

.requirements-grid {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 22px;

    margin-bottom: 22px;
}


.requirement-card {
    background: white;

    border: 1px solid #e8edf2;

    border-radius: 20px;

    padding: 24px;

    box-shadow:
        0 8px 25px rgba(15,23,42,.04);
}


.requirement-header {
    display: flex;

    align-items: center;

    gap: 13px;

    margin-bottom: 18px;
}


.requirement-icon {
    width: 48px;

    height: 48px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 23px;
}


.sound-icon {
    background: #fff4df;

    color: #d97706;
}


.light-icon {
    background: #fffbea;

    color: #ca8a04;
}


.requirement-header h2 {
    margin: 0;

    font-size: 19px;

    color: #172033;
}


.requirement-status {
    margin-left: auto;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 10px;

    font-weight: 800;

    text-transform: uppercase;
}


.status-required {
    background: #ecfdf3;

    color: #15803d;
}


.status-not-required {
    background: #f1f5f9;

    color: #64748b;
}


.requirement-list {
    display: flex;

    flex-direction: column;

    gap: 10px;
}


.requirement-item {
    display: flex;

    align-items: center;

    gap: 10px;

    padding: 11px 13px;

    background: #f8fafc;

    border-radius: 11px;

    font-size: 13px;

    color: #334155;

    font-weight: 600;
}


.provider-box {
    margin-top: 15px;

    padding: 12px 14px;

    border-radius: 11px;

    background: #f0fdfa;

    color: #0f766e;

    font-size: 12px;

    font-weight: 600;
}


.no-requirements {
    padding: 15px;

    border-radius: 11px;

    background: #f8fafc;

    color: #94a3b8;

    font-size: 13px;

    text-align: center;
}


/* =====================================================
   QUICK ACTIONS
===================================================== */

.quick-actions {
    display: grid;

    grid-template-columns:
        repeat(3,1fr);

    gap: 14px;
}


.quick-action {
    padding: 18px;

    border-radius: 15px;

    border: 1px solid #e8edf2;

    background: #fafcfd;

    text-decoration: none;

    color: #172033;

    transition: .2s;
}


.quick-action:hover {
    transform: translateY(-3px);

    border-color: #b8e5df;

    background: #f5fffd;
}


.quick-icon {
    font-size: 22px;

    margin-bottom: 10px;
}


.quick-title {
    font-weight: 750;

    font-size: 14px;
}


.quick-text {
    color: #94a3b8;

    font-size: 11px;

    margin-top: 4px;
}


/* =====================================================
   EMPTY
===================================================== */

.empty-dashboard {
    text-align: center;

    padding: 30px 10px;

    color: #94a3b8;

    font-size: 13px;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:1100px) {

    .dashboard-stats {
        grid-template-columns:
            repeat(2,1fr);
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .requirements-grid {
        grid-template-columns: 1fr;
    }
}


@media(max-width:700px) {

    .dashboard-hero {
        padding: 25px;
    }

    .dashboard-hero h1 {
        font-size: 27px;
    }

    .dashboard-stats {
        grid-template-columns: 1fr;
    }

    .attendance-panel {
        grid-template-columns: 1fr;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }

    .event-badge {
        display: none;
    }

    .requirements-grid {
        grid-template-columns: 1fr;
    }

    .requirement-status {
        font-size: 9px;
    }
}

</style>


<div class="dashboard-page">


<!-- =====================================================
     HERO
===================================================== -->

<section class="dashboard-hero">

    <div class="hero-content">

        <div class="hero-label">
            College Event Hub
        </div>

        <h1>
            Welcome back, Admin 👋
        </h1>

        <p>
            Keep your college events organized, monitor registrations,
            track attendance, and manage event requirements from one place.
        </p>

        <div class="hero-buttons">

            <a
                href="add_event.php"
                class="hero-btn hero-primary"
            >
                ＋ Create New Event
            </a>

            <a
                href="events.php"
                class="hero-btn hero-secondary"
            >
                View All Events →
            </a>

        </div>

    </div>

</section>


<!-- =====================================================
     STATISTICS
===================================================== -->

<section class="dashboard-stats">


    <div class="dashboard-stat">

        <div class="stat-top">

            <div class="stat-title">
                TOTAL EVENTS
            </div>

            <div class="stat-icon icon-event">
                📅
            </div>

        </div>

        <div class="stat-number">
            <?= $totalEvents ?>
        </div>

        <div class="stat-note">
            <?= $todayEvents ?> happening today
        </div>

    </div>


    <div class="dashboard-stat">

        <div class="stat-top">

            <div class="stat-title">
                TOTAL USERS
            </div>

            <div class="stat-icon icon-user">
                👥
            </div>

        </div>

        <div class="stat-number">
            <?= $totalUsers ?>
        </div>

        <div class="stat-note">
            Registered system users
        </div>

    </div>


    <div class="dashboard-stat">

        <div class="stat-top">

            <div class="stat-title">
                REGISTRATIONS
            </div>

            <div class="stat-icon icon-registration">
                🎟️
            </div>

        </div>

        <div class="stat-number">
            <?= $totalRegistrations ?>
        </div>

        <div class="stat-note">
            Event registrations
        </div>

    </div>


    <div class="dashboard-stat">

        <div class="stat-top">

            <div class="stat-title">
                ATTENDANCE
            </div>

            <div class="stat-icon icon-attendance">
                📊
            </div>

        </div>

        <div class="stat-number">
            <?= $attendancePercentage ?>%
        </div>

        <div class="stat-note">
            Overall attendance rate
        </div>

    </div>


</section>


<!-- =====================================================
     RECENT + UPCOMING
===================================================== -->

<section class="dashboard-grid">


    <!-- RECENT EVENTS -->

    <div class="dashboard-panel">

        <div class="panel-header">

            <div>

                <h2 class="panel-title">
                    Recent Events
                </h2>

                <p class="panel-subtitle">
                    Latest events added to the system.
                </p>

            </div>

            <a
                href="events.php"
                class="panel-link"
            >
                View all
            </a>

        </div>


        <?php if ($recentEvents && $recentEvents->num_rows > 0): ?>


            <?php while ($event = $recentEvents->fetch_assoc()): ?>

                <?php

                $timestamp =
                    strtotime($event["event_date"]);

                $day =
                    date("d", $timestamp);

                $month =
                    date("M", $timestamp);

                $eventDate =
                    $event["event_date"];

                $today =
                    date("Y-m-d");

                if ($eventDate > $today) {

                    $badge = "Upcoming";

                } elseif ($eventDate === $today) {

                    $badge = "Today";

                } else {

                    $badge = "Completed";
                }

                ?>


                <div class="event-row">


                    <div class="event-date-box">

                        <div class="event-day">
                            <?= htmlspecialchars($day) ?>
                        </div>

                        <div class="event-month">
                            <?= htmlspecialchars($month) ?>
                        </div>

                    </div>


                    <div class="event-info">

                        <div class="event-name">

                            <?= htmlspecialchars(
                                $event["title"]
                            ) ?>

                        </div>


                        <div class="event-meta">

                            🕐
                            <?= htmlspecialchars(
                                $event["event_time"]
                            ) ?>

                            &nbsp;&nbsp;

                            📍
                            <?= htmlspecialchars(
                                $event["location"]
                            ) ?>

                        </div>

                    </div>


                    <span class="event-badge">
                        <?= $badge ?>
                    </span>

                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="empty-dashboard">
                No events available yet.
            </div>


        <?php endif; ?>

    </div>


    <!-- UPCOMING -->

    <div class="dashboard-panel">

        <div class="panel-header">

            <div>

                <h2 class="panel-title">
                    Upcoming Schedule
                </h2>

                <p class="panel-subtitle">
                    Your next scheduled events.
                </p>

            </div>

        </div>


        <?php if ($upcomingEvents && $upcomingEvents->num_rows > 0): ?>


            <div class="timeline">


                <?php while ($event = $upcomingEvents->fetch_assoc()): ?>


                    <div class="timeline-item">

                        <div class="timeline-dot"></div>


                        <div class="timeline-title">

                            <?= htmlspecialchars(
                                $event["title"]
                            ) ?>

                        </div>


                        <div class="timeline-details">

                            📅
                            <?= htmlspecialchars(
                                $event["event_date"]
                            ) ?>

                            <br>

                            🕐
                            <?= htmlspecialchars(
                                $event["event_time"]
                            ) ?>

                            <br>

                            📍
                            <?= htmlspecialchars(
                                $event["location"]
                            ) ?>

                        </div>

                    </div>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <div class="empty-dashboard">
                No upcoming events.
            </div>


        <?php endif; ?>

    </div>


</section>


<!-- =====================================================
     SOUND & LIGHTING OVERVIEW
===================================================== -->

<section class="requirements-grid">


    <!-- SOUND -->

    <div class="requirement-card">

        <div class="requirement-header">

            <div class="requirement-icon sound-icon">
                🔊
            </div>

            <div>
                <h2>
                    Sound System
                </h2>
            </div>

            <span class="requirement-status
                <?= $sound_required
                    ? 'status-required'
                    : 'status-not-required'
                ?>"
            >
                <?= $sound_required
                    ? "Required"
                    : "Not Required"
                ?>
            </span>

        </div>


        <?php if (count($soundItems) > 0): ?>


            <div class="requirement-list">

                <?php foreach ($soundItems as $item): ?>

                    <div class="requirement-item">

                        <?= htmlspecialchars($item) ?>

                    </div>

                <?php endforeach; ?>

            </div>


            <?php if ($sound_provider !== ""): ?>

                <div class="provider-box">

                    🏢 Provider / Details:
                    <?= htmlspecialchars($sound_provider) ?>

                </div>

            <?php endif; ?>


        <?php else: ?>


            <div class="no-requirements">

                No sound equipment selected.

            </div>


        <?php endif; ?>

    </div>


    <!-- LIGHTING -->

    <div class="requirement-card">

        <div class="requirement-header">

            <div class="requirement-icon light-icon">
                💡
            </div>

            <div>
                <h2>
                    Lighting System
                </h2>
            </div>

            <span class="requirement-status
                <?= $lighting_required
                    ? 'status-required'
                    : 'status-not-required'
                ?>"
            >
                <?= $lighting_required
                    ? "Required"
                    : "Not Required"
                ?>
            </span>

        </div>


        <?php if (count($lightingItems) > 0): ?>


            <div class="requirement-list">

                <?php foreach ($lightingItems as $item): ?>

                    <div class="requirement-item">

                        <?= htmlspecialchars($item) ?>

                    </div>

                <?php endforeach; ?>

            </div>


            <?php if ($lighting_provider !== ""): ?>

                <div class="provider-box">

                    🏢 Provider / Details:
                    <?= htmlspecialchars($lighting_provider) ?>

                </div>

            <?php endif; ?>


        <?php else: ?>


            <div class="no-requirements">

                No lighting equipment selected.

            </div>


        <?php endif; ?>

    </div>


</section>


<!-- =====================================================
     ATTENDANCE
===================================================== -->

<section
    class="dashboard-panel"
    style="margin-bottom:22px;"
>

    <div class="panel-header">

        <div>

            <h2 class="panel-title">
                Attendance Overview
            </h2>

            <p class="panel-subtitle">
                Overall participation across registered events.
            </p>

        </div>

        <a
            href="attendance.php"
            class="panel-link"
        >
            Manage attendance
        </a>

    </div>


    <div class="attendance-panel">


        <div class="attendance-circle">

            <div class="attendance-value">
                <?= $attendancePercentage ?>%
            </div>

            <div class="attendance-label">
                Attendance
            </div>

        </div>


        <div class="attendance-info">

            <h3>

                <?= $totalPresent ?>

                <span style="
                    font-size:14px;
                    color:#94a3b8;
                    font-weight:500;
                ">

                    participants present

                </span>

            </h3>


            <p>

                Attendance is calculated from the records
                currently stored in the system.

            </p>


            <div class="progress-line">

                <div class="progress-fill"></div>

            </div>


            <div class="attendance-stats">


                <div class="attendance-stat">

                    <strong>
                        <?= $totalPresent ?>
                    </strong>

                    <span>
                        Present
                    </span>

                </div>


                <div class="attendance-stat">

                    <strong>
                        <?= $totalAttendance ?>
                    </strong>

                    <span>
                        Attendance Records
                    </span>

                </div>


            </div>

        </div>

    </div>

</section>


<!-- =====================================================
     QUICK ACTIONS
===================================================== -->

<section class="dashboard-panel">

    <div class="panel-header">

        <div>

            <h2 class="panel-title">
                Quick Actions
            </h2>

            <p class="panel-subtitle">
                Frequently used management options.
            </p>

        </div>

    </div>


    <div class="quick-actions">


        <a
            href="add_event.php"
            class="quick-action"
        >

            <div class="quick-icon">
                ✨
            </div>

            <div class="quick-title">
                Create Event
            </div>

            <div class="quick-text">
                Add a new college event
            </div>

        </a>


        <a
            href="events.php"
            class="quick-action"
        >

            <div class="quick-icon">
                📅
            </div>

            <div class="quick-title">
                Manage Events
            </div>

            <div class="quick-text">
                View and organize events
            </div>

        </a>


        <a
            href="attendance.php"
            class="quick-action"
        >

            <div class="quick-icon">
                📊
            </div>

            <div class="quick-title">
                Attendance
            </div>

            <div class="quick-text">
                Track participant attendance
            </div>

        </a>


        <a
            href="users.php"
            class="quick-action"
        >

            <div class="quick-icon">
                👥
            </div>

            <div class="quick-title">
                Users
            </div>

            <div class="quick-text">
                Manage registered users
            </div>

        </a>


        <a
            href="register_event.php"
            class="quick-action"
        >

            <div class="quick-icon">
                🎟️
            </div>

            <div class="quick-title">
                Registrations
            </div>

            <div class="quick-text">
                View event registrations
            </div>

        </a>


        <a
            href="settings.php"
            class="quick-action"
        >

            <div class="quick-icon">
                ⚙️
            </div>

            <div class="quick-title">
                Settings
            </div>

            <div class="quick-text">
                Manage sound and lighting
            </div>

        </a>


    </div>

</section>


</div>


<?php

require "footer.php";

?>