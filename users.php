<?php

require_once "db.php";

$page_title = "Manage Users";
$active_page = "users";

$message = "";
$type = "success";


/* =========================================================
   ACTIONS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* CHANGE ROLE */
    if (isset($_POST["change_role"])) {

        $user_id = (int)($_POST["user_id"] ?? 0);
        $new_role = strtolower(trim($_POST["new_role"] ?? "user"));

        if (
            $user_id > 0 &&
            in_array($new_role, ["user", "admin"], true)
        ) {

            $stmt = $conn->prepare(
                "UPDATE users SET role = ? WHERE id = ?"
            );

            $stmt->bind_param(
                "si",
                $new_role,
                $user_id
            );

            if ($stmt->execute()) {
                $message = "User role updated successfully.";
                $type = "success";
            } else {
                $message = "Could not update user role.";
                $type = "danger";
            }

            $stmt->close();
        }
    }


    /* DELETE USER */
    if (isset($_POST["delete_user"])) {

        $user_id = (int)($_POST["user_id"] ?? 0);

        if ($user_id > 0) {

            /* Delete registrations first */
            $regStmt = $conn->prepare(
                "DELETE FROM registrations WHERE user_id = ?"
            );

            $regStmt->bind_param(
                "i",
                $user_id
            );

            $regStmt->execute();
            $regStmt->close();


            /* Delete user */
            $stmt = $conn->prepare(
                "DELETE FROM users WHERE id = ?"
            );

            $stmt->bind_param(
                "i",
                $user_id
            );

            if ($stmt->execute()) {
                $message = "User deleted successfully.";
                $type = "success";
            } else {
                $message = "Could not delete user.";
                $type = "danger";
            }

            $stmt->close();
        }
    }
}


/* =========================================================
   GET USERS
========================================================= */

$result = $conn->query(
    "SELECT
        u.*,
        COUNT(r.id) AS registration_count
     FROM users u
     LEFT JOIN registrations r
        ON u.id = r.user_id
     GROUP BY u.id
     ORDER BY u.id DESC"
);


/* =========================================================
   STATISTICS
========================================================= */

$totalUsers = 0;
$totalAdmins = 0;
$totalRegularUsers = 0;

if ($result) {

    $totalUsers = $result->num_rows;

    while ($row = $result->fetch_assoc()) {

        $role = strtolower(
            $row["role"] ?? "user"
        );

        if ($role === "admin") {
            $totalAdmins++;
        } else {
            $totalRegularUsers++;
        }
    }

    /* Run query again for table */
    $result = $conn->query(
        "SELECT
            u.*,
            COUNT(r.id) AS registration_count
         FROM users u
         LEFT JOIN registrations r
            ON u.id = r.user_id
         GROUP BY u.id
         ORDER BY u.id DESC"
    );
}


require "header.php";

?>


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="premium-header">

    <div>

        <div class="eyebrow">
            ✦ USER MANAGEMENT
        </div>

        <h1>
            Manage Users
        </h1>

        <p>
            View, manage and control all registered users.
        </p>

    </div>

    <div class="header-orb">
        👥
    </div>

</div>


<!-- =========================================================
     MESSAGE
========================================================= -->

<?php if ($message): ?>

<div class="premium-alert <?= htmlspecialchars($type) ?>">
    <?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>


<!-- =========================================================
     STATISTICS
========================================================= -->

<div class="stats-grid">


    <!-- TOTAL USERS -->

    <div
        class="stat-card stat-filter-card"
        data-filter="all"
        onclick="filterByStat('all')"
    >

        <div class="stat-icon purple">
            👥
        </div>

        <div>

            <small>
                Total Users
            </small>

            <strong>
                <?= $totalUsers ?>
            </strong>

        </div>

    </div>


    <!-- ADMINISTRATORS -->

    <div
        class="stat-card stat-filter-card"
        data-filter="admin"
        onclick="filterByStat('admin')"
    >

        <div class="stat-icon violet">
            🔐
        </div>

        <div>

            <small>
                Administrators
            </small>

            <strong>
                <?= $totalAdmins ?>
            </strong>

        </div>

    </div>


    <!-- REGISTERED USERS -->

    <div
        class="stat-card stat-filter-card"
        data-filter="user"
        onclick="filterByStat('user')"
    >

        <div class="stat-icon green">
            ✨
        </div>

        <div>

            <small>
                Registered Users
            </small>

            <strong>
                <?= $totalRegularUsers ?>
            </strong>

        </div>

    </div>


