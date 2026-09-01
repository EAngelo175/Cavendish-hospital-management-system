<?php
require_once __DIR__ . "/config/app.php";

if (!empty($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "/dashboard.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e(HOSPITAL_NAME) ?> | Care that moves with you</title>
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/landing.css">
    </head>
    <body class="landing-page">
        <header class="landing-nav">
            <a class="landing-brand" href="<?= BASE_URL ?>/index.php">
                <img src="<?= e(HOSPITAL_LOGO) ?>" alt="<?= e(HOSPITAL_NAME) ?> logo" width="75" height="75">
                <span><?= e('CAVENDISH INTERNATIONAL HOSPITAL') ?></span>   
            </a>
            <a class="landing-login" href="<?= BASE_URL ?>/login.php">Log in</a>
        </header>

        <main>
            <section class="landing-hero">
                <div class="hero-copy">
                    <p class="eyebrow">COMPASSIONATE CARE, CLOSE TO HOME</p>
                    <h1>Better care begins with being <em>seen.</em></h1>
                    <p class="hero-lede">A calmer way to connect with trusted clinicians, manage appointments, and keep your care journey in one place.</p>
                    <div class="hero-actions">
                        <a class="landing-button primary" href="<?= BASE_URL ?>/patient/register.php">Patient sign up</a>
                        <a class="landing-button ghost" href="<?= BASE_URL ?>/login.php">Log in to portal</a>
                    </div>
                    <p class="hero-note"><span class="status-dot"></span> Welcoming patients every day</p>
                </div>
                <div class="photo-stage" aria-label="Hospital care moments">
                    <div class="photo photo-one"></div>
                    <div class="photo photo-two"></div>
                    <div class="photo photo-three"></div>
                    <div class="photo-caption"><strong>Care, with clarity.</strong><span>People-first medicine in a place that feels human.</span></div>
                </div>
            </section>

            <section class="landing-strip">
                <span>One trusted place for</span>
                <strong>Appointments</strong>
                <strong>Specialist care</strong>
                <strong>Health records</strong>
                <strong>Peace of mind</strong>
            </section>

        </main>
    </body>
</html>
