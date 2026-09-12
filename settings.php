<?php

require_once "db.php";

$page_title = "Settings";
$active_page = "settings";

$message = "";
$message_type = "";


/* =========================================================
   DEFAULT VALUES
========================================================= */

$website_name = "Event Management System";
$admin_name = "";
$admin_email = "";

$enable_notifications = 1;
$email_notifications = 1;
$sound_notifications = 1;
$light_notifications = 1;
$new_event_notifications = 1;
$registration_notifications = 1;
$attendance_notifications = 1;
$feedback_notifications = 1;
$system_notifications = 1;

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


/* =========================================================
   LOAD SETTINGS
========================================================= */

$load = $conn->query("
    SELECT
        website_name,
        admin_name,
        admin_email,

        enable_notifications,
        email_notifications,
        sound_notifications,
        light_notifications,
        new_event_notifications,
        registration_notifications,
        attendance_notifications,
        feedback_notifications,
        system_notifications,

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
    LIMIT 1
");


if ($load && $load->num_rows > 0) {

    $row = $load->fetch_assoc();

    $website_name = $row["website_name"] ?? $website_name;
    $admin_name = $row["admin_name"] ?? "";
    $admin_email = $row["admin_email"] ?? "";

    $enable_notifications =
        (int)($row["enable_notifications"] ?? 1);

    $email_notifications =
        (int)($row["email_notifications"] ?? 1);

    $sound_notifications =
        (int)($row["sound_notifications"] ?? 1);

    $light_notifications =
        (int)($row["light_notifications"] ?? 1);

    $new_event_notifications =
        (int)($row["new_event_notifications"] ?? 1);

    $registration_notifications =
        (int)($row["registration_notifications"] ?? 1);

    $attendance_notifications =
        (int)($row["attendance_notifications"] ?? 1);

    $feedback_notifications =
        (int)($row["feedback_notifications"] ?? 1);

    $system_notifications =
        (int)($row["system_notifications"] ?? 1);


    $sound_required =
        (int)($row["sound_required"] ?? 0);

    $microphone_required =
        (int)($row["microphone_required"] ?? 0);

    $speakers_required =
        (int)($row["speakers_required"] ?? 0);

    $microphone_count =
        max(1, (int)($row["microphone_count"] ?? 1));

    $sound_provider =
        $row["sound_provider"] ?? "";


    $lighting_required =
        (int)($row["lighting_required"] ?? 0);

    $stage_lighting =
        (int)($row["stage_lighting"] ?? 0);

    $decorative_lighting =
        (int)($row["decorative_lighting"] ?? 0);

    $emergency_lighting =
        (int)($row["emergency_lighting"] ?? 0);

    $lighting_provider =
        $row["lighting_provider"] ?? "";
}


/* =========================================================
   SAVE SETTINGS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $website_name =
        trim($_POST["website_name"] ?? "");

    $admin_name =
        trim($_POST["admin_name"] ?? "");

    $admin_email =
        trim($_POST["admin_email"] ?? "");


    $enable_notifications =
        isset($_POST["enable_notifications"]) ? 1 : 0;

    $email_notifications =
        isset($_POST["email_notifications"]) ? 1 : 0;

    $sound_notifications =
        isset($_POST["sound_notifications"]) ? 1 : 0;

    $light_notifications =
        isset($_POST["light_notifications"]) ? 1 : 0;

    $new_event_notifications =
        isset($_POST["new_event_notifications"]) ? 1 : 0;

    $registration_notifications =
        isset($_POST["registration_notifications"]) ? 1 : 0;

    $attendance_notifications =
        isset($_POST["attendance_notifications"]) ? 1 : 0;

    $feedback_notifications =
        isset($_POST["feedback_notifications"]) ? 1 : 0;

    $system_notifications =
        isset($_POST["system_notifications"]) ? 1 : 0;


    $sound_required =
        isset($_POST["sound_required"]) ? 1 : 0;

    $microphone_required =
        isset($_POST["microphone_required"]) ? 1 : 0;

    $speakers_required =
        isset($_POST["speakers_required"]) ? 1 : 0;

    $microphone_count =
        max(
            1,
            (int)($_POST["microphone_count"] ?? 1)
        );

    $sound_provider =
        trim($_POST["sound_provider"] ?? "");


    $lighting_required =
        isset($_POST["lighting_required"]) ? 1 : 0;

    $stage_lighting =
        isset($_POST["stage_lighting"]) ? 1 : 0;

    $decorative_lighting =
        isset($_POST["decorative_lighting"]) ? 1 : 0;

    $emergency_lighting =
        isset($_POST["emergency_lighting"]) ? 1 : 0;

    $lighting_provider =
        trim($_POST["lighting_provider"] ?? "");


    /* =====================================================
       BASIC VALIDATION
    ===================================================== */

    if ($website_name === "") {

        $message = "Website name is required.";
        $message_type = "danger";

    } elseif (
        $admin_email !== "" &&
        !filter_var($admin_email, FILTER_VALIDATE_EMAIL)
    ) {

        $message = "Please enter a valid admin email address.";
        $message_type = "danger";

    } else {


        /* =================================================
           UPDATE DATABASE
           
           TOTAL:
           3 strings
           9 notification integers
           4 sound integers
           1 sound string
           4 lighting integers
           1 lighting string

           TOTAL = 22 VARIABLES
        ================================================= */


        $sql = "
            UPDATE settings
            SET

                website_name = ?,
                admin_name = ?,
                admin_email = ?,

                enable_notifications = ?,
                email_notifications = ?,
                sound_notifications = ?,
                light_notifications = ?,
                new_event_notifications = ?,
                registration_notifications = ?,
                attendance_notifications = ?,
                feedback_notifications = ?,
                system_notifications = ?,

                sound_required = ?,
                microphone_required = ?,
                speakers_required = ?,
                microphone_count = ?,
                sound_provider = ?,

                lighting_required = ?,
                stage_lighting = ?,
                decorative_lighting = ?,
                emergency_lighting = ?,
                lighting_provider = ?,

                updated_at = CURRENT_TIMESTAMP

            WHERE id = 1
        ";


        $stmt = $conn->prepare($sql);


        if (!$stmt) {

            $message =
                "Database error while preparing settings: " .
                $conn->error;

            $message_type = "danger";

        } else {


            /* =============================================
               ALL 22 VALUES
               
               Put them into an array first.
               This prevents accidental missing variables.
            ============================================= */

            $values = [

                $website_name,
                $admin_name,
                $admin_email,

                $enable_notifications,
                $email_notifications,
                $sound_notifications,
                $light_notifications,
                $new_event_notifications,
                $registration_notifications,
                $attendance_notifications,
                $feedback_notifications,
                $system_notifications,

                $sound_required,
                $microphone_required,
                $speakers_required,
                $microphone_count,

                $sound_provider,

                $lighting_required,
                $stage_lighting,
                $decorative_lighting,
                $emergency_lighting,

                $lighting_provider
            ];


            /* =============================================
               EXACT TYPE STRING

               22 VALUES:

               1  s
               2  s
               3  s

               4  i
               5  i
               6  i
               7  i
               8  i
               9  i
               10 i
               11 i
               12 i

               13 i
               14 i
               15 i
               16 i

               17 s

               18 i
               19 i
               20 i
               21 i

               22 s
            ============================================= */

            $types =
                "sss" .
                "iiiiiiiii" .
                "iiii" .
                "s" .
                "iiii" .
                "s";


            /* =============================================
               SAFETY CHECK
            ============================================= */

            if (
                strlen($types) !== count($values)
            ) {

                $message =
                    "Internal settings error: parameter count mismatch.";

                $message_type = "danger";

                $stmt->close();

            } else {


                /* =========================================
                   bind_param NEEDS REFERENCES

                   Create references dynamically so PHP
                   receives exactly the same number of
                   parameters as the type string.
                ========================================= */

                $bind = [];

                $bind[] = $types;

                foreach ($values as $key => $value) {

                    $bind[] = &$values[$key];
                }


                call_user_func_array(
                    [$stmt, "bind_param"],
                    $bind
                );


                /* =========================================
                   EXECUTE
                ========================================= */

                if ($stmt->execute()) {

                    $message =
                        "Settings saved successfully.";

                    $message_type = "success";

                } else {

                    $message =
                        "Unable to save settings: " .
                        $stmt->error;

                    $message_type = "danger";
                }


                $stmt->close();
            }
        }
    }
}