</div>


<!-- =========================================================
     USERS CARD
========================================================= -->

<div class="premium-users-card">


    <!-- TOP BAR -->

    <div class="users-toolbar">

        <div>

            <div class="section-label">
                USER DIRECTORY
            </div>

            <h2>
                Users List
            </h2>

            <p>
                Manage your registered members and administrators.
            </p>

        </div>


        <div class="filters">

            <div class="search-box">

                <span>
                    🔎
                </span>

                <input
                    type="text"
                    id="userSearch"
                    placeholder="Search users..."
                >

            </div>


            <select
                id="roleFilter"
                class="role-filter"
            >

                <option value="all">
                    All Roles
                </option>

                <option value="admin">
                    Admin
                </option>

                <option value="user">
                    User
                </option>

            </select>

        </div>

    </div>


    <?php if ($result && $result->num_rows > 0): ?>

    <div class="table-container">

        <table id="usersTable">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Events</th>
                    <th>Account</th>
                    <th>Actions</th>

                </tr>

            </thead>


            <tbody>

            <?php while ($user = $result->fetch_assoc()): ?>

                <?php

                $id = (int)(
                    $user["id"] ?? 0
                );

                $name =
                    $user["name"]
                    ?? $user["username"]
                    ?? "User";

                $email =
                    $user["email"]
                    ?? "";

                $role =
                    strtolower(
                        $user["role"] ?? "user"
                    );

                $registrationCount =
                    (int)(
                        $user["registration_count"]
                        ?? 0
                    );

                $initial =
                    strtoupper(
                        substr($name, 0, 1)
                    );

                $isAdmin =
                    ($role === "admin");

                ?>


                <tr data-role="<?= htmlspecialchars($role) ?>">


                    <!-- ID -->

                    <td>

                        <span class="id-pill">
                            #<?= $id ?>
                        </span>

                    </td>


                    <!-- USER -->

                    <td>

                        <div class="user-cell">

                            <div
                                class="user-avatar <?= $isAdmin ? "admin" : "" ?>"
                            >
                                <?= htmlspecialchars($initial) ?>
                            </div>


                            <div>

                                <strong>
                                    <?= htmlspecialchars($name) ?>
                                </strong>

                                <small>
                                    <?= $isAdmin
                                        ? "Administrator"
                                        : "Registered user"
                                    ?>
                                </small>

                            </div>

                        </div>

                    </td>


                    <!-- EMAIL -->

                    <td>

                        <span class="email-text">
                            <?= htmlspecialchars($email) ?>
                        </span>

                    </td>


                    <!-- ROLE -->

                    <td>

                        <?php if ($isAdmin): ?>

                            <span class="role-badge admin">
                                ✦ Admin
                            </span>

                        <?php else: ?>

                            <span class="role-badge user">
                                ● User
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- EVENTS -->

                    <td>

                        <span class="event-count">
                            🎟️ <?= $registrationCount ?>
                        </span>

                    </td>


                    <!-- ACCOUNT -->

                    <td>

                        <span class="active-status">

                            <i></i>

                            Active

                        </span>

                    </td>


                    <!-- ACTIONS -->

                    <td>

                        <div class="action-buttons">


                            <!-- VIEW -->

                            <button
                                type="button"
                                class="action-btn view"
                                onclick='viewUser(
                                    <?= json_encode($name) ?>,
                                    <?= json_encode($email) ?>,
                                    <?= json_encode($role) ?>,
                                    <?= $registrationCount ?>
                                )'
                            >
                                👤
                            </button>


                            <!-- EMAIL -->

                            <?php if ($email): ?>

                                <a
                                    href="mailto:<?= htmlspecialchars($email) ?>?subject=Event Management System"
                                    class="action-btn email"
                                    title="Send Email"
                                >
                                    📧
                                </a>

                            <?php endif; ?>


                            <!-- CHANGE ROLE -->

                            <form
                                method="POST"
                                style="display:inline;"
                            >

                                <input
                                    type="hidden"
                                    name="user_id"
                                    value="<?= $id ?>"
                                >

                                <input
                                    type="hidden"
                                    name="new_role"
                                    value="<?= $isAdmin ? "user" : "admin" ?>"
                                >

                                <button
                                    type="submit"
                                    name="change_role"
                                    class="action-btn role"
                                    title="Change Role"
                                    onclick="return confirm('Change this user role?')"
                                >
                                    🔄
                                </button>

                            </form>


                            <!-- DELETE -->

                            <form
                                method="POST"
                                style="display:inline;"
                            >

                                <input
                                    type="hidden"
                                    name="user_id"
                                    value="<?= $id ?>"
                                >

                                <button
                                    type="submit"
                                    name="delete_user"
                                    class="action-btn delete"
                                    title="Delete User"
                                    onclick="return confirm('Delete this user and their registrations?')"
                                >
                                    🗑
                                </button>

                            </form>


                        </div>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>


    <?php else: ?>


        <div class="empty-users">

            <div>
                👥
            </div>

            <h2>
                No Users Yet
            </h2>

            <p>
                There are currently no registered users.
            </p>

        </div>


    <?php endif; ?>

