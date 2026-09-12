<?php

require_once "db.php";

$page_title = "Attendance";
$active_page = "attendance";

$message = "";
$type = "success";


/* =========================================================
   SAVE / UPDATE ATTENDANCE
========================================================= */

if (isset($_POST["save_attendance"])) {

    $registration_id = (int)($_POST["registration_id"] ?? 0);
    $status = trim($_POST["status"] ?? "");

    if ($registration_id <= 0) {

        $message = "Please select a registration.";
        $type = "danger";

    } elseif (!in_array($status, ["Present", "Absent"], true)) {

        $message = "Please select a valid attendance status.";
        $type = "danger";

    } else {

        /* Check existing attendance */

        $checkStmt = $conn->prepare(
            "SELECT id
             FROM attendance
             WHERE registration_id = ?"
        );

        if (!$checkStmt) {

            $message = "Database error: " . $conn->error;
            $type = "danger";

        } else {

            $checkStmt->bind_param("i", $registration_id);
            $checkStmt->execute();

            $checkResult = $checkStmt->get_result();


            if ($checkResult && $checkResult->num_rows > 0) {

                $existing = $checkResult->fetch_assoc();
                $attendance_id = (int)$existing["id"];


                /* UPDATE */

                $updateStmt = $conn->prepare(
                    "UPDATE attendance
                     SET status = ?, marked_at = NOW()
                     WHERE id = ?"
                );

                if ($updateStmt) {

                    $updateStmt->bind_param(
                        "si",
                        $status,
                        $attendance_id
                    );

                    if ($updateStmt->execute()) {

                        $message = "Attendance updated successfully.";
                        $type = "success";

                    } else {

                        $message =
                            "Attendance update failed: "
                            . $updateStmt->error;

                        $type = "danger";
                    }

                    $updateStmt->close();

                } else {

                    $message = "Database error: " . $conn->error;
                    $type = "danger";
                }


            } else {

                /* INSERT */

                $insertStmt = $conn->prepare(
                    "INSERT INTO attendance
                    (registration_id, status, marked_at)
                    VALUES (?, ?, NOW())"
                );

                if ($insertStmt) {

                    $insertStmt->bind_param(
                        "is",
                        $registration_id,
                        $status
                    );

                    if ($insertStmt->execute()) {

                        $message = "Attendance saved successfully.";
                        $type = "success";

                    } else {

                        $message =
                            "Attendance save failed: "
                            . $insertStmt->error;

                        $type = "danger";
                    }

                    $insertStmt->close();

                } else {

                    $message = "Database error: " . $conn->error;
                    $type = "danger";
                }
            }

            $checkStmt->close();
        }
    }
}


/* =========================================================
   GET REGISTRATIONS FOR DROPDOWN
========================================================= */

$registrationList = [];

$registrationQuery = "
    SELECT
        r.id AS registration_id,
        u.name AS user_name,
        u.email,
        e.title AS event_title,
        e.event_date,
        e.event_time,
        e.location
    FROM registrations r

    INNER JOIN users u
        ON r.user_id = u.id

    INNER JOIN events e
        ON r.event_id = e.id

    ORDER BY
        e.event_date ASC,
        e.event_time ASC,
        u.name ASC
";


$registrationResult = $conn->query($registrationQuery);


if ($registrationResult) {

    while ($row = $registrationResult->fetch_assoc()) {

        $registrationList[] = $row;
    }

} else {

    $message =
        "Registration list could not be loaded: "
        . $conn->error;

    $type = "danger";
}


/* =========================================================
   GET ATTENDANCE RECORDS
========================================================= */

$attendanceQuery = "
    SELECT
        r.id AS registration_id,

        u.name AS user_name,
        u.email,

        e.title AS event_title,
        e.event_date,
        e.event_time,
        e.location,

        a.status AS attendance_status,
        a.marked_at

    FROM registrations r

    INNER JOIN users u
        ON r.user_id = u.id

    INNER JOIN events e
        ON r.event_id = e.id

    LEFT JOIN attendance a
        ON a.registration_id = r.id

    ORDER BY
        e.event_date ASC,
        e.event_time ASC,
        r.id DESC