/* =========================================================
   HEADER
========================================================= */

require "header.php";

?>

<style>

/* =========================================================
   SETTINGS PAGE
========================================================= */

.settings-page {
    max-width: 1150px;
    margin: 0 auto;
    padding-bottom: 50px;
}


/* =========================================================
   HERO
========================================================= */

.settings-hero {
    position: relative;
    overflow: hidden;

    padding: 30px;
    margin-bottom: 24px;

    border-radius: 24px;

    color: #fff;

    background:
        radial-gradient(
            circle at 85% 15%,
            rgba(255,255,255,.16),
            transparent 28%
        ),
        linear-gradient(
            135deg,
            #17112f,
            #48239b,
            #713bf2
        );

    box-shadow:
        0 18px 45px rgba(72,42,170,.20);
}

.settings-hero::after {
    content: "";

    position: absolute;

    width: 210px;
    height: 210px;

    right: -75px;
    top: -95px;

    border-radius: 50%;

    background:
        rgba(255,255,255,.08);
}

.settings-hero-content {
    position: relative;
    z-index: 2;
}

.settings-label {
    color: rgba(255,255,255,.65);

    font-size: 11px;
    font-weight: 800;

    letter-spacing: 1.7px;

    margin-bottom: 9px;
}

.settings-hero h1 {
    margin: 0 0 9px;

    color: #fff;

    font-size: 32px;
}