</div>


<!-- =========================================================
     PROFILE MODAL
========================================================= -->

<div
    id="userModal"
    class="modal-overlay"
>

    <div class="profile-modal">

        <button
            type="button"
            class="modal-close"
            onclick="closeUserModal()"
        >
            ×
        </button>


        <div class="modal-top">

            <div
                id="modalAvatar"
                class="modal-avatar"
            >
                U
            </div>

            <h2 id="modalName">
                User
            </h2>

            <p id="modalEmail">
                email
            </p>

        </div>


        <div class="profile-info">

            <div class="profile-info-box">

                <small>
                    ROLE
                </small>

                <strong id="modalRole">
                    User
                </strong>

            </div>


            <div class="profile-info-box">

                <small>
                    EVENT REGISTRATIONS
                </small>

                <strong id="modalRegistrations">
                    0
                </strong>

            </div>

        </div>


        <div class="modal-active">

            <span></span>

            Active Account

        </div>

    </div>

</div>


<!-- =========================================================
     DESIGN
========================================================= -->

<style>

* {
    box-sizing: border-box;
}


/* =========================================================
   HEADER
========================================================= */

.premium-header {

    position: relative;
    overflow: hidden;

    display: flex;
    align-items: center;
    justify-content: space-between;

    min-height: 185px;

    padding: 34px 38px;

    margin-bottom: 24px;

    border-radius: 30px;

    background:
        radial-gradient(
            circle at 85% 20%,
            rgba(139,92,246,.22),
            transparent 30%
        ),
        radial-gradient(
            circle at 10% 100%,
            rgba(99,102,241,.12),
            transparent 35%
        ),
        linear-gradient(
            135deg,
            #ffffff 0%,
            #faf8ff 55%,
            #f1ecff 100%
        );

    border: 1px solid rgba(255,255,255,.9);

    box-shadow:
        0 25px 60px rgba(65,42,125,.11),
        0 8px 18px rgba(65,42,125,.06),
        inset 0 1px 0 white;
}

.premium-header::before {

    content: "";

    position: absolute;

    width: 230px;
    height: 230px;

    right: -80px;
    top: -100px;

    border-radius: 50%;

    background:
        linear-gradient(
            135deg,
            rgba(139,92,246,.15),
            rgba(99,102,241,.02)
        );

    box-shadow:
        inset 0 0 30px rgba(255,255,255,.7);
}

.eyebrow {

    font-size: 11px;

    font-weight: 900;

    letter-spacing: 2px;

    color: #7540df;

    margin-bottom: 8px;
}

