<?php
ob_start();

session_start();

require_once "db.php";

if (isset($_POST["enter_hub"])) {

    $_SESSION["user_id"] = 1;
    $_SESSION["user_name"] = "Administrator";
    $_SESSION["user_email"] = "admin@collegehub.local";

    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | College Event Hub</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    font-family: "Segoe UI", Arial, sans-serif;
    background: linear-gradient(135deg, #f7f3ff 0%, #eee7ff 45%, #f9f6ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 25px;
    color: #29233d;
    position: relative;
    overflow: hidden;
}

body::before {
    content: "";
    position: fixed;
    width: 330px;
    height: 330px;
    border-radius: 50%;
    background: rgba(185, 166, 235, .18);
    top: -130px;
    left: -100px;
}

body::after {
    content: "";
    position: fixed;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: rgba(215, 201, 246, .25);
    bottom: -130px;
    right: -90px;
}

.login-card {
    width: 100%;
    max-width: 430px;
    background: rgba(255,255,255,.94);
    border: 1px solid #e4dbf5;
    border-radius: 28px;
    padding: 42px;
    position: relative;
    z-index: 2;
    box-shadow: 0 25px 70px rgba(91, 72, 130, .14);
}

.logo {
    width: 68px;
    height: 68px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #c9b7ee, #ad95df);
    color: #ffffff;
    font-size: 30px;
    margin-bottom: 25px;
    box-shadow: 0 12px 25px rgba(153, 125, 211, .20);
}

.small-title {
    color: #9278c7;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2.2px;
    text-transform: uppercase;
    margin-bottom: 10px;
}

h1 {
    color: #302849;
    font-size: 31px;
    font-weight: 750;
    margin-bottom: 10px;
}

.subtitle {
    color: #7d7890;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 28px;
}

button {
    width: 100%;
    padding: 15px;
    border: 0;
    border-radius: 13px;
    background: linear-gradient(135deg, #a992d9, #9278c7);
    color: white;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    margin-top: 5px;
    transition: .25s;
    box-shadow: 0 10px 25px rgba(146,120,199,.20);
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 28px rgba(146,120,199,.27);
}

.note {
    margin-top: 21px;
    padding: 13px 15px;
    border-radius: 12px;
    background: #f7f3ff;
    border: 1px solid #e7def7;
    color: #716687;
    font-size: 12px;
    line-height: 1.6;
    text-align: center;
}

.back {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #8b7aa9;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
}

.back:hover {
    color: #7358a5;
}

@media (max-width: 500px) {
    body {
        padding: 15px;
    }

    .login-card {
        padding: 32px 25px;
    }

    h1 {
        font-size: 27px;
    }
}
</style>
</head>

<body>

<div class="login-card">

    <div class="logo">🎓</div>

    <div class="small-title">
        College Event Hub
    </div>

    <h1>
        Welcome Back
    </h1>

    <p class="subtitle">
        Enter the College Event Hub administration panel
        and continue managing college events.
    </p>

    <form method="POST">
        <button type="submit" name="enter_hub">
            Enter College Event Hub →
        </button>
    </form>

    <div class="note">
        🔒 Local college access. No Gmail account
        or Gmail password is required.
    </div>

    <a href="index.php" class="back">
        ← Back to College Event Hub
    </a>

</div>

</body>
</html>