";


$attendanceResult = $conn->query($attendanceQuery);


/* =========================================================
   HEADER
========================================================= */

require "header.php";

?>


<style>

/* =========================================================
   ATTENDANCE PAGE
========================================================= */

.attendance-page {
    max-width: 1200px;
    margin: 0 auto;
}


/* HEADER */

.attendance-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 25px;
    margin-bottom: 25px;
}

.attendance-hero h1 {
    margin: 0;
    font-size: 32px;
    font-weight: 800;
    color: #182033;
}

.attendance-hero p {
    margin: 8px 0 0;
    color: #718096;
}


/* BADGE */

.attendance-badge {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 14px 18px;

    background: linear-gradient(
        135deg,
        #f3edff,
        #ffffff
    );

    border: 1px solid #e5dbff;

    border-radius: 16px;

    box-shadow:
        0 12px 30px rgba(91,53,197,.10);
}

.attendance-badge-icon {
    width: 45px;
    height: 45px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;

    background: linear-gradient(
        135deg,
        #6d28d9,
        #8b5cf6
    );

    color: white;
    font-size: 21px;

    box-shadow:
        0 8px 18px rgba(109,40,217,.25);
}

.attendance-badge small {
    display: block;
    color: #7c6f95;
    font-size: 11px;
}

.attendance-badge strong {
    display: block;
    color: #4c1d95;
    margin-top: 3px;
}


/* ALERT */

.attendance-alert {
    padding: 15px 18px;
    margin-bottom: 22px;

    border-radius: 13px;

    font-weight: 700;
}

.attendance-alert.success {
    background: #ecfdf3;
    border: 1px solid #bbf7d0;
    color: #15803d;
}

.attendance-alert.danger {
    background: #fff1f2;
    border: 1px solid #fecdd3;
    color: #be123c;
}


/* FORM CARD */

.attendance-form-card {
    padding: 28px;
    margin-bottom: 25px;

    border-radius: 22px;

    background: rgba(255,255,255,.97);

    border: 1px solid #e5def1;

    box-shadow:
        0 20px 45px rgba(54,38,91,.10);
}

.attendance-title {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 25px;
}

.attendance-title-icon {
    width: 48px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 15px;

    background: linear-gradient(
        135deg,
        #ede9fe,
        #f5f3ff
    );

    font-size: 23px;
}

.attendance-title h2 {
    margin: 0;
    color: #182033;
    font-size: 22px;
}

.attendance-title p {
    margin: 5px 0 0;
    color: #718096;
    font-size: 13px;
}


/* FIELD */

.attendance-field {
    margin-bottom: 20px;
}

.attendance-field label {
    display: block;
    margin-bottom: 8px;

    color: #344054;

    font-size: 13px;
    font-weight: 800;
}

.attendance-field select {
    width: 100%;

    padding: 14px 15px;

    border-radius: 12px;

    border: 1px solid #ddd6ee;

    background: #fcfbff;

    color: #172033;

    font-size: 14px;

    outline: none;

    cursor: pointer;
}

.attendance-field select:focus {
    border-color: #8b5cf6;

    background: white;

    box-shadow:
        0 0 0 4px rgba(139,92,246,.12);
}


/* SUBMIT */

.attendance-submit {
    border: 0;

    padding: 13px 22px;

    border-radius: 12px;

    background: linear-gradient(
        135deg,
        #6d28d9,
        #7c3aed
    );

    color: white;

    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 10px 22px rgba(109,40,217,.25);

    transition: .2s;
}

.attendance-submit:hover {
    transform: translateY(-2px);

    box-shadow:
        0 14px 28px rgba(109,40,217,.32);
}


/* RECORDS */