.premium-header h1 {

    margin: 0;

    font-size: 36px;

    letter-spacing: -1px;

    color: #241641;
}

.premium-header p {

    margin: 9px 0 0;

    color: #777087;

    font-size: 15px;
}

.header-orb {

    position: relative;

    z-index: 2;

    width: 82px;
    height: 82px;

    border-radius: 27px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 36px;

    background:
        linear-gradient(
            145deg,
            #ffffff,
            #eee8ff
        );

    box-shadow:
        0 18px 35px rgba(88,49,180,.18),
        inset 0 2px 4px white;
}


/* =========================================================
   ALERT
========================================================= */

.premium-alert {

    padding: 15px 19px;

    margin-bottom: 20px;

    border-radius: 15px;

    font-weight: 700;

    box-shadow:
        0 8px 22px rgba(50,30,90,.07);
}

.premium-alert.success {

    color: #176b3b;

    background: #eafaf1;

    border: 1px solid #c9efd9;
}

.premium-alert.danger {

    color: #a52d3a;

    background: #fff0f1;

    border: 1px solid #ffd3d7;
}


/* =========================================================
   STATISTICS
========================================================= */

.stats-grid {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(210px, 1fr)
        );

    gap: 18px;

    margin-bottom: 24px;
}


/* CLICKABLE CARDS */

.stat-filter-card {

    cursor: pointer;

    user-select: none;
}

.stat-filter-card:hover {

    transform:
        translateY(-7px)
        scale(1.01);

}

.stat-filter-card.selected {

    border-color: #8b5cf6;

    box-shadow:
        0 0 0 4px rgba(139,92,246,.10),
        0 25px 50px rgba(65,42,125,.14);
}


.stat-card {

    position: relative;

    overflow: hidden;

    display: flex;

    align-items: center;

    gap: 16px;

    min-height: 115px;

    padding: 22px;

    border-radius: 22px;

    background:
        linear-gradient(
            145deg,
            #ffffff,
            #faf8ff
        );

    border: 1px solid #eee9f8;

    box-shadow:
        0 14px 35px rgba(65,42,125,.08),
        inset 0 1px 0 white;

    transition:
        transform .25s ease,
        box-shadow .25s ease,
        border-color .25s ease;
}

.stat-card::after {

    content: "";

    position: absolute;

    width: 110px;
    height: 110px;

    right: -50px;
    top: -50px;

    border-radius: 50%;

    background: rgba(124,58,237,.055);
}


.stat-icon {

    width: 55px;
    height: 55px;

    flex-shrink: 0;

    border-radius: 17px;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 25px;

    box-shadow:
        0 10px 20px rgba(80,45,150,.15),
        inset 0 2px 3px rgba(255,255,255,.8);
}

.stat-icon.purple {
    background: #eee8ff;
}

.stat-icon.violet {
    background: #e9e5ff;
}

.stat-icon.green {
    background: #e9f9f0;
}

.stat-card small {

    display: block;

    color: #81798e;

    font-size: 12px;

    font-weight: 700;
}

.stat-card strong {

    display: block;

    margin-top: 4px;

    color: #271747;

    font-size: 28px;
}


/* =========================================================
   MAIN USERS CARD
========================================================= */

.premium-users-card {

    overflow: hidden;

    border-radius: 28px;

    background:
        linear-gradient(
            145deg,
            #ffffff,
            #fbfaff
        );

    border: 1px solid #eeeaf7;

    box-shadow:
        0 25px 65px rgba(62,40,120,.10),
        0 8px 20px rgba(62,40,120,.05),
        inset 0 1px 0 white;

    padding: 27px;
}


/* =========================================================
   TOOLBAR
========================================================= */

.users-toolbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    flex-wrap: wrap;

    margin-bottom: 22px;
}

.section-label {

    color: #7640dc;

    font-size: 10px;

    font-weight: 900;

    letter-spacing: 1.8px;

    margin-bottom: 5px;
}

.users-toolbar h2 {

    margin: 0;

    color: #271747;

    font-size: 23px;
}