.settings-hero p {
    max-width: 650px;

    color: rgba(255,255,255,.75);

    font-size: 13px;

    line-height: 1.6;
}


/* =========================================================
   ALERT
========================================================= */

.settings-alert {
    padding: 14px 17px;

    margin-bottom: 22px;

    border-radius: 13px;

    font-size: 13px;
    font-weight: 700;
}

.settings-alert.success {
    color: #176b3c;

    background: #eaf8ef;

    border: 1px solid #ccefd9;
}

.settings-alert.danger {
    color: #b42335;

    background: #fff0f2;

    border: 1px solid #ffd7dc;
}


/* =========================================================
   GRID
========================================================= */

.settings-grid {
    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        minmax(0, 1fr);

    gap: 22px;
}


/* =========================================================
   CARD
========================================================= */

.settings-card {
    padding: 26px;

    background:
        rgba(255,255,255,.95);

    border:
        1px solid #e7e9f2;

    border-radius: 20px;

    box-shadow:
        0 14px 35px
        rgba(40,45,85,.07);
}

.settings-card.full {
    grid-column: 1 / -1;
}

.settings-card-header {
    display: flex;

    align-items: center;

    gap: 13px;

    margin-bottom: 23px;
}

.settings-card-icon {
    width: 45px;
    height: 45px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background: #eee9ff;

    font-size: 20px;
}

.settings-card-header h2 {
    margin: 0;

    font-size: 19px;

    font-weight: 800;
}

.settings-card-header p {
    margin-top: 3px;

    color: #8a93a8;

    font-size: 12px;
}


/* =========================================================
   FIELDS
========================================================= */

.settings-field {
    margin-bottom: 18px;
}

.settings-field:last-child {
    margin-bottom: 0;
}

.settings-field label {
    display: block;

    margin-bottom: 8px;

    color: #29334a;

    font-size: 12px;

    font-weight: 700;
}

.settings-field input,
.settings-field select {
    width: 100%;

    height: 46px;

    margin: 0;

    padding: 11px 13px;

    border:
        1px solid #dfe3ee;

    border-radius: 11px;

    background: #fbfbff;

    color: #172033;

    outline: none;

    font-family: inherit;

    box-sizing: border-box;
}

