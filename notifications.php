```php
<?php

require_once "db.php";

$page_title = "Notifications";
$active_page = "notifications";

$message = "";
$type = "success";


/* =========================
   MARK ALL AS READ
========================= */

if (isset($_POST["mark_all_read"])) {

    $stmt = $conn->prepare(
        "UPDATE notifications
         SET is_read = 1
         WHERE is_read = 0"
    );

    if ($stmt && $stmt->execute()) {

        $message = "All notifications marked as read.";

    } else {

        $message = "Could not update notifications.";
        $type = "danger";
    }

    if ($stmt) {
        $stmt->close();
    }
}


/* =========================
   MARK SINGLE AS READ
========================= */

if (isset($_GET["read"])) {

    $notification_id = (int)$_GET["read"];

    if ($notification_id > 0) {

        $stmt = $conn->prepare(
            "UPDATE notifications
             SET is_read = 1
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param(
                "i",
                $notification_id
            );

            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: notifications.php");
    exit;
}


/* =========================
   DELETE
========================= */

if (isset($_GET["delete"])) {

    $notification_id = (int)$_GET["delete"];

    if ($notification_id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM notifications
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param(
                "i",
                $notification_id
            );

            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: notifications.php");
    exit;
}


/* =========================
   FILTER
========================= */

$filter = $_GET["filter"] ?? "all";

$where = "";

$allowedFilters = [
    "event",
    "registration",
    "attendance",
    "feedback",
    "system"
];

if ($filter === "unread") {

    $where = "WHERE is_read = 0";

} elseif (in_array($filter, $allowedFilters, true)) {

    $safeFilter = $conn->real_escape_string($filter);

    $where = "WHERE type = '$safeFilter'";
}


/* =========================
   GET NOTIFICATIONS
========================= */

$sql = "
    SELECT
        id,
        user_id,
        title,
        message,
        type,
        is_read,
        created_at
    FROM notifications
    $where
    ORDER BY created_at DESC
";

$result = $conn->query($sql);


/* =========================
   TOTAL
========================= */

$totalNotifications = 0;

$totalResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM notifications"
);

if ($totalResult) {

    $row = $totalResult->fetch_assoc();

    $totalNotifications =
        (int)($row["total"] ?? 0);
}


/* =========================
   UNREAD
========================= */

$unreadNotifications = 0;

$unreadResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM notifications
     WHERE is_read = 0"
);

if ($unreadResult) {

    $row = $unreadResult->fetch_assoc();

    $unreadNotifications =
        (int)($row["total"] ?? 0);
}


/* =========================
   HEADER
========================= */

require "header.php";

?>

<style>

.notifications-page {
    max-width: 1150px;
    margin: 0 auto;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.notification-header h1 {
    margin: 0;
}

.notification-header p {
    margin-top: 6px;
    color: #718096;
}

.notification-summary {
    display: flex;
    gap: 15px;
    margin-bottom: 22px;
}

.notification-stat {
    flex: 1;
    padding: 20px;
    border-radius: 16px;
    background: linear-gradient(
        145deg,
        #ffffff,
        #f5f7fb
    );
    border: 1px solid #edf0f5;
    box-shadow:
        0 10px 25px rgba(31,41,55,.08);
}

.notification-stat small {
    color: #718096;
    font-weight: 600;
}

.notification-stat strong {
    display: block;
    margin-top: 6px;
    font-size: 28px;
}

.notification-toolbar {
    margin-bottom: 20px;
}

.notification-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.notification-filter {
    text-decoration: none;
    padding: 9px 15px;
    border-radius: 10px;
    background: #f1f3f7;
    color: #4a5568;
    font-size: 13px;
    font-weight: 700;
}

.notification-filter.active {
    background: #5b35c5;
    color: white;
}

.notification-list {
    display: grid;
    gap: 14px;
}

.notification-item {
    display: flex;
    gap: 15px;
    align-items: flex-start;
    padding: 20px;
    border-radius: 18px;
    background: white;
    border: 1px solid #edf0f5;
    box-shadow:
        0 8px 22px rgba(31,41,55,.07);
    transition: .2s;
}

.notification-item:hover {
    transform: translateY(-3px);
}

.notification-item.unread {
    border-left: 5px solid #6d3fd4;
    background: linear-gradient(
        100deg,
        #faf8ff,
        #ffffff
    );
}

.notification-icon {
    width: 48px;
    min-width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0ebff;
    font-size: 22px;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-size: 16px;
    font-weight: 800;
    color: #1f2937;
}

.notification-message {
    margin-top: 6px;
    color: #667085;
    line-height: 1.5;
}

.notification-time {
    margin-top: 8px;
    color: #98a2b3;
    font-size: 12px;
}

.notification-type {
    display: inline-block;
    margin-left: 8px;
    padding: 4px 8px;
    border-radius: 7px;
    background: #eee8ff;
    color: #5b35c5;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}

.notification-actions {
    display: flex;
    gap: 7px;
}

.notification-actions a {
    text-decoration: none;
    padding: 7px 10px;
    border-radius: 8px;
    background: #f4f5f7;
    color: #4a5568;
    font-size: 12px;
    font-weight: 700;
}

.notification-actions a:hover {
    background: #e9e5ff;
    color: #5b35c5;
}

.unread-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #6d3fd4;
    margin-top: 7px;
}

.notifications-empty {
    text-align: center;
    padding: 70px 20px;
    border-radius: 20px;
    background: white;
    border: 1px solid #edf0f5;
    box-shadow:
        0 12px 30px rgba(31,41,55,.07);
}

.notifications-empty-icon {
    font-size: 55px;
    margin-bottom: 15px;
}

.notifications-empty p {
    color: #718096;
}

@media(max-width:700px) {

    .notification-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .notification-summary {
        flex-direction: column;
    }

    .notification-item {
        flex-direction: column;
    }

}

</style>


<div class="notifications-page">


    <div class="notification-header">

        <div>

            <h1>🔔 Notifications</h1>

            <p>
                Stay updated with events, registrations
                and activity.
            </p>

        </div>


        <form method="POST">

            <button
                type="submit"
                name="mark_all_read"
                class="btn"
            >
                ✓ Mark All as Read
            </button>

        </form>

    </div>


    <?php if ($message): ?>

        <div
            class="alert alert-<?= htmlspecialchars($type) ?>"
            style="
                margin-bottom:20px;
                padding:14px 18px;
                border-radius:12px;
                font-weight:600;
            "
        >
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <div class="notification-summary">

        <div class="notification-stat">

            <small>
                Total Notifications
            </small>

            <strong>
                <?= $totalNotifications ?>
            </strong>

        </div>


        <div class="notification-stat">

            <small>
                Unread
            </small>

            <strong>
                <?= $unreadNotifications ?>
            </strong>

        </div>

    </div>


    <div class="notification-toolbar">

        <div class="notification-filters">

            <?php

            $filters = [

                "all" => "All",
                "unread" => "Unread",
                "event" => "Events",
                "registration" => "Registrations",
                "attendance" => "Attendance",
                "feedback" => "Feedback",
                "system" => "System"

            ];

            foreach ($filters as $key => $label):

            ?>

                <a
                    href="notifications.php?filter=<?= urlencode($key) ?>"
                    class="
                        notification-filter
                        <?= $filter === $key ? "active" : "" ?>
                    "
                >
                    <?= htmlspecialchars($label) ?>
                </a>

            <?php endforeach; ?>

        </div>

    </div>


    <?php if ($result && $result->num_rows > 0): ?>


        <div class="notification-list">


            <?php while ($notification = $result->fetch_assoc()): ?>

                <?php

                $notificationType =
                    strtolower(
                        $notification["type"] ?? "general"
                    );


                $icons = [

                    "event" => "🆕",
                    "registration" => "🎟️",
                    "attendance" => "📋",
                    "feedback" => "⭐",
                    "system" => "⚙️",
                    "general" => "🔔"

                ];


                $icon =
                    $icons[$notificationType]
                    ?? "🔔";


                $createdAt =
                    strtotime(
                        $notification["created_at"]
                    );


                $timeText =
                    $createdAt
                    ? date(
                        "d M Y, h:i A",
                        $createdAt
                    )
                    : "";


                $isUnread =
                    (int)$notification["is_read"] === 0;

                ?>


                <div
                    class="
                        notification-item
                        <?= $isUnread ? "unread" : "" ?>
                    "
                >


                    <div class="notification-icon">

                        <?= $icon ?>

                    </div>


                    <div class="notification-content">


                        <div class="notification-title">

                            <?= htmlspecialchars(
                                $notification["title"]
                            ) ?>


                            <span class="notification-type">

                                <?= htmlspecialchars(
                                    ucfirst(
                                        $notificationType
                                    )
                                ) ?>

                            </span>

                        </div>


                        <div class="notification-message">

                            <?= nl2br(
                                htmlspecialchars(
                                    $notification["message"]
                                )
                            ) ?>

                        </div>


                        <div class="notification-time">

                            🕐
                            <?= htmlspecialchars($timeText) ?>

                        </div>


                    </div>


                    <?php if ($isUnread): ?>

                        <span
                            class="unread-dot"
                            title="Unread"
                        ></span>

                    <?php endif; ?>


                    <div class="notification-actions">


                        <?php if ($isUnread): ?>

                            <a
                                href="notifications.php?read=<?= (int)$notification["id"] ?>"
                            >
                                ✓ Read
                            </a>

                        <?php endif; ?>


                        <a
                            href="notifications.php?delete=<?= (int)$notification["id"] ?>"
                            onclick="
                                return confirm(
                                    'Delete this notification?'
                                );
                            "
                        >
                            🗑
                        </a>


                    </div>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="notifications-empty">

            <div class="notifications-empty-icon">
                🔔
            </div>

            <h2>
                You're All Caught Up!
            </h2>

            <p>
                No notifications are available right now.
            </p>

        </div>


    <?php endif; ?>


</div>


<?php

require "footer.php";

?>
```