.users-toolbar p {

    color: #81798d;

    margin: 5px 0 0;

    font-size: 13px;
}

.filters {

    display: flex;

    gap: 10px;

    flex-wrap: wrap;
}

.search-box {

    display: flex;

    align-items: center;

    gap: 9px;

    min-width: 240px;

    height: 45px;

    padding: 0 14px;

    border-radius: 14px;

    background: white;

    border: 1px solid #e6e0f1;

    box-shadow:
        inset 0 1px 2px rgba(50,30,90,.03),
        0 5px 14px rgba(50,30,90,.04);
}

.search-box:focus-within {

    border-color: #8b5cf6;

    box-shadow:
        0 0 0 4px rgba(139,92,246,.10),
        0 8px 20px rgba(60,35,120,.08);
}

.search-box input {

    width: 100%;

    border: none !important;

    outline: none !important;

    background: transparent !important;

    box-shadow: none !important;

    padding: 0 !important;
}

.role-filter {

    height: 45px;

    min-width: 135px;

    padding: 0 12px;

    border-radius: 14px;

    border: 1px solid #e6e0f1;

    background: white;

    color: #4c425c;

    font-weight: 700;

    outline: none;

    cursor: pointer;

    box-shadow:
        0 5px 14px rgba(50,30,90,.04);
}


/* =========================================================
   TABLE
========================================================= */

.table-container {

    overflow-x: auto;
}

#usersTable {

    width: 100%;

    border-collapse: separate;

    border-spacing: 0 9px;

    min-width: 1000px;
}

#usersTable thead th {

    padding: 10px 14px;

    border: none;

    color: #8a8196;

    font-size: 10px;

    letter-spacing: 1.2px;

    text-transform: uppercase;

    text-align: left;
}

#usersTable tbody tr {

    background:
        linear-gradient(
            105deg,
            #ffffff,
            #fcfbff
        );

    transition:
        transform .22s ease,
        box-shadow .22s ease;
}

#usersTable tbody tr:hover {

    transform: translateY(-3px);

    box-shadow:
        0 14px 30px rgba(66,42,120,.10);
}

#usersTable tbody td {

    padding: 14px;

    border-top: 1px solid #f0ecf7;

    border-bottom: 1px solid #f0ecf7;
}

#usersTable tbody td:first-child {

    border-left: 1px solid #f0ecf7;

    border-radius: 16px 0 0 16px;
}

#usersTable tbody td:last-child {

    border-right: 1px solid #f0ecf7;

    border-radius: 0 16px 16px 0;
}


/* =========================================================
   USER
========================================================= */

.user-cell {

    display: flex;

    align-items: center;

    gap: 11px;
}

.user-avatar {

    width: 44px;
    height: 44px;

    flex-shrink: 0;

    border-radius: 14px;

    display: flex;

    align-items: center;
    justify-content: center;

    color: white;

    font-weight: 900;

    background:
        linear-gradient(
            145deg,
            #8b5cf6,
            #6335d5
        );

    box-shadow:
        0 9px 18px rgba(93,52,185,.24),
        inset 0 2px 3px rgba(255,255,255,.3);
}

.user-avatar.admin {

    background:
        linear-gradient(
            145deg,
            #30265e,
            #17112f
        );
}

.user-cell strong {

    display: block;

    color: #2c2044;

    font-size: 14px;
}

.user-cell small {

    display: block;

    color: #9890a3;

    font-size: 11px;

    margin-top: 3px;
}

.id-pill {

    display: inline-block;

    padding: 6px 9px;

    border-radius: 9px;

    background: #f0ebff;

    color: #6737d8;

    font-size: 11px;

    font-weight: 800;
}

.email-text {

    color: #625a6e;

    font-size: 13px;
}

.role-badge {

    display: inline-flex;

    align-items: center;

    padding: 7px 11px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 800;
}

.role-badge.admin {

    color: #6335d5;

    background: #eee8ff;
}

.role-badge.user {

    color: #21834a;

    background: #e8f8ef;
}

