<?php

require_once "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$page_title = "Add Event";
$active_page = "events";

$message = "";
$type = "success";

$title = "";
$description = "";
$category = "";
$event_date = "";
$event_time = "";
$location = "";
$organizer_name = "";
$capacity = "";
$registration_deadline = "";
$event_status = "Upcoming";
$event_type = "Free";
$maps_link = "";
$notify_email = "";


/* =========================
   ADD EVENT
========================= */

if (isset($_POST["add_event"])) {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $event_date = $_POST["event_date"] ?? "";
    $event_time = $_POST["event_time"] ?? "";
    $location = trim($_POST["location"] ?? "");
    $organizer_name = trim($_POST["organizer_name"] ?? "");
    $capacity = $_POST["capacity"] ?? "";
    $registration_deadline = $_POST["registration_deadline"] ?? "";
    $event_status = $_POST["event_status"] ?? "Upcoming";
    $event_type = $_POST["event_type"] ?? "Free";
    $maps_link = trim($_POST["maps_link"] ?? "");
    $notify_email = trim($_POST["notify_email"] ?? "");


    /* =========================
       VALIDATION
    ========================= */

    if (
        $title === "" ||
        $category === "" ||
        $event_date === "" ||
        $event_time === "" ||
        $location === "" ||
        $organizer_name === "" ||
        $capacity === ""
    ) {

        $message = "Please fill all required event fields.";
        $type = "danger";

    } elseif (!is_numeric($capacity) || $capacity < 1) {

        $message = "Maximum participants must be at least 1.";
        $type = "danger";

    } elseif (
        $notify_email !== "" &&
        !filter_var($notify_email, FILTER_VALIDATE_EMAIL)
    ) {

        $message = "Please enter a valid notification email.";
        $type = "danger";

    } elseif (
        $maps_link !== "" &&
        !filter_var($maps_link, FILTER_VALIDATE_URL)
    ) {

        $message = "Please enter a valid Google Maps link.";
        $type = "danger";

    } else {

        $capacity = (int)$capacity;


        /* =========================
           INSERT EVENT
        ========================= */

        $stmt = $conn->prepare(
            "INSERT INTO events
            (
                title,
                description,
                category,
                event_date,
                event_time,
                location,
                capacity,
                organizer_name,
                registration_deadline,
                event_status,
                event_type,
                maps_link
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "ssssssisssss",
            $title,
            $description,
            $category,
            $event_date,
            $event_time,
            $location,
            $capacity,
            $organizer_name,
            $registration_deadline,
            $event_status,
            $event_type,
            $maps_link
        );


        if ($stmt->execute()) {


            /* =========================
               SEND EMAIL
            ========================= */

            $emailSent = false;

            if ($notify_email !== "") {

                $subject =
                    "New Event Available - " . $title;


                $body =
                    "Hello,\n\n" .
                    "A new college event has been added.\n\n" .

                    "EVENT DETAILS\n" .
                    "--------------------------------\n" .
                    "Event: " . $title . "\n" .
                    "Category: " . $category . "\n" .
                    "Date: " . $event_date . "\n" .
                    "Time: " . $event_time . "\n" .
                    "Location: " . $location . "\n" .
                    "Organizer: " . $organizer_name . "\n" .
                    "Maximum Participants: " . $capacity . "\n" .
                    "Event Type: " . $event_type . "\n" .
                    "Status: " . $event_status . "\n" .
                    "--------------------------------\n\n" .

                    "You can now register for this event.\n\n" .

                    "Thank you,\n" .
                    "College Event Hub";


                try {

                    $mail = new PHPMailer(true);

                    $mail->isSMTP();

                    $mail->Host = 'smtp.gmail.com';

                    $mail->SMTPAuth = true;

                    $mail->Username = 'ridajahangir187@gmail.com';

                    $mail->Password = 'ewry nniu stsr mnqe';

                    $mail->SMTPSecure =
                        PHPMailer::ENCRYPTION_STARTTLS;

                    $mail->Port = 587;


                    $mail->setFrom(
                        'ridajahangir187@gmail.com',
                        'College Event Hub'
                    );


                    $mail->addAddress($notify_email);


                    $mail->isHTML(false);

                    $mail->Subject = $subject;

                    $mail->Body = $body;


                    $mail->send();

                    $emailSent = true;

                } catch (Exception $e) {

                    $emailSent = false;
                }
            }


            /* =========================
               RESULT MESSAGE
            ========================= */

            if ($notify_email !== "") {

                if ($emailSent) {

                    $message =
                        "Event created successfully! " .
                        "Notification sent to " .
                        $notify_email . ".";

                    $type = "success";

                } else {

                    $message =
                        "Event created successfully, but the email " .
                        "could not be sent.";

                    $type = "danger";
                }

            } else {

                $message =
                    "Event created successfully!";

                $type = "success";
            }


            /* Clear form */

            $title = "";
            $description = "";
            $category = "";
            $event_date = "";
            $event_time = "";
            $location = "";
            $organizer_name = "";
            $capacity = "";
            $registration_deadline = "";
            $event_status = "Upcoming";
            $event_type = "Free";
            $maps_link = "";
            $notify_email = "";

        } else {

            $message =
                "Event could not be added: " .
                $stmt->error;

            $type = "danger";
        }


        $stmt->close();
    }
}


require "header.php";

?>


<!-- =========================
     PAGE HEADER
========================= -->

<div class="page-header">

    <div>

        <h1>
            Create New Event ✨
        </h1>

        <p class="subtitle">
            Add complete event details and notify participants.
        </p>

    </div>


    <a
        href="events.php"
        class="btn"
    >
        ← Back to Events
    </a>

</div>


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
     MAIN GRID
========================= -->

<div
    style="
        display:grid;
        grid-template-columns:minmax(0, 1.7fr) minmax(280px, 1fr);
        gap:25px;
        align-items:start;
    "
>


<!-- =========================
     EVENT FORM
========================= -->

<div class="card">

    <div
        class="card-head"
        style="margin-bottom:25px;"
    >

        <div>

            <h2>
                📋 Event Information
            </h2>

            <p
                style="
                    color:#718096;
                    margin-top:6px;
                "
            >
                Enter the details of your new event.
            </p>

        </div>

    </div>


    <form method="POST" action="">


        <!-- EVENT NAME -->

        <div style="margin-bottom:18px;">

            <label
                style="
                    display:block;
                    font-weight:700;
                    margin-bottom:8px;
                "
            >
                📌 Event Name *
            </label>

            <input
                type="text"
                name="title"
                id="title"
                placeholder="Enter event name"
                value="<?= htmlspecialchars($title) ?>"
                maxlength="150"
                required
            >

        </div>


        <!-- DESCRIPTION -->

        <div style="margin-bottom:18px;">

            <label
                style="
                    display:block;
                    font-weight:700;
                    margin-bottom:8px;
                "
            >
                📝 Description
            </label>

            <textarea
                name="description"
                id="description"
                rows="5"
                maxlength="1000"
                placeholder="Describe the event..."
                style="
                    width:100%;
                    resize:vertical;
                "
            ><?= htmlspecialchars($description) ?></textarea>

            <div
                style="
                    text-align:right;
                    font-size:12px;
                    color:#718096;
                    margin-top:5px;
                "
            >
                <span id="charCount">0</span>/1000
            </div>

        </div>


        <!-- CATEGORY -->

        <div style="margin-bottom:18px;">

            <label
                style="
                    display:block;
                    font-weight:700;
                    margin-bottom:8px;
                "
            >
                🏷️ Event Category *
            </label>

            <select
                name="category"
                required
            >

                <option value="">Select Category</option>

                <option value="Workshop"
                    <?= $category === "Workshop" ? "selected" : "" ?>>
                    Workshop
                </option>

                <option value="Seminar"
                    <?= $category === "Seminar" ? "selected" : "" ?>>
                    Seminar
                </option>

                <option value="Sports"
                    <?= $category === "Sports" ? "selected" : "" ?>>
                    Sports
                </option>

                <option value="Fest"
                    <?= $category === "Fest" ? "selected" : "" ?>>
                    Fest
                </option>

                <option value="Cultural"
                    <?= $category === "Cultural" ? "selected" : "" ?>>
                    Cultural
                </option>

                <option value="Technical"
                    <?= $category === "Technical" ? "selected" : "" ?>>
                    Technical
                </option>

                <option value="Other"
                    <?= $category === "Other" ? "selected" : "" ?>>
                    Other
                </option>

            </select>

        </div>


        <!-- DATE + TIME -->

        <div
            style="
                display:grid;
                grid-template-columns:1fr 1fr;
                gap:18px;
                margin-bottom:18px;
            "
        >

            <div>

                <label
                    style="
                        display:block;
                        font-weight:700;
                        margin-bottom:8px;
                    "
                >
                    📅 Event Date *
                </label>

                <input
                    type="date"
                    name="event_date"
                    value="<?= htmlspecialchars($event_date) ?>"
                    required
                >

            </div>


            <div>

                <label
                    style="
                        display:block;
                        font-weight:700;
                        margin-bottom:8px;
                    "
                >
                    🕐 Event Time *
                </label>

                <input
                    type="time"
                    name="event_time"
                    value="<?= htmlspecialchars($event_time) ?>"
                    required
                >

            </div>

        </div>


        <!-- LOCATION -->

        <div style="margin-bottom:18px;">

            <label
                style="
                    display:block;
                    font-weight:700;
                    margin-bottom:8px;
                "
            >
                📍 Venue *
            </label>

            <input
                type="text"
                name="location"
                placeholder="Enter event venue"
                value="<?= htmlspecialchars($location) ?>"
                required
            >

        </div>


        <!-- MAPS LINK -->

        <div style="margin-bottom:18px;">

            <label
                style="
                    display:block;
                    font-weight:700;
                    margin-bottom:8px;
                "
            >
                🗺️ Google Maps Link
            </label>

            <input
                type="url"
                name="maps_link"
                placeholder="https://maps.google.com/..."
                value="<?= htmlspecialchars($maps_link) ?>"
            >

        </div>


        <!-- ORGANIZER + CAPACITY -->

        <div
            style="
                display:grid;
                grid-template-columns:1fr 1fr;
                gap:18px;
                margin-bottom:18px;
            "
        >

            <div>

                <label
                    style="
                        display:block;
                        font-weight:700;
                        margin-bottom:8px;
                    "
                >
                    👤 Organizer Name *
                </label>

                <input
                    type="text"
                    name="organizer_name"
                    placeholder="Enter organizer name"
                    value="<?= htmlspecialchars($organizer_name) ?>"
                    required
                >

            </div>


            <div>

                <label
                    style="
                        display:block;
                        font-weight:700;
                        margin-bottom:8px;
                    "
                >
                    👥 Maximum Participants *
                </label>

                <input
                    type="number"
                    name="capacity"
                    min="1"
                    placeholder="e.g. 100"
                    value="<?= htmlspecialchars($capacity) ?>"
                    required
                >

            </div>

        </div>


        <!-- DEADLINE -->

        <div style="margin-bottom:18px;">

            <label
                style="
                    display:block;
                    font-weight:700;
                    margin-bottom:8px;
                "
            >
                ⏳ Registration Deadline
            </label>

            <input
                type="datetime-local"
                name="registration_deadline"
                value="<?= htmlspecialchars($registration_deadline) ?>"
            >

        </div>


        <!-- STATUS + TYPE -->

        <div
            style="
                display:grid;
                grid-template-columns:1fr 1fr;
                gap:18px;
                margin-bottom:22px;
            "
        >

            <div>

                <label
                    style="
                        display:block;
                        font-weight:700;
                        margin-bottom:8px;
                    "
                >
                    📊 Event Status
                </label>

                <select name="event_status">

                    <option value="Upcoming"
                        <?= $event_status === "Upcoming" ? "selected" : "" ?>>
                        Upcoming
                    </option>

                    <option value="Ongoing"
                        <?= $event_status === "Ongoing" ? "selected" : "" ?>>
                        Ongoing
                    </option>

                    <option value="Completed"
                        <?= $event_status === "Completed" ? "selected" : "" ?>>
                        Completed
                    </option>

                </select>

            </div>


            <div>

                <label
                    style="
                        display:block;
                        font-weight:700;
                        margin-bottom:8px;
                    "
                >
                    💳 Event Type
                </label>

                <select name="event_type">

                    <option value="Free"
                        <?= $event_type === "Free" ? "selected" : "" ?>>
                        Free
                    </option>

                    <option value="Paid"
                        <?= $event_type === "Paid" ? "selected" : "" ?>>
                        Paid
                    </option>

                </select>

            </div>

        </div>


        <!-- NOTIFICATION -->

        <div
            style="
                background:#f5f1ff;
                border:1px solid #e3d8ff;
                padding:20px;
                border-radius:14px;
                margin-bottom:25px;
            "
        >

            <div
                style="
                    font-size:24px;
                    margin-bottom:8px;
                "
            >
                📧
            </div>

            <label
                style="
                    display:block;
                    font-weight:700;
                    margin-bottom:8px;
                    color:#4c2aa8;
                "
            >
                Notification Email
            </label>

            <input
                type="email"
                name="notify_email"
                placeholder="example@gmail.com"
                value="<?= htmlspecialchars($notify_email) ?>"
            >

            <p
                style="
                    margin:8px 0 0;
                    font-size:13px;
                    color:#6b5a9b;
                "
            >
                Enter the email address where the event notification
                should be sent.
            </p>

        </div>


        <!-- BUTTONS -->

        <div
            style="
                display:flex;
                gap:12px;
                flex-wrap:wrap;
            "
        >

            <button
                type="submit"
                name="add_event"
                class="btn"
            >
                ✨ Create Event & Send Notification
            </button>


            <a
                href="events.php"
                class="btn"
                style="
                    background:#eef0f5;
                    color:#4b5563;
                    box-shadow:none;
                "
            >
                Cancel
            </a>

        </div>


    </form>

</div>


<!-- =========================
     LIVE PREVIEW
========================= -->

<div>

    <div
        class="card"
        style="
            position:sticky;
            top:20px;
        "
    >

        <div
            style="
                font-size:13px;
                font-weight:700;
                color:#718096;
                margin-bottom:8px;
                text-transform:uppercase;
                letter-spacing:.5px;
            "
        >
            Live Preview
        </div>

        <h2
            id="previewTitle"
            style="
                margin:0 0 12px;
            "
        >
            Your Event Name
        </h2>


        <div
            style="
                display:grid;
                gap:12px;
                color:#4a5568;
                font-size:14px;
            "
        >

            <div>
                📅
                <strong>Date:</strong>
                <span id="previewDate">Not selected</span>
            </div>

            <div>
                🕐
                <strong>Time:</strong>
                <span id="previewTime">Not selected</span>
            </div>

            <div>
                📍
                <strong>Venue:</strong>
                <span id="previewLocation">Not selected</span>
            </div>

            <div>
                🏷️
                <strong>Category:</strong>
                <span id="previewCategory">Not selected</span>
            </div>

            <div>
                👤
                <strong>Organizer:</strong>
                <span id="previewOrganizer">Not selected</span>
            </div>

            <div>
                👥
                <strong>Capacity:</strong>
                <span id="previewCapacity">Not selected</span>
            </div>

        </div>


        <div
            style="
                margin-top:20px;
                padding:14px;
                background:#f7fafc;
                border-radius:12px;
            "
        >

            <strong>Description</strong>

            <p
                id="previewDescription"
                style="
                    color:#718096;
                    margin:7px 0 0;
                    font-size:14px;
                "
            >
                Your event description will appear here.
            </p>

        </div>


        <div
            style="
                margin-top:18px;
                padding-top:15px;
                border-top:1px solid #edf2f7;
                font-size:13px;
                color:#718096;
            "
        >
            🎟️ Registration available
        </div>

    </div>

</div>


</div>


<!-- =========================
     LIVE PREVIEW SCRIPT
========================= -->

<script>

function updatePreview(inputId, previewId, defaultText = "Not selected") {

    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!input || !preview) return;

    input.addEventListener("input", function () {

        preview.textContent =
            this.value.trim() !== ""
            ? this.value
            : defaultText;

    });
}


updatePreview(
    "title",
    "previewTitle",
    "Your Event Name"
);

updatePreview(
    "description",
    "previewDescription",
    "Your event description will appear here."
);

updatePreview(
    "event_date",
    "previewDate"
);

updatePreview(
    "event_time",
    "previewTime"
);

updatePreview(
    "location",
    "previewLocation"
);

updatePreview(
    "organizer_name",
    "previewOrganizer"
);

updatePreview(
    "capacity",
    "previewCapacity"
);


const category =
    document.querySelector('[name="category"]');

if (category) {

    category.addEventListener("change", function () {

        document.getElementById("previewCategory").textContent =
            this.value || "Not selected";

    });

}


const description =
    document.getElementById("description");

const charCount =
    document.getElementById("charCount");

if (description && charCount) {

    function updateCharacterCount() {

        charCount.textContent =
            description.value.length;

    }

    description.addEventListener(
        "input",
        updateCharacterCount
    );

    updateCharacterCount();

}

</script>


<?php

require "footer.php";

?>