.attendance-records {
    padding: 28px;

    border-radius: 22px;

    background: rgba(255,255,255,.97);

    border: 1px solid #e5def1;

    box-shadow:
        0 20px 45px rgba(54,38,91,.09);
}

.records-heading {
    margin-bottom: 20px;
}

.records-heading h2 {
    margin: 0;

    color: #182033;

    font-size: 22px;
}

.records-heading p {
    margin: 6px 0 0;

    color: #718096;

    font-size: 13px;
}


/* TABLE */

.attendance-table-wrap {
    width: 100%;

    overflow-x: auto;

    border-radius: 15px;

    border: 1px solid #e9e4f2;
}

.attendance-table {
    width: 100%;

    border-collapse: collapse;

    min-width: 950px;
}

.attendance-table th {
    padding: 15px;

    text-align: left;

    background: #f8f7fc;

    color: #667085;

    font-size: 11px;

    text-transform: uppercase;

    letter-spacing: .5px;
}

.attendance-table td {
    padding: 15px;

    border-top: 1px solid #eeeaf5;

    color: #344054;

    font-size: 13px;
}

.attendance-table tbody tr:hover {
    background: #faf9ff;
}


/* USER */

.attendance-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.attendance-avatar {
    width: 38px;
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: linear-gradient(
        135deg,
        #6d28d9,
        #8b5cf6
    );

    color: white;

    font-weight: 800;
}


/* STATUS */

.status-badge {
    display: inline-flex;
    align-items: center;

    padding: 7px 11px;

    border-radius: 999px;

    font-size: 11px;

    font-weight: 800;
}

.status-present {
    background: #dcfce7;
    color: #15803d;
}

.status-absent {
    background: #fee2e2;
    color: #b91c1c;
}

.status-pending {
    background: #f3f4f6;
    color: #6b7280;
}


/* NO REGISTRATION */

.no-registration {
    margin-top: 12px;

    padding: 14px 16px;

    border-radius: 12px;

    background: #fff7ed;

    border: 1px solid #fed7aa;

    color: #c2410c;

    font-size: 13px;

    font-weight: 600;
}


/* EMPTY */

.attendance-empty {
    text-align: center;

    padding: 55px 20px;

    color: #718096;
}

.attendance-empty-icon {
    font-size: 45px;
    margin-bottom: 10px;
}


/* RESPONSIVE */

@media (max-width: 800px) {

    .attendance-hero {
        flex-direction: column;
        align-items: flex-start;
    }

    .attendance-badge {
        width: 100%;
    }

    .attendance-form-card,
    .attendance-records {
        padding: 20px;
    }
}

</style>