.settings-field input:focus,
.settings-field select:focus {
    border-color: #8055f5;

    box-shadow:
        0 0 0 4px
        rgba(109,53,242,.10);
}


/* =========================================================
   TOGGLE ROW
========================================================= */

.toggle-list {
    display: grid;

    gap: 11px;
}

.toggle-row {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 15px;

    padding: 13px 14px;

    background: #fafaff;

    border:
        1px solid #eceef5;

    border-radius: 13px;
}

.toggle-info {
    display: flex;

    align-items: center;

    gap: 11px;

    min-width: 0;
}

.toggle-icon {
    width: 35px;
    height: 35px;

    display: flex;

    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    border-radius: 10px;

    background: #eee9ff;

    font-size: 16px;
}

.toggle-title {
    color: #29334a;

    font-size: 12px;

    font-weight: 700;
}

.toggle-description {
    margin-top: 2px;

    color: #929bad;

    font-size: 10px;
}


/* =========================================================
   SWITCH
========================================================= */

.switch {
    position: relative;

    width: 47px;
    height: 25px;

    flex-shrink: 0;
}

.switch input {
    opacity: 0;

    width: 0;
    height: 0;
}

.slider {
    position: absolute;

    inset: 0;

    cursor: pointer;

    border-radius: 999px;

    background: #d8dce7;

    transition: .2s;
}

.slider::before {
    content: "";

    position: absolute;

    width: 19px;
    height: 19px;

    left: 3px;
    top: 3px;

    border-radius: 50%;

    background: #fff;

    box-shadow:
        0 2px 5px
        rgba(0,0,0,.18);

    transition: .2s;
}

.switch input:checked + .slider {
    background:
        linear-gradient(
            135deg,
            #7b42f5,
            #5928dd
        );
}

.switch input:checked + .slider::before {
    transform: translateX(22px);
}


/* =========================================================
   EQUIPMENT
========================================================= */

.equipment-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 15px;
}

.equipment-row {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 12px;

    padding: 13px 14px;

    border:
        1px solid #eceef5;

    border-radius: 12px;

    background: #fafaff;
}

.equipment-name {
    color: #29334a;

    font-size: 12px;

    font-weight: 700;
}

.equipment-small {
    margin-top: 3px;

    color: #929bad;

    font-size: 10px;
}


/* =========================================================
   SUB FIELDS
========================================================= */

.sub-fields {
    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 15px;

    margin-top: 17px;
}


/* =========================================================
   SAVE AREA
========================================================= */

.settings-save {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 15px;

    margin-top: 22px;

    padding: 18px 21px;

    background: #fff;

    border:
        1px solid #e7e9f2;

    border-radius: 17px;

    box-shadow:
        0 10px 28px
        rgba(40,45,85,.06);
}

.save-info strong {
    display: block;

    color: #172033;

    font-size: 13px;
}

.save-info span {
    display: block;

    margin-top: 3px;

    color: #8a93a8;

    font-size: 11px;
}

.save-button {
    min-width: 165px;

    height: 47px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 7px;

    border: 0;

    border-radius: 12px;

    color: #fff;

    background:
        linear-gradient(
            135deg,
            #7438f4,
            #5827dc
        );

    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 10px 22px
        rgba(94,43,221,.22);

    transition: .2s;
}

.save-button:hover {
    transform: translateY(-2px);

    box-shadow:
        0 14px 28px
        rgba(94,43,221,.30);
}


/* =========================================================
   DARK MODE
========================================================= */

body.dark-mode {
    --bg: #0b1020;
    --card: #12182b;
    --text: #edf1ff;
    --muted: #9aa4bd;
    --border: #27304a;

    background:
        radial-gradient(
            circle at 80% 5%,
            rgba(109,53,242,.20),
            transparent 30%
        ),
        #0b1020;

    color: #edf1ff;
}

