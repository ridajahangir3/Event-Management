<?php

session_start();

require_once "db.php";

$page_title = "My Profile";
$active_page = "profile";

$message = "";
$type = "success";

$user = null;


/* =========================
   FIND LOGGED-IN USER
========================= */

$userId = 0;
$userEmail = "";


/* Check common session IDs */

if (isset($_SESSION["user_id"])) {
    $userId = (int)$_SESSION["user_id"];
}

if ($userId <= 0 && isset($_SESSION["id"])) {
    $userId = (int)$_SESSION["id"];
}


/* Check common session emails */

if (isset($_SESSION["user_email"])) {
    $userEmail = trim($_SESSION["user_email"]);
}

if ($userEmail === "" && isset($_SESSION["email"])) {
    $userEmail = trim($_SESSION["email"]);
}


/* =========================
   GET USER BY ID
========================= */

if ($userId > 0) {

    $stmt = $conn->prepare(
        "SELECT *
         FROM users
         WHERE id = ?
         LIMIT 1"
    );

    if ($stmt) {

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }

        $stmt->close();
    }
}


/* =========================
   GET USER BY EMAIL
========================= */

if (!$user && $userEmail !== "") {

    $stmt = $conn->prepare(
        "SELECT *
         FROM users
         WHERE email = ?
         LIMIT 1"
    );

    if ($stmt) {

        $stmt->bind_param("s", $userEmail);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }

        $stmt->close();
    }
}


/* =========================
   FALLBACK ADMIN
========================= */

if (!$user) {

    $result = $conn->query(
        "SELECT *
         FROM users
         WHERE role = 'admin'
         ORDER BY id ASC
         LIMIT 1"
    );

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
}


/* =========================
   UPDATE PROFILE
========================= */

if ($user && isset($_POST["update_profile"])) {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    if ($name === "") {

        $message = "Name cannot be empty.";
        $type = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $type = "danger";

    } else {

        $currentId = (int)$user["id"];


        /* Check duplicate email */

        $check = $conn->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             AND id != ?
             LIMIT 1"
        );

        $emailExists = false;

        if ($check) {

            $check->bind_param(
                "si",
                $email,
                $currentId
            );

            $check->execute();

            $checkResult = $check->get_result();

            if ($checkResult && $checkResult->num_rows > 0) {
                $emailExists = true;
            }

            $check->close();
        }


        if ($emailExists) {

            $message =
                "This email address is already being used.";

            $type = "danger";

        } else {

            $stmt = $conn->prepare(
                "UPDATE users
                 SET name = ?, email = ?
                 WHERE id = ?"
            );

            if ($stmt) {

                $stmt->bind_param(
                    "ssi",
                    $name,
                    $email,
                    $currentId
                );

                if ($stmt->execute()) {

                    $message =
                        "Profile updated successfully.";

                    $type = "success";

                    $user["name"] = $name;
                    $user["email"] = $email;

                    $_SESSION["user_email"] = $email;

                } else {

                    $message =
                        "Could not update profile.";

                    $type = "danger";
                }

                $stmt->close();

            } else {

                $message =
                    "Could not prepare profile update.";

                $type = "danger";
            }
        }
    }
}


/* =========================
   USER INFORMATION
========================= */

$userId =
    (int)(
        $user["id"]
        ?? 0
    );

$userName =
    $user["name"]
    ?? $user["username"]
    ?? "User";

$userEmail =
    $user["email"]
    ?? "";

$userRole =
    $user["role"]
    ?? "user";

$userRole = strtolower(trim($userRole));

$initial = strtoupper(
    substr(
        trim($userName),
        0,
        1
    )
);

$isAdmin = ($userRole === "admin");


/* =========================
   REGISTRATION COUNT
========================= */

$registrationCount = 0;

if ($userId > 0) {

    $checkTable = $conn->query(
        "SHOW TABLES LIKE 'registrations'"
    );

    if ($checkTable && $checkTable->num_rows > 0) {

        $registrationResult = $conn->query(
            "SELECT COUNT(*) AS total
             FROM registrations
             WHERE user_id = $userId"
        );

        if ($registrationResult) {

            $row =
                $registrationResult->fetch_assoc();

            $registrationCount =
                (int)(
                    $row["total"]
                    ?? 0
                );
        }
    }
}


/* =========================
   ATTENDANCE COUNT
========================= */

$attendanceCount = 0;

$attendanceTable = $conn->query(
    "SHOW TABLES LIKE 'attendance'"
);

