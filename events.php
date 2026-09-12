<?php

require_once "db.php";

$page_title = "Manage Events";
$active_page = "events";

$message = "";
$type = "success";


/* =========================
   DELETE EVENT
========================= */

if (isset($_GET["delete"])) {

    $id = (int) $_GET["delete"];

    if ($id > 0) {

        /* DELETE ATTENDANCE */

        $stmt = $conn->prepare(
            "DELETE FROM attendance
             WHERE registration_id IN
             (
                 SELECT id
                 FROM registrations
                 WHERE event_id = ?
             )"
        );

        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }


        /* DELETE REGISTRATIONS */

        $stmt = $conn->prepare(
            "DELETE FROM registrations
             WHERE event_id = ?"
        );

        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }


        /* DELETE EVENT */

        $stmt = $conn->prepare(
            "DELETE FROM events
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {

                $message = "Event deleted successfully.";
                $type = "success";

            } else {

                $message = "Event could not be deleted.";
                $type = "danger";
            }

            $stmt->close();

        } else {

            $message = "Unable to delete event.";
            $type = "danger";
        }
    }
}


/* =========================
   ADD EVENT
========================= */

if (isset($_POST["add_event"])) {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $organizer_name = trim($_POST["organizer"] ?? "");
    $event_date = $_POST["event_date"] ?? "";
    $event_time = $_POST["event_time"] ?? "";
    $location = trim($_POST["location"] ?? "");
    $capacity = (int)($_POST["capacity"] ?? 0);


    /* =========================
       VALIDATION
    ========================= */

    if (
        $title === "" ||
        $event_date === "" ||
        $event_time === "" ||
        $location === ""
    ) {

        $message = "Please fill all required event details.";
        $type = "danger";

    } else {


        /* =========================
           INSERT EVENT
        ========================= */

        $stmt = $conn->prepare(
            "INSERT INTO events
            (
                title,
                description,
                category,
                organizer_name,
                event_date,
                event_time,
                location,
                capacity
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );


        if ($stmt) {

            $stmt->bind_param(
                "sssssssi",
                $title,
                $description,
                $category,
                $organizer_name,
                $event_date,
                $event_time,
                $location,
                $capacity
            );


            if ($stmt->execute()) {

                $message = "🎉 Event created successfully!";
                $type = "success";

            } else {

                $message =
                    "Event could not be created: " .
                    $stmt->error;

                $type = "danger";
            }

            $stmt->close();

        } else {

            $message =
                "Unable to prepare event query: " .
                $conn->error;

            $type = "danger";
        }
    }
}


/* =========================
   GET EVENTS
========================= */

$result = $conn->query(
    "SELECT
        id,
        title,
        description,
        category,
        organizer_name,
        event_date,
        event_time,
        location,
        capacity
     FROM events
     ORDER BY event_date ASC, event_time ASC"
);


/* =========================
   EVENT STATS
========================= */

$totalEvents = 0;
$upcomingEvents = 0;

if ($result) {

    $totalEvents = $result->num_rows;

    $today = date("Y-m-d");

    $result->data_seek(0);

    while ($tempEvent = $result->fetch_assoc()) {

        if ($tempEvent["event_date"] >= $today) {
            $upcomingEvents++;
        }
    }

    $result->data_seek(0);
}


require "header.php";

?>


<!-- =========================
     MESSAGE
========================= -->

<?php if ($message): ?>

<div
    class="alert alert-<?= htmlspecialchars($type) ?>"
    style="
        padding:15px 18px;
        margin-bottom:22px;
        border-radius:12px;
        font-weight:600;
    "
>
    <?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>


<!-- =========================
     PAGE HEADER
========================= -->

<div class="page-header">

    <div>

        <div
            style="
                color:#6d35f2;
                font-size:12px;
                font-weight:800;
                letter-spacing:1.5px;
                margin-bottom:8px;
                text-transform:uppercase;
            "
        >
            ✦ Event Management
        </div>

        <h1>
            Manage Events
        </h1>

        <p class="subtitle">
            Create, organize and manage all your college events.
        </p>

    </div>


    <button
        type="button"
        class="btn"
        onclick="toggleEventForm()"
        id="addEventBtn"
    >
        ＋ Add Event
    </button>

</div>


<!-- =========================
     ADD EVENT FORM
========================= -->

<div
    id="eventForm"
    class="card"
    style="
        display:none;
        margin-bottom:24px;
        background:
            linear-gradient(
                135deg,
                rgba(109,53,242,.08),
                rgba(255,255,255,.98)
            );
        border:1px solid #e5dcff;
    "
>

    <div class="card-head">

        <div>

            <div
                style="
                    width:46px;
                    height:46px;
                    border-radius:14px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    background:#eee8ff;
                    font-size:23px;
                    margin-bottom:12px;
                "
            >
                📅
            </div>

            <h2>
                Create New Event
            </h2>

            <p
                style="
                    color:#718096;
                    margin-top:5px;
                    font-size:13px;
                "
            >
                Add the details of your new event below.
            </p>

        </div>

    </div>


    <form method="POST">


        <div class="form-grid">


            <!-- EVENT NAME -->

            <div class="field">

                <label>
                    Event Name *
                </label>

                <input
                    type="text"
                    name="title"
                    placeholder="e.g. Independence Day Celebration"
                    required
                >

            </div>


            <!-- LOCATION -->

            <div class="field">

                <label>
                    Location *
                </label>

                <input
                    type="text"
                    name="location"
                    placeholder="e.g. College Ground"
                    required
                >

            </div>


            <!-- DATE -->

            <div class="field">

                <label>
                    Event Date *
                </label>

                <input
                    type="date"
                    name="event_date"
                    required
                >

            </div>


            <!-- TIME -->

            <div class="field">

                <label>
                    Event Time *
                </label>

                <input
                    type="time"
                    name="event_time"
                    required
                >

            </div>


            <!-- CATEGORY -->

            <div class="field">

                <label>
                    Event Category
                </label>

                <select name="category">

                    <option value="">
                        Select Category
                    </option>

                    <option value="Workshop">
                        Workshop
                    </option>

                    <option value="Seminar">
                        Seminar
                    </option>

                    <option value="Sports">
                        Sports
                    </option>

                    <option value="Cultural">
                        Cultural
                    </option>

                    <option value="Fest">
                        Fest
                    </option>

                    <option value="Other">
                        Other
                    </option>

                </select>

            </div>


            <!-- ORGANIZER -->

            <div class="field">

                <label>
                    Organizer Name
                </label>

                <input
                    type="text"
                    name="organizer"
                    placeholder="e.g. Computer Department"
                >

            </div>


            <!-- CAPACITY -->

            <div class="field">

                <label>
                    Maximum Participants
                </label>

                <input
                    type="number"
                    name="capacity"
                    min="0"
                    value="0"
                    placeholder="e.g. 100"
                >

            </div>


            <!-- DESCRIPTION -->

            <div
                class="field"
                style="
                    grid-column:1 / -1;
                "
            >

                <label>
                    Event Description
                </label>

                <textarea
                    name="description"
                    rows="4"
                    maxlength="1000"
                    placeholder="Write a short description about the event..."
                    oninput="updateCharacterCount(this)"
                ></textarea>

                <small
                    id="characterCount"
                    style="
                        display:block;
                        text-align:right;
                        color:#718096;
                        margin-top:5px;
                    "
                >
                    0 / 1000
                </small>

            </div>


        </div>


        <!-- BUTTONS -->

        <div class="form-actions">

            <button
                type="submit"
                name="add_event"
                class="btn btn-primary"
            >
                ✨ Create Event
            </button>


            <button
                type="button"
                class="btn"
                onclick="toggleEventForm()"
                style="
                    background:#f1f3f8;
                    color:#475467;
                    box-shadow:none;
                "
            >
                Cancel
            </button>

        </div>

    </form>

</div>


<!-- =========================
     EVENT STATS
========================= -->

<div
    class="stats"
    style="margin-bottom:24px;"
>


    <div class="stat-card">

        <h3>
            Total Events
        </h3>

        <div class="number">
            <?= $totalEvents ?>
        </div>

        <small style="color:#718096;">
            All created events
        </small>

    </div>


    <div class="stat-card">

        <h3>
            Upcoming Events
        </h3>

        <div class="number">
            <?= $upcomingEvents ?>
        </div>

        <small style="color:#718096;">
            Events still to come
        </small>

    </div>


    <div class="stat-card">

        <h3>
            Event Status
        </h3>

        <div
            style="
                margin-top:5px;
                color:#16803a;
                font-weight:800;
            "
        >
            ● System Active
        </div>

        <small style="color:#718096;">
            Event management ready
        </small>

    </div>


    <div
        class="stat-card"
        style="
            background:
                linear-gradient(
                    135deg,
                    #241653,
                    #6432e9
                );
            color:white;
        "
    >

        <h3 style="color:rgba(255,255,255,.7);">
            Quick Action
        </h3>

        <div
            style="
                font-size:18px;
                font-weight:800;
                margin-bottom:10px;
            "
        >
            Create an Event ✨
        </div>

        <button
            type="button"
            onclick="toggleEventForm()"
            style="
                background:white;
                color:#6334d9;
                box-shadow:none;
                padding:8px 12px;
                border:0;
                border-radius:8px;
                cursor:pointer;
                font-weight:700;
            "
        >
            ＋ Add Now
        </button>

    </div>

</div>


<!-- =========================
     ALL EVENTS
========================= -->

<div class="card">

    <div class="card-head">

        <div>

            <h2>
                All Events
            </h2>

            <p
                style="
                    color:#718096;
                    font-size:13px;
                    margin-top:5px;
                "
            >
                View and manage your scheduled events.
            </p>

        </div>


        <span
            class="badge"
            style="
                background:#eee9ff;
                color:#6334d9;
            "
        >
            <?= $totalEvents ?> Events
        </span>

    </div>


    <?php if ($result && $result->num_rows > 0): ?>


    <div class="table-wrap">

        <table class="table">

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Event</th>

                    <th>Category</th>

                    <th>Organizer</th>

                    <th>Date</th>

                    <th>Time</th>

                    <th>Location</th>

                    <th>Capacity</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>


            <?php while ($event = $result->fetch_assoc()): ?>


                <?php

                $eventDate =
                    $event["event_date"] ?? "";

                $today =
                    date("Y-m-d");


                if ($eventDate > $today) {

                    $status = "Upcoming";
                    $statusClass = "upcoming";

                } elseif ($eventDate === $today) {

                    $status = "Today";
                    $statusClass = "today";

                } else {

                    $status = "Completed";
                    $statusClass = "completed";
                }

                ?>


                <tr>


                    <!-- ID -->

                    <td>
                        #<?= (int)$event["id"] ?>
                    </td>


                    <!-- EVENT -->

                    <td>

                        <strong>
                            <?= htmlspecialchars(
                                $event["title"] ?? "Event"
                            ) ?>
                        </strong>

                    </td>


                    <!-- CATEGORY -->

                    <td>

                        <?= htmlspecialchars(
                            $event["category"] ?? ""
                        ) ?>

                    </td>


                    <!-- ORGANIZER -->

                    <td>

                        <?= htmlspecialchars(
                            $event["organizer_name"] ?? ""
                        ) ?>

                    </td>


                    <!-- DATE -->

                    <td>

                        <span class="event-date">

                            <?= htmlspecialchars(
                                $eventDate
                            ) ?>

                        </span>

                    </td>


                    <!-- TIME -->

                    <td>

                        <?= htmlspecialchars(
                            $event["event_time"] ?? ""
                        ) ?>

                    </td>


                    <!-- LOCATION -->

                    <td>

                        📍
                        <?= htmlspecialchars(
                            $event["location"] ?? ""
                        ) ?>

                    </td>


                    <!-- CAPACITY -->

                    <td>

                        <?= (int)(
                            $event["capacity"] ?? 0
                        ) ?>

                    </td>


                    <!-- STATUS -->

                    <td>

                        <span
                            class="status <?= htmlspecialchars(
                                $statusClass
                            ) ?>"
                        >
                            <?= htmlspecialchars($status) ?>
                        </span>

                    </td>


                    <!-- ACTION -->

                    <td>

                        <div
                            class="actions"
                            style="
                                display:flex;
                                gap:8px;
                            "
                        >

                            <a
                                href="events.php?delete=<?= (int)$event["id"] ?>"
                                class="btn delete"
                                onclick="
                                    return confirm(
                                        'Are you sure you want to delete this event?'
                                    );
                                "
                                style="
                                    background:#fff0f0;
                                    color:#dc2626;
                                    border:1px solid #ffd5d5;
                                    box-shadow:none;
                                "
                            >
                                🗑 Delete
                            </a>

                        </div>

                    </td>


                </tr>


            <?php endwhile; ?>


            </tbody>

        </table>

    </div>


    <?php else: ?>


    <!-- EMPTY STATE -->

    <div
        style="
            text-align:center;
            padding:65px 20px;
            color:#64748b;
        "
    >

        <div
            style="
                width:80px;
                height:80px;
                margin:0 auto 18px;
                border-radius:24px;
                display:flex;
                align-items:center;
                justify-content:center;
                background:#eee9ff;
                font-size:38px;
            "
        >
            📅
        </div>


        <h2
            style="
                color:#172033;
                margin-bottom:8px;
            "
        >
            No Events Yet
        </h2>


        <p style="margin-bottom:20px;">
            Your event list is empty.
            Create your first event!
        </p>


        <button
            type="button"
            class="btn"
            onclick="toggleEventForm()"
        >
            ✨ Create First Event
        </button>

    </div>


    <?php endif; ?>

</div>


<!-- =========================
     JAVASCRIPT
========================= -->

<script>

function toggleEventForm() {

    const form =
        document.getElementById("eventForm");

    const button =
        document.getElementById("addEventBtn");


    if (
        form.style.display === "none" ||
        form.style.display === ""
    ) {

        form.style.display = "block";

        if (button) {
            button.innerHTML = "✕ Close";
        }

        form.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });

    } else {

        form.style.display = "none";

        if (button) {
            button.innerHTML = "＋ Add Event";
        }
    }
}


/* =========================
   DESCRIPTION COUNTER
========================= */

function updateCharacterCount(textarea) {

    const counter =
        document.getElementById("characterCount");

    if (counter) {

        counter.innerHTML =
            textarea.value.length + " / 1000";
    }
}

</script>


<?php

require "footer.php";

?>