.event-count {

    display: inline-block;

    padding: 7px 10px;

    border-radius: 10px;

    background: #f5f3f9;

    color: #554b65;

    font-size: 12px;

    font-weight: 800;
}

.active-status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    color: #27894f;

    font-size: 12px;

    font-weight: 800;
}

.active-status i {

    width: 8px;
    height: 8px;

    border-radius: 50%;

    background: #31c66c;

    box-shadow:
        0 0 0 4px rgba(49,198,108,.10);
}


/* =========================================================
   ACTION BUTTONS
========================================================= */

.action-buttons {

    display: flex;

    align-items: center;

    gap: 6px;
}

.action-btn {

    width: 34px;
    height: 34px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border: none;

    border-radius: 10px;

    text-decoration: none;

    cursor: pointer;

    transition:
        transform .2s ease,
        box-shadow .2s ease;
}

.action-btn:hover {

    transform:
        translateY(-3px)
        scale(1.05);

    box-shadow:
        0 8px 17px rgba(60,35,120,.13);
}

.action-btn.view {

    background: #eee8ff;
}

.action-btn.email {

    background: #e8f4ff;
}

.action-btn.role {

    background: #f5f1ff;
}

.action-btn.delete {

    background: #fff0f1;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-users {

    text-align: center;

    padding: 70px 20px;
}

.empty-users > div {

    font-size: 55px;

    margin-bottom: 12px;
}

.empty-users h2 {

    color: #2b2042;

    margin: 0;
}

.empty-users p {

    color: #847b91;

    margin-top: 7px;
}


/* =========================================================
   MODAL
========================================================= */

.modal-overlay {

    display: none;

    position: fixed;

    inset: 0;

    z-index: 9999;

    align-items: center;
    justify-content: center;

    padding: 20px;

    background:
        rgba(24,15,50,.58);

    backdrop-filter: blur(10px);
}

.profile-modal {

    position: relative;

    width: 100%;

    max-width: 430px;

    padding: 30px;

    border-radius: 27px;

    background:
        radial-gradient(
            circle at 90% 0%,
            rgba(139,92,246,.16),
            transparent 30%
        ),
        linear-gradient(
            145deg,
            #ffffff,
            #f8f5ff
        );

    border: 1px solid rgba(255,255,255,.9);

    box-shadow:
        0 40px 100px rgba(22,12,50,.30),
        inset 0 1px 0 white;
}

.modal-close {

    position: absolute;

    top: 15px;
    right: 15px;

    width: 35px;
    height: 35px;

    border: none;

    border-radius: 50%;

    background: #f2eff7;

    color: #62586f;

    font-size: 20px;

    cursor: pointer;
}

.modal-top {

    text-align: center;

    margin-bottom: 24px;
}

.modal-avatar {

    width: 75px;
    height: 75px;

    margin: 0 auto 13px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 23px;

    color: white;

    font-size: 29px;

    font-weight: 900;

    background:
        linear-gradient(
            145deg,
            #8b5cf6,
            #6335d5
        );

    box-shadow:
        0 15px 30px rgba(93,52,185,.27),
        inset 0 2px 3px rgba(255,255,255,.35);
}

.modal-top h2 {

    margin: 0;

    color: #291c43;
}

.modal-top p {

    margin: 6px 0 0;

    color: #81798d;

    font-size: 13px;
}

.profile-info {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;
}

.profile-info-box {

    padding: 15px;

    border-radius: 15px;

    background: rgba(255,255,255,.72);

    border: 1px solid #eee9f5;
}

.profile-info-box small {

    display: block;

    color: #938a9d;

    font-size: 9px;

    letter-spacing: 1px;

    font-weight: 800;
}

.profile-info-box strong {

    display: block;

    color: #332449;

    margin-top: 5px;
}

.modal-active {

    display: flex;

    justify-content: center;
    align-items: center;

    gap: 7px;

    margin-top: 20px;

    color: #27894f;

    font-size: 13px;

    font-weight: 800;
}