<div class="attendance-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="attendance-hero">

        <div>

            <h1>
                Mark Attendance
            </h1>

            <p>
                Track attendance of registered participants for your events.
            </p>

        </div>


        <div class="attendance-badge">

            <div class="attendance-badge-icon">
                ✓
            </div>

            <div>

                <small>
                    ATTENDANCE MANAGEMENT
                </small>

                <strong>
                    Keep Records Updated
                </strong>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MESSAGE
    ====================================================== -->

    <?php if ($message): ?>

        <div class="attendance-alert <?= htmlspecialchars($type) ?>">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         FORM
    ====================================================== -->

    <section class="attendance-form-card">

        <div class="attendance-title">

            <div class="attendance-title-icon">
                📝
            </div>

            <div>

                <h2>
                    Mark Participant Attendance
                </h2>

                <p>
                    Select a registered participant and update their attendance.
                </p>

            </div>

        </div>


        <form method="POST" action="">


            <!-- REGISTRATION -->

            <div class="attendance-field">

                <label>
                    👤 Select Registration
                </label>


                <select
                    name="registration_id"
                    required
                >

                    <option value="">
                        Choose participant / event...
                    </option>


                    <?php if (count($registrationList) > 0): ?>

                        <?php foreach ($registrationList as $registration): ?>

                            <option
                                value="<?= (int)$registration["registration_id"] ?>"
                            >

                                <?= htmlspecialchars($registration["user_name"]) ?>

                                —
                                <?= htmlspecialchars($registration["event_title"]) ?>

                                —
                                <?= htmlspecialchars($registration["event_date"]) ?>

                            </option>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <option value="" disabled>
                            No registered participants available
                        </option>

                    <?php endif; ?>

                </select>


                <?php if (count($registrationList) === 0): ?>

                    <div class="no-registration">

                        ⚠️ No registrations found.

                        First go to
                        <strong>Register Event</strong>
                        and register a participant for an event.

                    </div>

                <?php endif; ?>

            </div>


            <!-- STATUS -->

            <div class="attendance-field">

                <label>
                    📋 Attendance Status
                </label>

                <select
                    name="status"
                    required
                >

                    <option value="Present">
                        ✓ Present
                    </option>

                    <option value="Absent">
                        ✕ Absent
                    </option>

                </select>

            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                name="save_attendance"
                class="attendance-submit"
            >

                ✓ Save Attendance

            </button>

        </form>

    </section>


    <!-- =====================================================
         RECORDS
    ====================================================== -->

    <section class="attendance-records">

        <div class="records-heading">

            <h2>
                Attendance Records
            </h2>

            <p>
                View all registered participants and their attendance status.
            </p>

        </div>


        <div class="attendance-table-wrap">

            <table class="attendance-table">

                <thead>

                    <tr>

                        <th>Registration</th>
                        <th>Participant</th>
                        <th>Email</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Marked At</th>

                    </tr>

                </thead>


                <tbody>


                <?php if ($attendanceResult && $attendanceResult->num_rows > 0): ?>


                    <?php while ($row = $attendanceResult->fetch_assoc()): ?>


                        <?php

                        $name = $row["user_name"] ?? "User";

                        $initial = strtoupper(
                            substr($name, 0, 1)
                        );

                        $attendanceStatus =
                            $row["attendance_status"] ?? "";

                        ?>


                        <tr>


                            <!-- REGISTRATION -->

                            <td>

                                <strong>
                                    #<?= (int)$row["registration_id"] ?>
                                </strong>

                            </td>


                            <!-- PARTICIPANT -->

                            <td>

                                <div class="attendance-user">

                                    <div class="attendance-avatar">

                                        <?= htmlspecialchars($initial) ?>

                                    </div>

                                    <strong>

                                        <?= htmlspecialchars($name) ?>

                                    </strong>

                                </div>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <?= htmlspecialchars(
                                    $row["email"] ?? ""
                                ) ?>

                            </td>


                            <!-- EVENT -->

                            <td>

                                <strong>

                                    <?= htmlspecialchars(
                                        $row["event_title"] ?? ""
                                    ) ?>

                                </strong>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?= htmlspecialchars(
                                    $row["event_date"] ?? ""
                                ) ?>

                            </td>


                            <!-- TIME -->

                            <td>

                                <?= htmlspecialchars(
                                    $row["event_time"] ?? ""
                                ) ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if ($attendanceStatus === "Present"): ?>

                                    <span class="status-badge status-present">
                                        ✓ Present
                                    </span>

                                <?php elseif ($attendanceStatus === "Absent"): ?>

                                    <span class="status-badge status-absent">
                                        ✕ Absent
                                    </span>

                                <?php else: ?>

                                    <span class="status-badge status-pending">
                                        Not Marked
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- MARKED AT -->

                            <td>

                                <?= htmlspecialchars(
                                    $row["marked_at"] ?? "—"
                                ) ?>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="8"
                            class="attendance-empty"
                        >

                            <div class="attendance-empty-icon">
                                📋
                            </div>

                            <strong>
                                No registrations found.
                            </strong>

                            <br>

                            Register a participant first.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>

    </section>


</div>


<?php

require "footer.php";

?>