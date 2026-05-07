<?php
require_once 'config.php';

// Login না থাকলে ফেরত পাঠাও
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$name = htmlspecialchars($_SESSION['user_name']);
$role = htmlspecialchars($_SESSION['user_role']);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – MeoWoof</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            background: #eef4f0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 16px;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.08);
            text-align: center;
            max-width: 420px;
            width: 100%;
        }
        .icon { font-size: 3rem; margin-bottom: 12px; }
        h2 { color: #1a6b50; font-size: 1.5rem; font-weight: 800; }
        p { color: #6b8c7e; margin-top: 8px; font-size: 0.95rem; }
        .badge {
            display: inline-block;
            margin-top: 14px;
            background: #edfaf4;
            color: #1a7a50;
            border: 1px solid #b2e8d0;
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 28px;
            padding: 12px 28px;
            background: #2fa87e;
            color: #fff;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.95rem;
            box-shadow: 0 4px 16px rgba(47,168,126,0.35);
            transition: background 0.2s;
        }
        .logout-btn:hover { background: #259068; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">🐾</div>
    <h2>স্বাগতম, <?= $name ?>!</h2>
    <p>আপনি সফলভাবে MeoWoof এ লগইন করেছেন।</p>
    <div class="badge">Role: <?= $role ?></div>
    <br>
    <a href="logout.php" class="logout-btn">Logout করুন</a>
</div>
</body>
</html>
