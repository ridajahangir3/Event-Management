<?php

require_once "db.php";

$page_title = "Registrations";
$active_page = "registrations";

$message = "";
$type = "success";


/* =========================================================
   SEND REGISTRATION EMAIL
========================================================= */

function sendRegistrationEmail(
    $to,
    $name,
    $eventTitle,
    $eventDate,
    $eventTime,
    $location
) {

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $subject = "Registration Confirmed - " . $eventTitle;

    $emailBody =
        "Hello " . $name . ",\n\n" .

        "Your registration has been successfully confirmed.\n\n" .

        "EVENT DETAILS\n" .
        "--------------------------------\n" .
        "Event: " . $eventTitle . "\n" .
        "Date: " . $eventDate . "\n" .
        "Time: " . ($eventTime ?: "Not specified") . "\n" .
        "Location: " . ($location ?: "Not specified") . "\n" .
        "--------------------------------\n\n" .

        "We look forward to seeing you at the event.\n\n" .

        "Thank you,\n" .
        "Event Management System";


    $fromEmail = "noreply@localhost.com";

    $headers  = "From: Event Management System <" . $fromEmail . ">\r\n";
    $headers .= "Reply-To: " . $fromEmail . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail(
        $to,
        $subject,
        $emailBody,
        $headers
    );
}


/* =========================================================
   DELETE REGISTRATION
========================================================= */

if (isset($_GET["delete_registration"])) {

    $registration_id = (int)($_GET["delete_registration"] ?? 0);

    if ($registration_id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM registrations WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param(
                "i",
                $registration_id
            );

            if ($stmt->execute()) {

                $message = "Registration deleted successfully.";
                $type = "success";

            } else {

                $message = "Registration could not be deleted.";
                $type = "danger";
            }

            $stmt->close();

        } else {

            $message = "Unable to delete registration.";
            $type = "danger";
        }
    }
}


/* =========================================================
   REGISTER USER
========================================================= */

if (isset($_POST["register"])) {

    $event_id = (int)($_POST["event_id"] ?? 0);
    $user_id  = (int)($_POST["user_id"] ?? 0);


    /* =====================================================
       VALIDATE SELECTION
    ===================================================== */

    if ($event_id <= 0 || $user_id <= 0) {

        $message =
            "Please select both an event and a participant.";

        $type = "danger";

    } else {


        /* =================================================
           CHECK EVENT EXISTS
        ================================================= */

        $eventCheck = $conn->prepare(
            "SELECT
                id,
                title,
                event_date,
                event_time,
                location
             FROM events
             WHERE id = ?"
        );

        $eventCheck->bind_param(
            "i",
            $event_id
        );

        $eventCheck->execute();

        $eventResult =
            $eventCheck->get_result();

        $eventInfo =
            $eventResult->fetch_assoc();

        $eventCheck->close();


        /* =================================================
           CHECK USER EXISTS
        ================================================= */

        $userCheck = $conn->prepare(
            "SELECT
                id,
                name,
                email
             FROM users
             WHERE id = ?"
        );

        $userCheck->bind_param(
            "i",
            $user_id
        );

        $userCheck->execute();

        $userResult =
            $userCheck->get_result();

        $userInfo =
            $userResult->fetch_assoc();

        $userCheck->close();


        if (!$eventInfo || !$userInfo) {

            $message =
                "Invalid event or participant selected.";

            $type = "danger";

        } else {


            /* =============================================
               CHECK DUPLICATE REGISTRATION
            ============================================= */

            $checkStmt = $conn->prepare(
                "SELECT id
                 FROM registrations
                 WHERE event_id = ?
                 AND user_id = ?"
            );

            $checkStmt->bind_param(
                "ii",
                $event_id,
                $user_id
            );

            $checkStmt->execute();

            $check =
                $checkStmt->get_result();


            if ($check && $check->num_rows > 0) {

                $message =
                    "This participant is already registered for this event.";

                $type = "danger";

            } else {


                /* =========================================
                   CHECK EVENT CAPACITY
                ========================================= */

                $capacity = 0;

                $capacityStmt = $conn->prepare(
                    "SELECT capacity
                     FROM events
                     WHERE id = ?"
                );

                if ($capacityStmt) {

                    $capacityStmt->bind_param(
                        "i",
                        $event_id
                    );

                    $capacityStmt->execute();

                    $capacityResult =
                        $capacityStmt->get_result();

                    $capacityData =
                        $capacityResult->fetch_assoc();

                    if ($capacityData) {
                        $capacity =
                            (int)$capacityData["capacity"];
                    }

                    $capacityStmt->close();
                }


                /* =========================================
                   CURRENT REGISTRATION COUNT
                ========================================= */

                $countStmt = $conn->prepare(
                    "SELECT COUNT(*) AS total
                     FROM registrations
                     WHERE event_id = ?"
                );

                $currentRegistrations = 0;

                if ($countStmt) {

                    $countStmt->bind_param(
                        "i",
                        $event_id
                    );

                    $countStmt->execute();

                    $countResult =
                        $countStmt->get_result();

                    $countData =
                        $countResult->fetch_assoc();

                    if ($countData) {

                        $currentRegistrations =
                            (int)$countData["total"];
                    }

                    $countStmt->close();
                }


                /* =========================================
                   CAPACITY CHECK
                ========================================= */

                if (
                    $capacity > 0 &&
                    $currentRegistrations >= $capacity
                ) {

                    $message =
                        "Registration is full. Maximum participants for this event have been reached.";

                    $type = "danger";

                } else {


                    /* =====================================
                       INSERT REGISTRATION
                    ===================================== */

                    $stmt = $conn->prepare(
                        "INSERT INTO registrations
                        (event_id, user_id)
                        VALUES (?, ?)"
                    );

                    if ($stmt) {

                        $stmt->bind_param(
                            "ii",
                            $event_id,
                            $user_id
                        );


                        if ($stmt->execute()) {


                            /* =============================
                               SEND EMAIL
                            ============================= */

                            $emailSent =
                                sendRegistrationEmail(
                                    $userInfo["email"],
                                    $userInfo["name"],
                                    $eventInfo["title"],
                                    $eventInfo["event_date"],
                                    $eventInfo["event_time"] ?? "",
                                    $eventInfo["location"] ?? ""
                                );


                            if ($emailSent) {

                                $message =
                                    "Registration successful! Confirmation email sent to "
                                    . $userInfo["email"] . ".";

                                $type = "success";

                            } else {

                                $message =
                                    "Registration successful, but the confirmation email could not be sent.";

                                $type = "success";
                            }

                        } else {

                            $message =
                                "Registration failed: "
                                . $stmt->error;

                            $type = "danger";
                        }

                        $stmt->close();

                    } else {

                        $message =
                            "Unable to prepare registration query.";

                        $type = "danger";
                    }
                }
            }

            $checkStmt->close();
        }
    }
}