if (
    $attendanceTable &&
    $attendanceTable->num_rows > 0 &&
    $userId > 0
) {

    $registrationTable = $conn->query(
        "SHOW TABLES LIKE 'registrations'"
    );

    if (
        $registrationTable &&
        $registrationTable->num_rows > 0
    ) {

        $attendanceResult = $conn->query(
            "SELECT COUNT(*) AS total
             FROM attendance a
             INNER JOIN registrations r
                 ON a.registration_id = r.id
             WHERE r.user_id = $userId
             AND a.status = 'Present'"
        );

        if ($attendanceResult) {

            $row =
                $attendanceResult->fetch_assoc();

            $attendanceCount =
                (int)(
                    $row["total"]
                    ?? 0
                );
        }
    }
}


require "header.php";

?>


<style>

/* =========================
   PROFILE PAGE
========================= */

.profile-page {
    max-width: 1100px;
    margin: 0 auto;
}


/* HEADER */

.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.profile-header h1 {
    margin: 0;
    font-size: 30px;
}

.profile-header p {
    margin-top: 7px;
    color: #718096;
}


/* MAIN GRID */

.profile-grid {
    display: grid;
    grid-template-columns: 330px minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}


/* =========================
   PROFILE CARD
========================= */

.profile-card {
    position: relative;
    overflow: hidden;
    padding: 30px;
    border-radius: 24px;

    background:
        linear-gradient(
            145deg,
            #ffffff,
            #f7f5ff
        );

    border: 1px solid #ebe7f8;

    box-shadow:
        0 20px 45px rgba(44,31,100,.12),
        inset 0 1px 0 rgba(255,255,255,.9);

    text-align: center;
}

.profile-card::before {
    content: "";

    position: absolute;

    width: 180px;
    height: 180px;

    border-radius: 50%;

    background: #eee7ff;

    top: -90px;
    right: -70px;

    opacity: .7;
}


/* AVATAR */

.profile-avatar {
    position: relative;
    z-index: 1;

    width: 105px;
    height: 105px;

    margin: 0 auto 18px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background:
        linear-gradient(
            145deg,
            #713cff,
            #4c20b8
        );

    color: white;

    font-size: 42px;
    font-weight: 900;

    box-shadow:
        0 15px 30px rgba(91,53,197,.35),
        inset 0 2px 4px rgba(255,255,255,.35);
}


/* NAME */

.profile-card h2 {
    margin: 0;

    font-size: 22px;

    color: #1f2937;
}


/* EMAIL */

.profile-email {
    display: block;

    margin-top: 7px;

    color: #718096;

    font-size: 14px;

    word-break: break-word;
}


/* ROLE */

.profile-role {
    display: inline-flex;

    margin-top: 16px;

    padding: 7px 13px;

    border-radius: 999px;

    background: #eee8ff;

    color: #5b35c5;

    font-size: 12px;

    font-weight: 800;
}

.profile-role.admin {
    background: #e9ddff;
    color: #5524bb;
}


/* STATUS */

.profile-status {
    margin-top: 18px;

    padding-top: 18px;

    border-top: 1px solid #ebe7f3;

    color: #16a34a;

    font-size: 13px;

    font-weight: 700;
}


/* =========================
   STATS
========================= */

.profile-stats {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;

    margin-top: 22px;
}

.profile-stat {
    padding: 14px;

    border-radius: 13px;

    background: rgba(255,255,255,.8);

    border: 1px solid #eeeaf7;
}

.profile-stat strong {
    display: block;

    font-size: 21px;

    color: #1f2937;
}

.profile-stat small {
    color: #718096;

    font-size: 11px;

    font-weight: 700;
}


/* =========================
   FORM CARD
========================= */

.profile-form-card {
    padding: 28px;

    border-radius: 24px;

    background: white;

    border: 1px solid #edf0f5;

    box-shadow:
        0 15px 35px rgba(31,41,55,.08);
}

.profile-form-card h2 {
    margin: 0;
}

.profile-form-subtitle {
    margin: 6px 0 25px;

    color: #718096;
}


/* FORM */

.profile-form {
    display: grid;

    gap: 20px;
}

.profile-field label {
    display: block;

    margin-bottom: 8px;

    font-weight: 700;

    color: #374151;
}

.profile-field input {
    width: 100%;

    box-sizing: border-box;
}


/* =========================
   ACCOUNT INFORMATION
========================= */

.account-info {
    margin-top: 25px;

    padding: 19px;

    border-radius: 16px;

    background: #f8f7fc;

    border: 1px solid #eeeaf7;
}

.account-info h3 {
    margin: 0 0 13px;

    font-size: 15px;
}

.account-row {
    display: flex;

    justify-content: space-between;

    gap: 15px;

    padding: 10px 0;

    border-bottom: 1px solid #e9e7ef;

    font-size: 13px;
}

