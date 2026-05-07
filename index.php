<?php
require_once 'config.php';

// Already logged in হলে dashboard এ redirect
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'login';
$error = '';
$success = '';

// ===== LOGIN LOGIC =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email    = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'ইমেইল এবং পাসওয়ার্ড দিন।';
        $tab = 'login';
    } else {
        $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'পাসওয়ার্ড ভুল হয়েছে।';
                $tab = 'login';
            }
        } else {
            $error = 'এই ইমেইল দিয়ে কোনো অ্যাকাউন্ট নেই।';
            $tab = 'login';
        }
    }
}

// ===== REGISTER LOGIC =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $full_name = trim(mysqli_real_escape_string($conn, $_POST['full_name']));
    $email     = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $phone     = trim(mysqli_real_escape_string($conn, $_POST['phone']));
    $role      = mysqli_real_escape_string($conn, $_POST['role']);
    $address   = trim(mysqli_real_escape_string($conn, $_POST['address']));
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $tab = 'register';

    // Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($password)) {
        $error = 'সব ঘর পূরণ করুন।';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'সঠিক ইমেইল ঠিকানা দিন।';
    } elseif (strlen($password) < 6) {
        $error = 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।';
    } elseif ($password !== $confirm) {
        $error = 'পাসওয়ার্ড দুটো মিলছে না।';
    } else {
        // Check duplicate email
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'এই ইমেইল আগেই ব্যবহার হয়েছে।';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (full_name, email, phone, role, address, password) 
                    VALUES ('$full_name', '$email', '$phone', '$role', '$address', '$hashed')";
            if (mysqli_query($conn, $sql)) {
                $success = 'অ্যাকাউন্ট তৈরি হয়েছে! এখন লগইন করুন।';
                $tab = 'login';
            } else {
                $error = 'একটি সমস্যা হয়েছে: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeoWoof – Stray Animal Management</title>
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

        .wrapper {
            width: 100%;
            max-width: 420px;
        }

        /* HEADER */
        .brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand-icon {
            width: 80px;
            height: 80px;
            background: #2fa87e;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 36px;
            box-shadow: 0 8px 24px rgba(47,168,126,0.3);
        }
        .brand h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #1a6b50;
            letter-spacing: -0.5px;
        }
        .brand p {
            color: #6b8c7e;
            font-size: 0.9rem;
            margin-top: 4px;
        }

        /* CARD */
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 28px 28px 32px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.08);
        }

        /* TABS */
        .tabs {
            display: flex;
            background: #f0f5f2;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 26px;
        }
        .tab-btn {
            flex: 1;
            padding: 11px 0;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            color: #7a9e90;
            cursor: pointer;
            transition: all 0.25s;
        }
        .tab-btn.active {
            background: #2fa87e;
            color: #fff;
            box-shadow: 0 3px 12px rgba(47,168,126,0.4);
        }

        /* FORM */
        .field { margin-bottom: 18px; }
        .field-row { display: flex; gap: 14px; }
        .field-row .field { flex: 1; }

        label {
            display: block;
            font-size: 0.72rem;
            font-weight: 800;
            color: #3d5a50;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        input, select {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #dde8e3;
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.95rem;
            color: #2d4a3e;
            background: #f7faf8;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            appearance: none;
        }
        input:focus, select:focus {
            border-color: #2fa87e;
            box-shadow: 0 0 0 3px rgba(47,168,126,0.15);
            background: #fff;
        }
        input::placeholder { color: #b0c8be; }

        .pass-wrap {
            position: relative;
        }
        .pass-wrap input { padding-right: 44px; }
        .eye-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            color: #7a9e90;
            padding: 4px;
        }

        /* SELECT arrow */
        .select-wrap { position: relative; }
        .select-wrap select { padding-right: 36px; }
        .select-wrap::after {
            content: "▾";
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #7a9e90;
            pointer-events: none;
            font-size: 0.9rem;
        }

        /* BUTTON */
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: #2fa87e;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-family: 'Nunito', sans-serif;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(47,168,126,0.35);
        }
        .btn-primary:hover {
            background: #259068;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(47,168,126,0.45);
        }
        .btn-primary:active { transform: translateY(0); }

        /* ALERTS */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .alert-error { background: #fef0f0; color: #c0392b; border: 1px solid #f5c6c6; }
        .alert-success { background: #edfaf4; color: #1a7a50; border: 1px solid #b2e8d0; }

        /* FORM SECTIONS */
        .form-section { display: none; }
        .form-section.active { display: block; }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Brand Header -->
    <div class="brand">
        <div class="brand-icon">🐾</div>
        <h1>MeoWoof</h1>
        <p>Stray Animal Management System · Dhaka</p>
    </div>

    <!-- Card -->
    <div class="card">
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn <?= $tab === 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Login</button>
            <button class="tab-btn <?= $tab === 'register' ? 'active' : '' ?>" onclick="switchTab('register')">Register</button>
        </div>

        <!-- Alert Messages -->
        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- ====== LOGIN FORM ====== -->
        <div class="form-section <?= $tab === 'login' ? 'active' : '' ?>" id="section-login">
            <form method="POST" action="">
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="you@example.com" required
                           value="<?= isset($_POST['email']) && isset($_POST['login']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="field">
                    <label>Password</label>
                    <div class="pass-wrap">
                        <input type="password" name="password" id="login-pass" placeholder="••••••••" required>
                        <button type="button" class="eye-btn" onclick="togglePass('login-pass', this)">👁</button>
                    </div>
                </div>
                <button type="submit" name="login" class="btn-primary">Login</button>
            </form>
        </div>

        <!-- ====== REGISTER FORM ====== -->
        <div class="form-section <?= $tab === 'register' ? 'active' : '' ?>" id="section-register">
            <form method="POST" action="">
                <div class="field">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Your name" required
                           value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="you@example.com" required
                               value="<?= isset($_POST['email']) && isset($_POST['register']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                    <div class="field">
                        <label>Phone</label>
                        <input type="tel" name="phone" placeholder="01X-XXXXXXX" required
                               value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                    </div>
                </div>

                <div class="field">
                    <label>Role</label>
                    <div class="select-wrap">
                        <select name="role">
                            <option value="Donator" <?= (isset($_POST['role']) && $_POST['role'] === 'Donator') ? 'selected' : '' ?>>Donator</option>
                            <option value="Volunteer" <?= (isset($_POST['role']) && $_POST['role'] === 'Volunteer') ? 'selected' : '' ?>>Volunteer</option>
                            <option value="Vet" <?= (isset($_POST['role']) && $_POST['role'] === 'Vet') ? 'selected' : '' ?>>Vet</option>
                            <option value="Admin" <?= (isset($_POST['role']) && $_POST['role'] === 'Admin') ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label>Address</label>
                    <input type="text" name="address" placeholder="Area, Dhaka" required
                           value="<?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?>">
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Password</label>
                        <div class="pass-wrap">
                            <input type="password" name="password" id="reg-pass" placeholder="Min 6 chars" required>
                            <button type="button" class="eye-btn" onclick="togglePass('reg-pass', this)">👁</button>
                        </div>
                    </div>
                    <div class="field">
                        <label>Confirm Password</label>
                        <div class="pass-wrap">
                            <input type="password" name="confirm_password" id="reg-pass2" placeholder="Repeat password" required>
                            <button type="button" class="eye-btn" onclick="togglePass('reg-pass2', this)">👁</button>
                        </div>
                    </div>
                </div>

                <button type="submit" name="register" class="btn-primary">Create account</button>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
        document.querySelector('#section-' + tab).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    function togglePass(id, btn) {
        const input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = '🙈';
        } else {
            input.type = 'password';
            btn.textContent = '👁';
        }
    }
</script>
</body>
</html>