/* =========================================================
   GET EVENTS
========================================================= */

$events = $conn->query(
    "SELECT
        id,
        title,
        event_date,
        event_time,
        location,
        capacity
     FROM events
     ORDER BY event_date ASC, event_time ASC"
);

if (!$events) {

    die(
        "Events could not be loaded: "
        . $conn->error
    );
}


/* =========================================================
   GET USERS
========================================================= */

$users = $conn->query(
    "SELECT
        id,
        name,
        email
     FROM users
     ORDER BY name ASC"
);

if (!$users) {

    die(
        "Users could not be loaded: "
        . $conn->error
    );
}


/* =========================================================
   REGISTRATION FILTERS
========================================================= */

$search = trim(
    $_GET["search"] ?? ""
);

$filterEvent =
    (int)($_GET["filter_event"] ?? 0);


/* =========================================================
   GET REGISTRATION RECORDS
========================================================= */

$regSql =
    "SELECT
        r.id,
        u.name AS user_name,
        u.email,
        e.id AS event_id,
        e.title AS event_title,
        e.event_date,
        e.event_time,
        e.location
     FROM registrations r
     JOIN users u
        ON r.user_id = u.id
     JOIN events e
        ON r.event_id = e.id
     WHERE 1=1";


$params = [];
$types = "";


if ($search !== "") {

    $regSql .=
        " AND (
            u.name LIKE ?
            OR u.email LIKE ?
            OR e.title LIKE ?
        )";

    $searchValue =
        "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "sss";
}


if ($filterEvent > 0) {

    $regSql .=
        " AND e.id = ?";

    $params[] =
        $filterEvent;

    $types .= "i";
}


$regSql .=
    " ORDER BY r.id DESC";


if (count($params) > 0) {

    $stmt = $conn->prepare(
        $regSql
    );

    if (!$stmt) {

        die(
            "Registration query error: "
            . $conn->error
        );
    }


    $stmt->bind_param(
        $types,
        ...$params
    );

    $stmt->execute();

    $regs =
        $stmt->get_result();

} else {

    $regs =
        $conn->query($regSql);
}