.account-row:last-child {
    border-bottom: 0;
}

.account-row span:first-child {
    color: #718096;
}

.account-row span:last-child {
    font-weight: 700;

    color: #374151;

    text-align: right;
}


/* =========================
   SECURITY CARD
========================= */

.security-box {
    margin-top: 25px;

    padding: 20px;

    border-radius: 16px;

    background:
        linear-gradient(
            145deg,
            #faf8ff,
            #f5f1ff
        );

    border: 1px solid #e6ddff;
}

.security-box h3 {
    margin: 0;

    color: #4c2aa8;
}

.security-box p {
    margin: 6px 0 0;

    font-size: 13px;

    color: #718096;
}


/* =========================
   RESPONSIVE
========================= */

@media (max-width: 800px) {

    .profile-grid {
        grid-template-columns: 1fr;
    }

    .profile-header {
        align-items: flex-start;

        flex-direction: column;
    }

}

</style>


<div class="profile-page">


    <!-- =========================
         HEADER
    ========================= -->

    <div class="profile-header">

        <div>

            <h1>
                👤 My Profile
            </h1>

            <p>
                View and manage your account information.
            </p>

        </div>

    </div>


    <!-- =========================
         MESSAGE
    ========================= -->

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


    <div class="profile-grid">


        <!-- =========================
             LEFT PROFILE CARD
        ========================= -->

        <div class="profile-card">


            <div class="profile-avatar">

                <?= htmlspecialchars($initial) ?>

            </div>


            <h2>

                <?= htmlspecialchars($userName) ?>

            </h2>


            <span class="profile-email">

                <?= htmlspecialchars($userEmail) ?>

            </span>


            <span
                class="
                    profile-role
                    <?= $isAdmin ? 'admin' : '' ?>
                "
            >

                <?= $isAdmin
                    ? "✦ Administrator"
                    : "● Registered User"
                ?>

            </span>


            <div class="profile-status">

                🟢 Account Active

            </div>


            <!-- STATS -->

            <div class="profile-stats">


                <div class="profile-stat">

                    <strong>
                        <?= $registrationCount ?>
                    </strong>

                    <small>
                        Registrations
                    </small>

                </div>


                <div class="profile-stat">

                    <strong>
                        <?= $attendanceCount ?>
                    </strong>

                    <small>
                        Attended
                    </small>

                </div>


            </div>


        </div>


        <!-- =========================
             RIGHT SIDE
        ========================= -->

        <div>


            <!-- PROFILE INFORMATION -->

            <div class="profile-form-card">


                <h2>
                    👤 Personal Information
                </h2>


                <p class="profile-form-subtitle">
                    Update your basic account information.
                </p>


                <form
                    method="POST"
                    class="profile-form"
                >


                    <!-- NAME -->

                    <div class="profile-field">

                        <label>
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="<?= htmlspecialchars($userName) ?>"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="profile-field">

                        <label>
                            Email Address
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars($userEmail) ?>"
                            maxlength="150"
                            required
                        >

                    </div>


                    <!-- BUTTON -->

                    <div>

                        <button
                            type="submit"
                            name="update_profile"
                            class="btn"
                        >
                            💾 Save Changes
                        </button>

                    </div>


                </form>


                <!-- ACCOUNT INFORMATION -->

                <div class="account-info">


                    <h3>
                        📋 Account Information
                    </h3>


                    <div class="account-row">

                        <span>
                            Account ID
                        </span>

                        <span>
                            #<?= $userId ?>
                        </span>

                    </div>


                    <div class="account-row">

                        <span>
                            Role
                        </span>

                        <span>
                            <?= htmlspecialchars(
                                ucfirst($userRole)
                            ) ?>
                        </span>

                    </div>


                    <div class="account-row">

                        <span>
                            Status
                        </span>

                        <span style="color:#16a34a;">
                            🟢 Active
                        </span>

                    </div>


                    <div class="account-row">

                        <span>
                            Registrations
                        </span>

                        <span>
                            <?= $registrationCount ?>
                        </span>

                    </div>


                    <div class="account-row">

                        <span>
                            Events Attended
                        </span>

                        <span>
                            <?= $attendanceCount ?>
                        </span>

                    </div>


                </div>


                <!-- SECURITY -->

                <div class="security-box">

                    <h3>
                        🔐 Account Security
                    </h3>

                    <p>
                        Your account information is protected.
                        Password management can be added separately.
                    </p>

                </div>


            </div>


        </div>


    </div>


</div>


<?php

require "footer.php";

?>