body.dark-mode .settings-card,
body.dark-mode .settings-save {
    background: #12182b;

    border-color: #27304a;
}

body.dark-mode .settings-card-header h2,
body.dark-mode .toggle-title,
body.dark-mode .equipment-name,
body.dark-mode .save-info strong {
    color: #edf1ff;
}

body.dark-mode .settings-card-header p,
body.dark-mode .toggle-description,
body.dark-mode .equipment-small,
body.dark-mode .save-info span {
    color: #9aa4bd;
}

body.dark-mode .toggle-row,
body.dark-mode .equipment-row {
    background: #0f1527;

    border-color: #27304a;
}

body.dark-mode .settings-field label {
    color: #dbe2f4;
}

body.dark-mode .settings-field input,
body.dark-mode .settings-field select {
    color: #edf1ff;

    background: #0f1527;

    border-color: #303a56;
}

body.dark-mode .settings-card-icon,
body.dark-mode .toggle-icon {
    background: #211a42;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 850px) {

    .settings-grid {
        grid-template-columns: 1fr;
    }

    .settings-card.full {
        grid-column: auto;
    }

    .equipment-grid {
        grid-template-columns: 1fr;
    }
}


@media (max-width: 600px) {

    .settings-page {
        padding: 0 2px 35px;
    }

    .settings-hero {
        padding: 23px;
    }

    .settings-hero h1 {
        font-size: 27px;
    }

    .settings-card {
        padding: 19px;
    }

    .sub-fields {
        grid-template-columns: 1fr;
    }

    .settings-save {
        align-items: stretch;

        flex-direction: column;
    }

    .save-button {
        width: 100%;
    }
}

</style>