/* =========================================================
   TOTAL REGISTRATIONS
========================================================= */

$totalRegistrationsResult =
    $conn->query(
        "SELECT COUNT(*) AS total
         FROM registrations"
    );

$totalRegistrations = 0;

if ($totalRegistrationsResult) {

    $totalData =
        $totalRegistrationsResult->fetch_assoc();

    $totalRegistrations =
        (int)($totalData["total"] ?? 0);
}


/* =========================================================
   HEADER
========================================================= */

require "header.php";

?>


<!-- =======================================================
     PAGE HEADER
======================================================= -->

<div class="page-header registration-header">

    <div>

        <div class="eyebrow">
            ✦ EVENT REGISTRATION
        </div>

        <h1>
            Register Participants
        </h1>

        <p class="subtitle">
            Easily connect users with upcoming events
            and keep track of every registration.
        </p>

    </div>


    <div class="registration-badge">

        <span>🎟️</span>

        <div>

            <small>
                Registration Portal
            </small>

            <strong>
                Ready to Register
            </strong>

        </div>

    </div>

</div>


<!-- =======================================================
     MESSAGE
======================================================= -->

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


<!-- =======================================================
     REGISTRATION STATS
======================================================= -->

<div
    class="stats"
    style="margin-bottom:24px;"
>

    <div class="stat-card">

        <h3>
            Total Registrations
        </h3>

        <div class="number">
            <?= $totalRegistrations ?>
        </div>

        <small style="color:#718096;">
            All registered participants
        </small>

    </div>


    <div class="stat-card">

        <h3>
            Available Events
        </h3>

        <div class="number">
            <?= $events ? $events->num_rows : 0 ?>
        </div>

        <small style="color:#718096;">
            Events available for registration
        </small>

    </div>

</div>


<!-- =======================================================
     REGISTRATION AREA
======================================================= -->

