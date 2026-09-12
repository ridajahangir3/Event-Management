<?php

if (!isset($page_title)) {
    $page_title = "Event Management System";
}

if (!isset($active_page)) {
    $active_page = "";
}

/* =========================
   UNREAD NOTIFICATION COUNT
========================= */

$unreadNotifications = 0;

if (isset($conn)) {

    $tableCheck = $conn->query(
        "SHOW TABLES LIKE 'notifications'"
    );

    if ($tableCheck && $tableCheck->num_rows > 0) {

        $countResult = $conn->query(
            "SELECT COUNT(*) AS total
             FROM notifications
             WHERE is_read = 0"
        );

        if ($countResult) {

            $countRow = $countResult->fetch_assoc();

            $unreadNotifications =
                (int)($countRow["total"] ?? 0);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($page_title) ?>
        - Event Management System
    </title>

    <link
        rel="stylesheet"
        href="style.css"
    >

    <style>

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: sticky !important;
            top: 0 !important;
            z-index: 9999 !important;

            pointer-events: auto !important;
        }

        .sidebar a {
            position: relative !important;
            z-index: 10000 !important;

            display: flex !important;

            pointer-events: auto !important;
            cursor: pointer !important;
        }

        .sidebar a::before,
        .sidebar a::after {
            pointer-events: none !important;
        }

        /* =========================
           NOTIFICATIONS
        ========================= */

        .notification-menu-link {
            display: flex !important;
            align-items: center;
            gap: 8px;

            position: relative;
            z-index: 10001 !important;
        }

        .notification-badge {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            min-width: 20px;
            height: 20px;

            padding: 0 6px;
            margin-left: auto;

            border-radius: 999px;

            background: #ef4444;
            color: #fff;

            font-size: 11px;
            font-weight: 800;
            line-height: 1;

            pointer-events: none;

            box-shadow:
                0 3px 8px
                rgba(239, 68, 68, .30);
        }

        /* =========================
           PROFILE
        ========================= */

        .profile-menu-link {
            display: flex !important;
            align-items: center;
            gap: 8px;

            position: relative;
            z-index: 10001 !important;

            pointer-events: auto !important;
        }

        /* =========================
           SETTINGS
        ========================= */

        .settings-menu-link {
            display: flex !important;
            align-items: center;
            gap: 8px;

            position: relative !important;
            z-index: 10002 !important;

            pointer-events: auto !important;
            cursor: pointer !important;
        }

        .settings-menu-link * {
            pointer-events: none !important;
        }

        .settings-menu-link:hover {
            color: #fff !important;

            background:
                rgba(255, 255, 255, .07) !important;

            transform: translateX(3px);
        }

        .settings-menu-link.active {
            color: #fff !important;

            background:
                linear-gradient(
                    135deg,
                    #7038f4,
                    #5d2be7
                ) !important;

            box-shadow:
                0 12px 28px
                rgba(100, 45, 235, .32);
        }

        /* =========================
           DISABLE SIDEBAR OVERLAYS
        ========================= */

        .sidebar::before,
        .sidebar::after {
            pointer-events: none !important;
        }

    </style>

</head>

<body>

<div class="container">

    <aside class="sidebar">

        <h2 class="logo">
            Event Manager
        </h2>


        <!-- DASHBOARD -->

        <a
            href="dashboard.php"
            class="<?= $active_page == 'dashboard' ? 'active' : '' ?>"
        >
            🏠 Dashboard
        </a>


        <!-- EVENTS -->

        <a
            href="events.php"
            class="<?= $active_page == 'events' ? 'active' : '' ?>"
        >
            🗓️ Manage Events
        </a>


        <!-- USERS -->

        <a
            href="users.php"
            class="<?= $active_page == 'users' ? 'active' : '' ?>"
        >
            👥 Manage Users
        </a>


        <!-- REGISTRATIONS -->

        <a
            href="register_event.php"
            class="<?= $active_page == 'registrations' ? 'active' : '' ?>"
        >
            📝 Registrations
        </a>


        <!-- ATTENDANCE -->

        <a
            href="attendance.php"
            class="<?= $active_page == 'attendance' ? 'active' : '' ?>"
        >
            ✅ Attendance
        </a>


        <!-- NOTIFICATIONS -->

        <a
            href="notifications.php"
            class="notification-menu-link <?= $active_page == 'notifications' ? 'active' : '' ?>"
        >

            🔔 Notifications

            <?php if ($unreadNotifications > 0): ?>

                <span class="notification-badge">

                    <?= $unreadNotifications > 99
                        ? '99+'
                        : $unreadNotifications
                    ?>

                </span>

            <?php endif; ?>

        </a>


        <!-- PROFILE -->

        <a
            href="profile.php"
            class="profile-menu-link <?= $active_page == 'profile' ? 'active' : '' ?>"
        >
            👤 User Profile
        </a>


        <!-- SETTINGS -->

        <a
            href="settings.php"
            class="settings-menu-link <?= $active_page == 'settings' ? 'active' : '' ?>"
        >
            ⚙️ Settings
        </a>

    </aside>


    <main class="main">