.modal-active span {

    width: 8px;
    height: 8px;

    border-radius: 50%;

    background: #31c66c;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .premium-header {

        padding: 25px;

        min-height: 160px;
    }

    .premium-header h1 {

        font-size: 28px;
    }

    .header-orb {

        display: none;
    }

    .premium-users-card {

        padding: 18px;
    }

    .filters {

        width: 100%;
    }

    .search-box {

        min-width: 0;

        flex: 1;
    }

    .role-filter {

        min-width: 120px;
    }

    .profile-info {

        grid-template-columns: 1fr;
    }

}

</style>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>


/* =========================================================
   GET ELEMENTS
========================================================= */

const searchInput =
    document.getElementById("userSearch");

const roleFilter =
    document.getElementById("roleFilter");


/* =========================================================
   FILTER USERS
========================================================= */

function filterUsers() {

    const search =
        searchInput
            ? searchInput.value.toLowerCase().trim()
            : "";

    const role =
        roleFilter
            ? roleFilter.value
            : "all";


    const rows =
        document.querySelectorAll(
            "#usersTable tbody tr"
        );


    rows.forEach(function(row) {

        const text =
            row.innerText.toLowerCase();

        const rowRole =
            row.getAttribute("data-role");


        const searchMatch =
            text.includes(search);


        const roleMatch =
            role === "all"
            || rowRole === role;


        if (searchMatch && roleMatch) {

            row.style.display = "";

        } else {

            row.style.display = "none";

        }

    });


    /* Highlight selected card */

    document
        .querySelectorAll(".stat-filter-card")
        .forEach(function(card) {

            card.classList.remove("selected");

        });


    const selectedCard =
        document.querySelector(
            '.stat-filter-card[data-filter="' +
            role +
            '"]'
        );


    if (selectedCard) {

        selectedCard.classList.add("selected");

    }

}


/* =========================================================
   SEARCH
========================================================= */

if (searchInput) {

    searchInput.addEventListener(
        "input",
        function() {

            filterUsers();

        }
    );

}


/* =========================================================
   DROPDOWN
========================================================= */

if (roleFilter) {

    roleFilter.addEventListener(
        "change",
        function() {

            filterUsers();

        }
    );

}


/* =========================================================
   STATISTICS CARD CLICK
========================================================= */

function filterByStat(role) {

    /* Set dropdown */

    if (roleFilter) {

        roleFilter.value = role;

    }


    /* Apply filter */

    filterUsers();


    /* Scroll to users */

    const usersCard =
        document.querySelector(
            ".premium-users-card"
        );


    if (usersCard) {

        usersCard.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });

    }

}


/* =========================================================
   VIEW USER
========================================================= */

function viewUser(
    name,
    email,
    role,
    registrations
) {

    const modal =
        document.getElementById(
            "userModal"
        );


    const avatar =
        document.getElementById(
            "modalAvatar"
        );


    document.getElementById(
        "modalName"
    ).textContent = name;


    document.getElementById(
        "modalEmail"
    ).textContent = email;


    document.getElementById(
        "modalRole"
    ).textContent =
        role === "admin"
        ? "✦ Administrator"
        : "● Registered User";


    document.getElementById(
        "modalRegistrations"
    ).textContent =
        registrations + " event(s)";


    avatar.textContent =
        name
            .charAt(0)
            .toUpperCase();


    modal.style.display = "flex";

}


/* =========================================================
   CLOSE MODAL
========================================================= */

function closeUserModal() {

    const modal =
        document.getElementById(
            "userModal"
        );


    if (modal) {

        modal.style.display = "none";

    }

}


/* =========================================================
   CLOSE MODAL BY CLICKING OUTSIDE
========================================================= */

const userModal =
    document.getElementById(
        "userModal"
    );


if (userModal) {

    userModal.addEventListener(
        "click",
        function(event) {

            if (event.target === this) {

                closeUserModal();

            }

        }
    );

}


/* =========================================================
   ESC CLOSE
========================================================= */

document.addEventListener(
    "keydown",
    function(event) {

        if (event.key === "Escape") {

            closeUserModal();

        }

    }
);


/* =========================================================
   INITIAL STATE
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        filterUsers();

    }
);

</script>


<?php

require "footer.php";

?>