<div class="registration-layout">


    <!-- ===================================================
         LEFT : FORM
    ==================================================== -->

    <section class="card registration-form-card">


        <div class="registration-card-title">

            <div class="title-icon">
                📝
            </div>

            <div>

                <h2>
                    New Registration
                </h2>

                <p>
                    Select an event and participant below.
                </p>

            </div>

        </div>


        <form
            method="POST"
            id="registrationForm"
        >


            <!-- EVENT -->

            <div class="field registration-field">

                <label>

                    <span>📅</span>

                    Select Event

                </label>


                <select
                    name="event_id"
                    id="eventSelect"
                    required
                >

                    <option value="">
                        Choose an event...
                    </option>


                    <?php if ($events && $events->num_rows > 0): ?>

                        <?php while ($e = $events->fetch_assoc()): ?>

                            <option
                                value="<?= (int)$e["id"] ?>"
                                data-title="<?= htmlspecialchars($e["title"]) ?>"
                                data-date="<?= htmlspecialchars($e["event_date"]) ?>"
                                data-time="<?= htmlspecialchars($e["event_time"] ?? "") ?>"
                                data-location="<?= htmlspecialchars($e["location"] ?? "") ?>"
                                data-capacity="<?= (int)($e["capacity"] ?? 0) ?>"
                            >

                                <?= htmlspecialchars($e["title"]) ?>

                                —

                                <?= htmlspecialchars($e["event_date"]) ?>

                            </option>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <option value="" disabled>
                            No events available
                        </option>

                    <?php endif; ?>

                </select>

            </div>


            <!-- EVENT SEARCH -->

            <div
                class="field registration-field"
                style="margin-top:-5px;"
            >

                <input
                    type="text"
                    id="eventSearch"
                    placeholder="🔎 Search event..."
                    autocomplete="off"
                >

            </div>


            <!-- EVENT INFO -->

            <div
                id="eventInfoBox"
                style="
                    display:none;
                    margin-bottom:20px;
                    padding:15px;
                    border-radius:12px;
                    background:#f7f4ff;
                    border:1px solid #e5dcff;
                "
            >

                <strong>
                    Event Information
                </strong>

                <div
                    style="
                        margin-top:10px;
                        display:grid;
                        gap:6px;
                        font-size:13px;
                        color:#475467;
                    "
                >

                    <div>
                        📅
                        <strong>Date:</strong>
                        <span id="infoDate">—</span>
                    </div>

                    <div>
                        🕐
                        <strong>Time:</strong>
                        <span id="infoTime">—</span>
                    </div>

                    <div>
                        📍
                        <strong>Location:</strong>
                        <span id="infoLocation">—</span>
                    </div>

                    <div>
                        👥
                        <strong>Capacity:</strong>
                        <span id="infoCapacity">—</span>
                    </div>

                </div>

            </div>


            <!-- PARTICIPANT -->

            <div class="field registration-field">

                <label>

                    <span>👤</span>

                    Select Participant

                </label>


                <input
                    type="text"
                    id="participantSearch"
                    placeholder="🔎 Search participant by name or email..."
                    autocomplete="off"
                    style="margin-bottom:10px;"
                >


                <select
                    name="user_id"
                    id="userSelect"
                    required
                >

                    <option value="">
                        Choose a participant...
                    </option>


                    <?php if ($users && $users->num_rows > 0): ?>

                        <?php while ($u = $users->fetch_assoc()): ?>

                            <option
                                value="<?= (int)$u["id"] ?>"
                                data-name="<?= htmlspecialchars($u["name"]) ?>"
                                data-email="<?= htmlspecialchars($u["email"]) ?>"
                            >

                                <?= htmlspecialchars($u["name"]) ?>

                                —

                                <?= htmlspecialchars($u["email"]) ?>

                            </option>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <option value="" disabled>
                            No participants available
                        </option>

                    <?php endif; ?>

                </select>

            </div>


            <!-- SELECTED USER PREVIEW -->

            <div
                class="selected-user-preview"
                id="selectedUserPreview"
            >

                <div
                    class="mini-avatar"
                    id="userAvatar"
                >
                    ?
                </div>


                <div>

                    <small>
                        Selected Participant
                    </small>

                    <strong id="selectedUserName">
                        No participant selected
                    </strong>

                </div>


                <span class="check-mark">
                    ✓
                </span>

            </div>


            <!-- BUTTON -->

            <div
                class="form-actions registration-submit"
            >

                <button
                    class="btn btn-primary register-button"
                    name="register"
                    type="submit"
                >

                    <span>
                        ＋
                    </span>

                    Complete Registration

                </button>

            </div>

        </form>

    </section>


    <!-- ===================================================
         RIGHT : EVENT PREVIEW
    ==================================================== -->

    <section
        class="event-preview-card"
        id="eventPreview"
    >

        <div class="preview-glow"></div>


        <div class="preview-label">
            EVENT PREVIEW
        </div>


        <div class="preview-icon">
            ✦
        </div>


        <h2 id="previewTitle">
            Select an event
        </h2>


        <p class="preview-description">
            Your selected event details will appear here.
        </p>


        <div class="preview-details">


            <!-- DATE -->

            <div class="preview-detail">

                <div class="detail-icon">
                    📅
                </div>

                <div>

                    <small>
                        Date
                    </small>

                    <strong id="previewDate">
                        —
                    </strong>

                </div>

            </div>


            <!-- TIME -->

            <div class="preview-detail">

                <div class="detail-icon">
                    🕐
                </div>

                <div>

                    <small>
                        Time
                    </small>

                    <strong id="previewTime">
                        —
                    </strong>

                </div>

            </div>


            <!-- LOCATION -->

            <div class="preview-detail">

                <div class="detail-icon">
                    📍
                </div>

                <div>

                    <small>
                        Location
                    </small>

                    <strong id="previewLocation">
                        —
                    </strong>

                </div>

            </div>


            <!-- CAPACITY -->

            <div class="preview-detail">

                <div class="detail-icon">
                    👥
                </div>

                <div>

                    <small>
                        Maximum Participants
                    </small>

                    <strong id="previewCapacity">
                        —
                    </strong>

                </div>

            </div>

        </div>


        <div class="preview-footer">

            <span>
                🎟️
            </span>

            <div>

                <strong>
                    Registration available
                </strong>

                <small>
                    Confirmation email will be sent
                </small>

            </div>

        </div>

    </section>

</div>


<!-- =======================================================
     REGISTRATION RECORDS
======================================================= -->