<div class="settings-page">


    <!-- =====================================================
         HERO
    ===================================================== -->

    <section class="settings-hero">

        <div class="settings-hero-content">

            <div class="settings-label">
                SYSTEM CONFIGURATION
            </div>

            <h1>
                ⚙️ Settings
            </h1>

            <p>
                Manage your event management system,
                notifications, sound requirements,
                lighting requirements and administrator details.
            </p>

        </div>

    </section>


    <!-- =====================================================
         ALERT
    ===================================================== -->

    <?php if ($message !== ""): ?>

        <div
            class="settings-alert <?= htmlspecialchars($message_type) ?>"
        >
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <!-- =====================================================
         SETTINGS FORM
    ===================================================== -->

    <form method="POST" action="settings.php">

        <div class="settings-grid">


            <!-- =============================================
                 WEBSITE SETTINGS
            ============================================== -->

            <section class="settings-card">

                <div class="settings-card-header">

                    <div class="settings-card-icon">
                        🌐
                    </div>

                    <div>

                        <h2>
                            Website Settings
                        </h2>

                        <p>
                            Basic website information
                        </p>

                    </div>

                </div>


                <div class="settings-field">

                    <label>
                        Website Name
                    </label>

                    <input
                        type="text"
                        name="website_name"
                        value="<?= htmlspecialchars($website_name) ?>"
                        required
                    >

                </div>


                <div class="settings-field">

                    <label>
                        Admin Name
                    </label>

                    <input
                        type="text"
                        name="admin_name"
                        value="<?= htmlspecialchars($admin_name) ?>"
                    >

                </div>


                <div class="settings-field">

                    <label>
                        Admin Email
                    </label>

                    <input
                        type="email"
                        name="admin_email"
                        value="<?= htmlspecialchars($admin_email) ?>"
                    >

                </div>

            </section>


            <!-- =============================================
                 NOTIFICATIONS
            ============================================== -->

            <section class="settings-card">

                <div class="settings-card-header">

                    <div class="settings-card-icon">
                        🔔
                    </div>

                    <div>

                        <h2>
                            Notifications
                        </h2>

                        <p>
                            Control system notification alerts
                        </p>

                    </div>

                </div>


                <div class="toggle-list">


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                🔔
                            </div>

                            <div>

                                <div class="toggle-title">
                                    Enable Notifications
                                </div>

                                <div class="toggle-description">
                                    Master notification switch
                                </div>

                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="enable_notifications"
                                <?= $enable_notifications ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                📧
                            </div>

                            <div>

                                <div class="toggle-title">
                                    Email Notifications
                                </div>

                                <div class="toggle-description">
                                    Receive email alerts
                                </div>

                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="email_notifications"
                                <?= $email_notifications ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                🔊
                            </div>

                            <div>

                                <div class="toggle-title">
                                    Sound Notifications
                                </div>

                                <div class="toggle-description">
                                    Play notification sounds
                                </div>

                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="sound_notifications"
                                <?= $sound_notifications ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                💡
                            </div>

                            <div>

                                <div class="toggle-title">
                                    Light Notifications
                                </div>

                                <div class="toggle-description">
                                    Enable visual notification alerts
                                </div>

                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="light_notifications"
                                <?= $light_notifications ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>

                </div>

            </section>


            <!-- =============================================
                 EVENT NOTIFICATIONS
            ============================================== -->

            <section class="settings-card full">

                <div class="settings-card-header">

                    <div class="settings-card-icon">
                        🗓️
                    </div>

                    <div>

                        <h2>
                            Event Notifications
                        </h2>

                        <p>
                            Choose which event activities should trigger alerts
                        </p>

                    </div>

                </div>


                <div class="toggle-list">


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                🗓️
                            </div>

                            <div>

                                <div class="toggle-title">
                                    New Event Notifications
                                </div>

                                <div class="toggle-description">
                                    Notify when a new event is created
                                </div>

                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="new_event_notifications"
                                <?= $new_event_notifications ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                🎟️
                            </div>

                            <div>

                                <div class="toggle-title">
                                    Registration Notifications
                                </div>

                                <div class="toggle-description">
                                    Notify when someone registers
                                </div>

                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="registration_notifications"
                                <?= $registration_notifications ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                ✅
                            </div>

                            <div>

                                <div class="toggle-title">
                                    Attendance Notifications
                                </div>

                                <div class="toggle-description">
                                    Notify about attendance updates
                                </div>

                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="attendance_notifications"
                                <?= $attendance_notifications ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                ⭐
                            </div>

                            <div>

                                <div class="toggle-title">
                                    Feedback Notifications
                                </div>

                                <div class="toggle-description">
                                    Notify about new feedback
                                </div>

                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="feedback_notifications"
                                <?= $feedback_notifications ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                ⚙️
                            </div>

                            <div>

                                <div class="toggle-title">
                                    System Notifications
                                </div>

                                <div class="toggle-description">
                                    System and administrative alerts
                                </div>

                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="system_notifications"
                                <?= $system_notifications ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>

                </div>

            </section>


            <!-- =============================================
                 SOUND SYSTEM
            ============================================== -->

            <section class="settings-card">

                <div class="settings-card-header">

                    <div class="settings-card-icon">
                        🔊
                    </div>

                    <div>

                        <h2>
                            Sound System
                        </h2>

                        <p>
                            Configure event sound equipment
                        </p>

                    </div>

                </div>


                <div class="toggle-list">


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                🔊
                            </div>

                            <div class="toggle-title">
                                Sound System Required
                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="sound_required"
                                <?= $sound_required ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                🎤
                            </div>

                            <div class="toggle-title">
                                Microphone Required
                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="microphone_required"
                                <?= $microphone_required ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                🔊
                            </div>

                            <div class="toggle-title">
                                Speakers Required
                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="speakers_required"
                                <?= $speakers_required ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>

                </div>


                <div class="sub-fields">

                    <div class="settings-field">

                        <label>
                            Number of Microphones
                        </label>

                        <input
                            type="number"
                            name="microphone_count"
                            min="1"
                            value="<?= (int)$microphone_count ?>"
                        >

                    </div>


                    <div class="settings-field">

                        <label>
                            Sound Provider / Details
                        </label>

                        <input
                            type="text"
                            name="sound_provider"
                            value="<?= htmlspecialchars($sound_provider) ?>"
                            placeholder="Provider or equipment details"
                        >

                    </div>

                </div>

            </section>


            <!-- =============================================
                 LIGHTING SYSTEM
            ============================================== -->

            <section class="settings-card">

                <div class="settings-card-header">

                    <div class="settings-card-icon">
                        💡
                    </div>

                    <div>

                        <h2>
                            Lighting System
                        </h2>

                        <p>
                            Configure event lighting requirements
                        </p>

                    </div>

                </div>


                <div class="toggle-list">


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                💡
                            </div>

                            <div class="toggle-title">
                                Lighting System Required
                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="lighting_required"
                                <?= $lighting_required ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                🎭
                            </div>

                            <div class="toggle-title">
                                Stage Lighting
                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="stage_lighting"
                                <?= $stage_lighting ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                ✨
                            </div>

                            <div class="toggle-title">
                                Decorative Lighting
                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="decorative_lighting"
                                <?= $decorative_lighting ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>


                    <div class="toggle-row">

                        <div class="toggle-info">

                            <div class="toggle-icon">
                                🚨
                            </div>

                            <div class="toggle-title">
                                Emergency Lighting
                            </div>

                        </div>

                        <label class="switch">

                            <input
                                type="checkbox"
                                name="emergency_lighting"
                                <?= $emergency_lighting ? "checked" : "" ?>
                            >

                            <span class="slider"></span>

                        </label>

                    </div>

                </div>


                <div
                    class="settings-field"
                    style="margin-top:17px;"
                >

                    <label>
                        Lighting Provider / Details
                    </label>

                    <input
                        type="text"
                        name="lighting_provider"
                        value="<?= htmlspecialchars($lighting_provider) ?>"
                        placeholder="Provider or lighting details"
                    >

                </div>

            </section>


            <!-- =============================================
                 APPEARANCE
            ============================================== -->

            <section class="settings-card full">

                <div class="settings-card-header">

                    <div class="settings-card-icon">
                        🎨
                    </div>

                    <div>

                        <h2>
                            Appearance
                        </h2>

                        <p>
                            Choose your preferred dashboard appearance
                        </p>

                    </div>

                </div>


                <div class="toggle-row">

                    <div class="toggle-info">

                        <div class="toggle-icon">
                            🌙
                        </div>

                        <div>

                            <div class="toggle-title">
                                Dark Mode
                            </div>

                            <div class="toggle-description">
                                Switch the settings interface to dark appearance
                            </div>

                        </div>

                    </div>


                    <label class="switch">

                        <input
                            type="checkbox"
                            id="darkModeToggle"
                        >

                        <span class="slider"></span>

                    </label>

                </div>

            </section>

        </div>


        <!-- =================================================
             SAVE
        ================================================= -->

        <div class="settings-save">

            <div class="save-info">

                <strong>
                    Save your configuration
                </strong>

                <span>
                    Changes will be applied to your Event Management System.
                </span>

            </div>


            <button
                type="submit"
                class="save-button"
            >
                💾 Save Settings
            </button>

        </div>

    </form>

</div>


<script>

/* =========================================================
   DARK MODE
========================================================= */

(function () {

    const darkModeToggle =
        document.getElementById("darkModeToggle");

    const savedTheme =
        localStorage.getItem("eventManagerDarkMode");


    if (savedTheme === "1") {

        document.body.classList.add("dark-mode");

        if (darkModeToggle) {
            darkModeToggle.checked = true;
        }

    }


    if (darkModeToggle) {

        darkModeToggle.addEventListener(
            "change",
            function () {

                if (this.checked) {

                    document.body.classList.add(
                        "dark-mode"
                    );

                    localStorage.setItem(
                        "eventManagerDarkMode",
                        "1"
                    );

                } else {

                    document.body.classList.remove(
                        "dark-mode"
                    );

                    localStorage.setItem(
                        "eventManagerDarkMode",
                        "0"
                    );

                }

            }
        );

    }

})();

</script>


</main>

</div>

</body>

</html>