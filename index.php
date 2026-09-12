```php
<?php
session_start();
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

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    background:
        linear-gradient(
            135deg,
            #eee9f8,
            #f7f4fc,
            #e7e0f2
        );

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 25px;

    color: #302a3d;

    overflow: hidden;

    position: relative;
}


/* =========================
   SOFT BACKGROUND SHAPES
========================= */

body::before {

    content: "";

    position: fixed;

    width: 360px;

    height: 360px;

    border-radius: 50%;

    background: rgba(177, 160, 207, .15);

    top: -160px;

    right: -100px;
}


body::after {

    content: "";

    position: fixed;

    width: 300px;

    height: 300px;

    border-radius: 50%;

    background: rgba(205, 193, 225, .20);

    bottom: -140px;

    left: -100px;
}


/* =========================
   MAIN CARD
========================= */

.login-card {

    width: 100%;

    max-width: 920px;

    min-height: 530px;

    display: grid;

    grid-template-columns: 1.05fr .95fr;

    background: rgba(255,255,255,.96);

    border: 1px solid #e2dbea;

    border-radius: 30px;

    overflow: hidden;

    position: relative;

    z-index: 2;

    box-shadow:
        0 30px 80px rgba(73, 60, 91, .15);
}


/* =========================
   LEFT SIDE
========================= */

.left {

    padding: 60px;

    display: flex;

    flex-direction: column;

    justify-content: center;

    background:
        linear-gradient(
            145deg,
            #ffffff,
            #f7f3fb
        );
}


.logo {

    width: 72px;

    height: 72px;

    border-radius: 21px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        linear-gradient(
            135deg,
            #b6a4cf,
            #9f8bbd
        );

    color: white;

    font-size: 32px;

    margin-bottom: 28px;

    box-shadow:
        0 12px 25px rgba(126, 104, 157, .20);
}


.eyebrow {

    color: #88739f;

    font-size: 11px;

    font-weight: 800;

    letter-spacing: 2.3px;

    text-transform: uppercase;

    margin-bottom: 12px;
}


h1 {

    font-size: 47px;

    line-height: 1.06;

    letter-spacing: -1.5px;

    color: #342c43;

    margin-bottom: 20px;
}


.description {

    max-width: 420px;

    color: #777182;

    font-size: 15px;

    line-height: 1.8;
}


/* =========================
   FEATURES
========================= */

.features {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 12px;

    margin-top: 30px;
}


.feature {

    display: flex;

    align-items: center;

    gap: 9px;

    color: #5d5668;

    font-size: 12px;

    font-weight: 700;
}


.feature-icon {

    width: 34px;

    height: 34px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eee8f7;

    color: #75618e;

    font-size: 16px;
}


/* =========================
   RIGHT LOGIN SIDE
========================= */

.right {

    padding: 55px 48px;

    display: flex;

    flex-direction: column;

    justify-content: center;

    text-align: center;

    background:
        linear-gradient(
            145deg,
            #9584ae,
            #a99abb,
            #b7abc7
        );

    color: white;

    position: relative;

    overflow: hidden;
}


.right::before {

    content: "";

    position: absolute;

    width: 230px;

    height: 230px;

    border-radius: 50%;

    background: rgba(255,255,255,.07);

    top: -100px;

    right: -70px;
}


.right::after {

    content: "";

    position: absolute;

    width: 190px;

    height: 190px;

    border-radius: 50%;

    background: rgba(255,255,255,.06);

    bottom: -90px;

    left: -70px;
}


.right-content {

    position: relative;

    z-index: 2;
}


/* =========================
   LOGIN ICON
========================= */

.login-icon {

    width: 72px;

    height: 72px;

    margin: 0 auto 23px;

    border-radius: 21px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: rgba(255,255,255,.18);

    border: 1px solid rgba(255,255,255,.18);

    font-size: 30px;

    box-shadow:
        0 12px 25px rgba(60,45,75,.12);
}


.right h2 {

    font-size: 30px;

    margin-bottom: 11px;

    font-weight: 750;
}


.subtitle {

    color: rgba(255,255,255,.82);

    font-size: 14px;

    line-height: 1.7;

    max-width: 330px;

    margin: 0 auto 28px;
}


/* =========================
   LOGIN BUTTON
========================= */

.login-btn {

    display: block;

    width: 100%;

    padding: 15px;

    border-radius: 13px;

    background: white;

    color: #6d5a82;

    text-decoration: none;

    font-size: 15px;

    font-weight: 800;

    transition: .25s;

    box-shadow:
        0 12px 25px rgba(60,45,75,.15);
}


.login-btn:hover {

    transform: translateY(-3px);

    box-shadow:
        0 16px 30px rgba(60,45,75,.22);
}


/* =========================
   SECURITY
========================= */

.security {

    margin-top: 20px;

    padding: 12px;

    border-radius: 11px;

    background: rgba(255,255,255,.10);

    border: 1px solid rgba(255,255,255,.12);

    color: rgba(255,255,255,.78);

    font-size: 11px;

    line-height: 1.6;
}


/* =========================
   MOBILE
========================= */

@media (max-width: 750px) {

    .login-card {

        grid-template-columns: 1fr;

    }

    .left {

        padding: 42px 30px;

    }

    .right {

        padding: 45px 30px;

    }

    h1 {

        font-size: 38px;

    }

}


@media (max-width: 480px) {

    .features {

        grid-template-columns: 1fr;

    }

    body {

        padding: 14px;

    }

}

</style>

</head>


<body>


<div class="login-card">


    <!-- =========================
         LEFT
    ========================= -->

    <div class="left">


        <div class="logo">
            🎓
        </div>


        <div class="eyebrow">
            College Event Management
        </div>


        <h1>
            College<br>
            Event Hub
        </h1>


        <p class="description">

            A simple and elegant platform
            for managing college events,
            registrations, participants
            and attendance.

        </p>


        <div class="features">


            <div class="feature">

                <div class="feature-icon">
                    📅
                </div>

                Manage Events

            </div>


            <div class="feature">

                <div class="feature-icon">
                    🎟️
                </div>

                Registrations

            </div>


            <div class="feature">

                <div class="feature-icon">
                    👥
                </div>

                Participants

            </div>


            <div class="feature">

                <div class="feature-icon">
                    📊
                </div>

                Attendance

            </div>


        </div>


    </div>


    <!-- =========================
         RIGHT
    ========================= -->

    <div class="right">


        <div class="right-content">


            <div class="login-icon">
                🔐
            </div>


            <h2>
                Welcome
            </h2>


            <p class="subtitle">

                Welcome to the College Event Hub.
                Click below to enter the
                administration panel.

            </p>


            <a
                href="login.php"
                class="login-btn"
            >
                Login to College Event Hub →
            </a>


            <div class="security">

                🔒 Local college system.
                No Gmail ID or Gmail password
                is required.

            </div>


        </div>


    </div>


</div>


</body>

</html>
```