<section class="card registration-records">


    <div class="records-header">

        <div>

            <div class="records-icon">
                📋
            </div>

            <div>

                <h2>
                    Registration Records
                </h2>

                <p>
                    View and manage registered participants.
                </p>

            </div>

        </div>


        <div class="records-count">

            <span>
                <?= $regs ? $regs->num_rows : 0 ?>
            </span>

            registrations

        </div>

    </div>


    <!-- ===================================================
         SEARCH + FILTER
    ==================================================== -->

    <form
        method="GET"
        style="
            display:flex;
            gap:12px;
            flex-wrap:wrap;
            margin:20px 0;
        "
    >

        <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="🔎 Search participant, email or event..."
            style="
                flex:1;
                min-width:250px;
            "
        >


        <select
            name="filter_event"
            style="min-width:220px;"
        >

            <option value="">
                All Events
            </option>


            <?php

            $filterEvents =
                $conn->query(
                    "SELECT id, title
                     FROM events
                     ORDER BY event_date ASC"
                );

            if (
                $filterEvents &&
                $filterEvents->num_rows > 0
            ):

                while (
                    $fe =
                    $filterEvents->fetch_assoc()
                ):

            ?>

                <option
                    value="<?= (int)$fe["id"] ?>"
                    <?= $filterEvent === (int)$fe["id"]
                        ? "selected"
                        : "" ?>
                >

                    <?= htmlspecialchars(
                        $fe["title"]
                    ) ?>

                </option>

            <?php

                endwhile;

            endif;

            ?>

        </select>


        <button
            type="submit"
            class="btn"
        >
            Search
        </button>


        <a
            href="register_event.php"
            class="btn"
            style="
                background:#eef0f5;
                color:#475467;
                box-shadow:none;
            "
        >
            Clear
        </a>

    </form>


    <!-- ===================================================
         TABLE
    ==================================================== -->

    <div class="table-wrap">

        <table class="table">

            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        Participant
                    </th>

                    <th>
                        Email
                    </th>

                    <th>
                        Event
                    </th>

                    <th>
                        Date
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php if ($regs && $regs->num_rows > 0): ?>


                <?php while ($r = $regs->fetch_assoc()): ?>


                    <tr>


                        <!-- ID -->

                        <td>

                            <span class="registration-id">

                                #<?= (int)$r["id"] ?>

                            </span>

                        </td>


                        <!-- PARTICIPANT -->

                        <td>

                            <div class="record-user">

                                <div class="record-avatar">

                                    <?= htmlspecialchars(
                                        strtoupper(
                                            substr(
                                                $r["user_name"],
                                                0,
                                                1
                                            )
                                        )
                                    ) ?>

                                </div>


                                <strong>

                                    <?= htmlspecialchars(
                                        $r["user_name"]
                                    ) ?>

                                </strong>

                            </div>

                        </td>


                        <!-- EMAIL -->

                        <td>

                            <span class="record-email">

                                <?= htmlspecialchars(
                                    $r["email"]
                                ) ?>

                            </span>

                        </td>


                        <!-- EVENT -->

                        <td>

                            <strong class="record-event">

                                <?= htmlspecialchars(
                                    $r["event_title"]
                                ) ?>

                            </strong>

                        </td>


                        <!-- DATE -->

                        <td>

                            <span class="record-date">

                                <?= htmlspecialchars(
                                    $r["event_date"]
                                ) ?>

                            </span>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <span class="registered-status">

                                <span></span>

                                Registered

                            </span>

                        </td>


                        <!-- ACTION -->

                        <td>

                            <a
                                href="register_event.php?delete_registration=<?= (int)$r["id"] ?>"
                                class="btn"
                                style="
                                    background:#fff0f0;
                                    color:#dc2626;
                                    border:1px solid #ffd5d5;
                                    box-shadow:none;
                                    padding:7px 10px;
                                "
                                onclick="
                                    return confirm(
                                        'Are you sure you want to delete this registration?'
                                    );
                                "
                            >

                                🗑 Delete

                            </a>

                        </td>


                    </tr>


                <?php endwhile; ?>


            <?php else: ?>


                <tr>

                    <td colspan="7">

                        <div
                            class="empty-registration"
                            style="
                                text-align:center;
                                padding:45px 20px;
                            "
                        >

                            <div style="font-size:40px;">
                                🎟️
                            </div>

                            <h3>
                                No registrations found
                            </h3>

                            <p>
                                Register a participant using
                                the form above.
                            </p>

                        </div>

                    </td>

                </tr>


            <?php endif; ?>


            </tbody>

        </table>

    </div>

</section>


<!-- =======================================================
     JAVASCRIPT
======================================================= -->

<script>


/* =========================================================
   EVENT PREVIEW
========================================================= */

const eventSelect =
    document.getElementById("eventSelect");

const eventSearch =
    document.getElementById("eventSearch");

const previewTitle =
    document.getElementById("previewTitle");

const previewDate =
    document.getElementById("previewDate");

const previewTime =
    document.getElementById("previewTime");

const previewLocation =
    document.getElementById("previewLocation");

const previewCapacity =
    document.getElementById("previewCapacity");

const eventInfoBox =
    document.getElementById("eventInfoBox");

const infoDate =
    document.getElementById("infoDate");

const infoTime =
    document.getElementById("infoTime");

const infoLocation =
    document.getElementById("infoLocation");

const infoCapacity =
    document.getElementById("infoCapacity");


if (eventSelect) {

    eventSelect.addEventListener(
        "change",
        function () {

            const option =
                this.options[this.selectedIndex];


            if (!this.value) {

                previewTitle.textContent =
                    "Select an event";

                previewDate.textContent =
                    "—";

                previewTime.textContent =
                    "—";

                previewLocation.textContent =
                    "—";

                previewCapacity.textContent =
                    "—";

                if (eventInfoBox) {
                    eventInfoBox.style.display =
                        "none";
                }

                return;
            }


            previewTitle.textContent =
                option.dataset.title ||
                "Event";


            previewDate.textContent =
                option.dataset.date ||
                "Not specified";


            previewTime.textContent =
                option.dataset.time ||
                "Not specified";


            previewLocation.textContent =
                option.dataset.location ||
                "Not specified";


            previewCapacity.textContent =
                option.dataset.capacity &&
                option.dataset.capacity !== "0"
                    ? option.dataset.capacity
                    : "Not specified";


            if (eventInfoBox) {

                eventInfoBox.style.display =
                    "block";

                infoDate.textContent =
                    option.dataset.date ||
                    "Not specified";

                infoTime.textContent =
                    option.dataset.time ||
                    "Not specified";

                infoLocation.textContent =
                    option.dataset.location ||
                    "Not specified";

                infoCapacity.textContent =
                    option.dataset.capacity &&
                    option.dataset.capacity !== "0"
                        ? option.dataset.capacity
                        : "Not specified";
            }

        }
    );

}


/* =========================================================
   EVENT SEARCH
========================================================= */

if (eventSearch && eventSelect) {

    eventSearch.addEventListener(
        "input",
        function () {

            const searchText =
                this.value
                    .toLowerCase()
                    .trim();


            const options =
                eventSelect.querySelectorAll(
                    "option"
                );


            options.forEach(
                function (option, index) {

                    if (index === 0) {
                        return;
                    }

                    const text =
                        option.textContent
                            .toLowerCase();

                    if (
                        searchText === "" ||
                        text.includes(searchText)
                    ) {

                        option.style.display =
                            "";

                    } else {

                        option.style.display =
                            "none";
                    }

                }
            );

        }
    );

}


/* =========================================================
   PARTICIPANT SEARCH
========================================================= */

const participantSearch =
    document.getElementById(
        "participantSearch"
    );

const userSelect =
    document.getElementById(
        "userSelect"
    );


if (
    participantSearch &&
    userSelect
) {

    participantSearch.addEventListener(
        "input",
        function () {

            const searchText =
                this.value
                    .toLowerCase()
                    .trim();


            const options =
                userSelect.querySelectorAll(
                    "option"
                );


            options.forEach(
                function (option, index) {

                    if (index === 0) {
                        return;
                    }


                    const name =
                        (
                            option.dataset.name ||
                            ""
                        ).toLowerCase();


                    const email =
                        (
                            option.dataset.email ||
                            ""
                        ).toLowerCase();


                    const text =
                        option.textContent
                            .toLowerCase();


                    if (
                        searchText === "" ||
                        name.includes(searchText) ||
                        email.includes(searchText) ||
                        text.includes(searchText)
                    ) {

                        option.style.display =
                            "";

                    } else {

                        option.style.display =
                            "none";
                    }

                }
            );

        }
    );

}


/* =========================================================
   USER PREVIEW
========================================================= */

const userName =
    document.getElementById(
        "selectedUserName"
    );

const userAvatar =
    document.getElementById(
        "userAvatar"
    );


if (userSelect) {

    userSelect.addEventListener(
        "change",
        function () {

            const option =
                this.options[this.selectedIndex];


            if (!this.value) {

                userName.textContent =
                    "No participant selected";

                userAvatar.textContent =
                    "?";

                return;
            }


            const name =
                option.dataset.name ||
                option.textContent
                    .split(" — ")[0]
                    .trim();


            userName.textContent =
                name;


            userAvatar.textContent =
                name
                    .charAt(0)
                    .toUpperCase();

        }
    );

}


</script>


<?php

require "footer.php";

?>