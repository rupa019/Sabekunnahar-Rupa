<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = '';
$msg_type = '';

// ===== ADD ANIMAL =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_animal'])) {
    $type          = mysqli_real_escape_string($conn, $_POST['type']);
    $area_id       = (int)$_POST['area_id'];
    $health_status = mysqli_real_escape_string($conn, $_POST['health_status']);
    $is_vaccinated = mysqli_real_escape_string($conn, $_POST['is_vaccinated']);
    $is_sterilized = mysqli_real_escape_string($conn, $_POST['is_sterilized']);
    $age           = !empty($_POST['age']) ? (int)$_POST['age'] : 'NULL';
    $gender        = mysqli_real_escape_string($conn, $_POST['gender']);
    $feeding_time  = mysqli_real_escape_string($conn, $_POST['feeding_time']);

    $age_val = ($age === 'NULL') ? 'NULL' : $age;

    $sql = "INSERT INTO animals (type, area_id, health_status, is_vaccinated, is_sterilized, age, gender, feeding_time)
            VALUES ('$type', $area_id, '$health_status', '$is_vaccinated', '$is_sterilized', $age_val, '$gender', '$feeding_time')";

    if (mysqli_query($conn, $sql)) {
        $message  = 'প্রাণীটি সফলভাবে যোগ করা হয়েছে!';
        $msg_type = 'success';
    } else {
        $message  = 'সমস্যা হয়েছে: ' . mysqli_error($conn);
        $msg_type = 'error';
    }
}

// ===== DELETE ANIMAL =====
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM animals WHERE animal_id = $del_id");
    $message  = 'প্রাণীটি মুছে ফেলা হয়েছে।';
    $msg_type = 'success';
}

// ===== FETCH AREAS =====
$areas_result = mysqli_query($conn, "SELECT * FROM areas ORDER BY area_name");

// ===== FETCH ANIMALS =====
$filter_type   = isset($_GET['filter_type']) ? mysqli_real_escape_string($conn, $_GET['filter_type']) : '';
$filter_health = isset($_GET['filter_health']) ? mysqli_real_escape_string($conn, $_GET['filter_health']) : '';

$where = [];
if ($filter_type)   $where[] = "a.type = '$filter_type'";
if ($filter_health) $where[] = "a.health_status = '$filter_health'";
$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$animals_result = mysqli_query($conn,
    "SELECT a.*, ar.area_name FROM animals a
     LEFT JOIN areas ar ON a.area_id = ar.area_id
     $where_sql
     ORDER BY a.animal_id DESC"
);

$total = mysqli_num_rows($animals_result);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Animal Management – MeoWoof</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
    --green:   #2fa87e;
    --green-d: #1e8a64;
    --green-l: #edf7f3;
    --bg:      #eef4f0;
    --card:    #ffffff;
    --text:    #1e3a2f;
    --muted:   #6b8c7e;
    --border:  #d8ede5;
    --red:     #e05252;
    --yellow:  #f0a500;
    --blue:    #4a90d9;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Nunito', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
}

/* NAV */
nav {
    background: var(--green);
    padding: 14px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 3px 16px rgba(47,168,126,0.3);
    position: sticky; top: 0; z-index: 100;
}
.nav-brand { color:#fff; font-weight:900; font-size:1.2rem; display:flex; align-items:center; gap:8px; }
.nav-links a {
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    font-weight: 700;
    font-size: 0.88rem;
    margin-left: 20px;
    transition: color 0.2s;
}
.nav-links a:hover { color: #fff; }

/* MAIN LAYOUT */
.container { max-width: 1100px; margin: 0 auto; padding: 28px 20px; }

/* PAGE HEADER */
.page-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
}
.page-header h1 { font-size: 1.5rem; font-weight: 900; color: var(--text); }
.page-header p  { color: var(--muted); font-size: 0.88rem; margin-top: 2px; }

/* STAT CARDS */
.stats { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 24px; }
.stat-card {
    background: var(--card);
    border-radius: 14px;
    padding: 16px 22px;
    flex: 1; min-width: 130px;
    border: 1.5px solid var(--border);
    box-shadow: 0 2px 10px rgba(0,0,0,0.04);
}
.stat-card .num { font-size: 1.8rem; font-weight: 900; }
.stat-card .lbl { font-size: 0.78rem; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }
.stat-cat  .num { color: #f0a500; }
.stat-dog  .num { color: #4a90d9; }
.stat-sick .num { color: #e05252; }
.stat-all  .num { color: var(--green); }

/* ALERT */
.alert {
    padding: 13px 18px; border-radius: 12px;
    font-weight: 700; font-size: 0.9rem;
    margin-bottom: 22px; display: flex; align-items: center; gap: 10px;
}
.alert-success { background:#edfaf4; color:#1a7a50; border:1.5px solid #b2e8d0; }
.alert-error   { background:#fef0f0; color:#c0392b; border:1.5px solid #f5c6c6; }

/* FORM CARD */
.form-card {
    background: var(--card);
    border-radius: 18px;
    padding: 26px 28px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.07);
    margin-bottom: 28px;
    border: 1.5px solid var(--border);
}
.form-card h2 {
    font-size: 1.05rem; font-weight: 800; color: var(--text);
    margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}
.field { display: flex; flex-direction: column; gap: 6px; }
.field.full { grid-column: 1 / -1; }
label {
    font-size: 0.71rem; font-weight: 800; color: #3d5a50;
    letter-spacing: 0.08em; text-transform: uppercase;
}
input, select {
    padding: 10px 13px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    color: var(--text);
    background: #f7faf8;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    appearance: none;
}
input:focus, select:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(47,168,126,0.15);
    background: #fff;
}
input::placeholder { color: #b0c8be; }
.btn {
    padding: 11px 22px; border: none; border-radius: 10px;
    font-family: 'Nunito', sans-serif; font-weight: 800;
    font-size: 0.92rem; cursor: pointer; transition: all 0.2s;
}
.btn-green {
    background: var(--green); color: #fff;
    box-shadow: 0 3px 14px rgba(47,168,126,0.35);
}
.btn-green:hover { background: var(--green-d); transform: translateY(-1px); }

/* FILTER BAR */
.filter-bar {
    display: flex; gap: 12px; align-items: center;
    flex-wrap: wrap; margin-bottom: 18px;
}
.filter-bar select {
    padding: 9px 13px; min-width: 160px; font-size: 0.88rem;
}
.filter-bar .btn { padding: 9px 18px; font-size: 0.85rem; }
.btn-outline {
    background: #fff; color: var(--green);
    border: 1.5px solid var(--green);
}
.btn-outline:hover { background: var(--green-l); }

/* TABLE CARD */
.table-card {
    background: var(--card);
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.07);
    border: 1.5px solid var(--border);
}
.table-head {
    padding: 18px 24px;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1.5px solid var(--border);
}
.table-head h2 { font-size: 1rem; font-weight: 800; }
.count-badge {
    background: var(--green-l); color: var(--green);
    border: 1px solid #c0e8d5;
    padding: 4px 12px; border-radius: 20px;
    font-size: 0.8rem; font-weight: 800;
}
table { width: 100%; border-collapse: collapse; }
th {
    background: #f4f9f6;
    padding: 12px 16px;
    text-align: left;
    font-size: 0.72rem; font-weight: 800;
    color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em;
    border-bottom: 1.5px solid var(--border);
}
td {
    padding: 13px 16px;
    font-size: 0.88rem;
    border-bottom: 1px solid #f0f5f2;
    vertical-align: middle;
}
tr:last-child td { border-bottom: none; }
tr:hover td { background: #fafdf9; }

/* BADGES */
.badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px;
    font-size: 0.75rem; font-weight: 800;
}
.badge-cat    { background: #fff8e6; color: #b07800; border: 1px solid #f5dfa0; }
.badge-dog    { background: #e8f1fb; color: #2d6db5; border: 1px solid #b8d4f0; }
.badge-healthy   { background: #edfaf4; color: #1a7a50; border: 1px solid #b2e8d0; }
.badge-sick      { background: #fef0f0; color: #c0392b; border: 1px solid #f5c6c6; }
.badge-injured   { background: #fff4e6; color: #b05800; border: 1px solid #fcd9a8; }
.badge-pregnant  { background: #f5e8fb; color: #7b2f9e; border: 1px solid #dab8f0; }
.badge-yes { background: #edfaf4; color: #1a7a50; border: 1px solid #b2e8d0; }
.badge-no  { background: #f4f4f4; color: #888; border: 1px solid #ddd; }

/* DELETE BTN */
.btn-del {
    background: none; border: 1.5px solid #f5c6c6;
    color: var(--red); border-radius: 8px;
    padding: 5px 12px; font-size: 0.8rem;
    font-family: 'Nunito', sans-serif;
    font-weight: 700; cursor: pointer;
    transition: all 0.2s;
}
.btn-del:hover { background: #fef0f0; }

/* EMPTY */
.empty-state {
    text-align: center; padding: 48px 20px; color: var(--muted);
}
.empty-state .icon { font-size: 3rem; margin-bottom: 12px; }
.empty-state p { font-weight: 700; }

/* RESPONSIVE */
@media (max-width: 640px) {
    .form-grid { grid-template-columns: 1fr 1fr; }
    .stats { gap: 10px; }
    table { font-size: 0.8rem; }
    th, td { padding: 10px 10px; }
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
    <div class="nav-brand">🐾 MeoWoof</div>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="animals.php" style="color:#fff;">Animals</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">

    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h1>🐱🐶 Animal Management</h1>
            <p>Dhaka-র পথের প্রাণীদের তথ্য যোগ করুন ও পরিচালনা করুন</p>
        </div>
    </div>

    <!-- ALERT -->
    <?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?>">
        <?= $msg_type === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <?php
    $stat_all  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM animals"))[0] ?? 0;
    $stat_cat  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM animals WHERE type='cat'"))[0] ?? 0;
    $stat_dog  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM animals WHERE type='dog'"))[0] ?? 0;
    $stat_sick = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM animals WHERE health_status='sick' OR health_status='injured'"))[0] ?? 0;
    ?>
    <div class="stats">
        <div class="stat-card stat-all">
            <div class="num"><?= $stat_all ?></div>
            <div class="lbl">মোট প্রাণী</div>
        </div>
        <div class="stat-card stat-cat">
            <div class="num"><?= $stat_cat ?></div>
            <div class="lbl">🐱 বিড়াল</div>
        </div>
        <div class="stat-card stat-dog">
            <div class="num"><?= $stat_dog ?></div>
            <div class="lbl">🐶 কুকুর</div>
        </div>
        <div class="stat-card stat-sick">
            <div class="num"><?= $stat_sick ?></div>
            <div class="lbl">⚠️ অসুস্থ/আহত</div>
        </div>
    </div>

    <!-- ADD FORM -->
    <div class="form-card">
        <h2>➕ নতুন প্রাণী যোগ করুন</h2>
        <form method="POST">
            <div class="form-grid">
                <div class="field">
                    <label>ধরন (Type)</label>
                    <select name="type" required>
                        <option value="">বেছে নিন</option>
                        <option value="cat">🐱 বিড়াল (Cat)</option>
                        <option value="dog">🐶 কুকুর (Dog)</option>
                    </select>
                </div>
                <div class="field">
                    <label>এলাকা (Area)</label>
                    <select name="area_id" required>
                        <option value="">এলাকা বেছে নিন</option>
                        <?php if ($areas_result): while ($area = mysqli_fetch_assoc($areas_result)): ?>
                        <option value="<?= $area['area_id'] ?>"><?= htmlspecialchars($area['area_name']) ?></option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
                <div class="field">
                    <label>স্বাস্থ্য অবস্থা</label>
                    <select name="health_status" required>
                        <option value="">বেছে নিন</option>
                        <option value="healthy">✅ সুস্থ (Healthy)</option>
                        <option value="sick">🤒 অসুস্থ (Sick)</option>
                        <option value="injured">🩹 আহত (Injured)</option>
                        <option value="pregnant">🤰 গর্ভবতী (Pregnant)</option>
                    </select>
                </div>
                <div class="field">
                    <label>টিকা দেওয়া?</label>
                    <select name="is_vaccinated" required>
                        <option value="yes">✅ হ্যাঁ (Yes)</option>
                        <option value="no">❌ না (No)</option>
                    </select>
                </div>
                <div class="field">
                    <label>বন্ধ্যাকরণ?</label>
                    <select name="is_sterilized" required>
                        <option value="yes">✅ হ্যাঁ (Yes)</option>
                        <option value="no">❌ না (No)</option>
                    </select>
                </div>
                <div class="field">
                    <label>বয়স (বছর, optional)</label>
                    <input type="number" name="age" min="0" max="30" placeholder="যদি জানা থাকে">
                </div>
                <div class="field">
                    <label>লিঙ্গ (Gender)</label>
                    <select name="gender" required>
                        <option value="">বেছে নিন</option>
                        <option value="male">♂ পুরুষ (Male)</option>
                        <option value="female">♀ মহিলা (Female)</option>
                        <option value="unknown">❓ অজানা</option>
                    </select>
                </div>
                <div class="field">
                    <label>খাওয়ানোর সময়</label>
                    <input type="time" name="feeding_time" required>
                </div>
                <div class="field full" style="display:flex; justify-content:flex-end; margin-top:4px;">
                    <button type="submit" name="add_animal" class="btn btn-green">➕ প্রাণী যোগ করুন</button>
                </div>
            </div>
        </form>
    </div>

    <!-- FILTER -->
    <div class="filter-bar">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <select name="filter_type">
                <option value="">সব ধরন</option>
                <option value="cat" <?= $filter_type==='cat'?'selected':'' ?>>🐱 বিড়াল</option>
                <option value="dog" <?= $filter_type==='dog'?'selected':'' ?>>🐶 কুকুর</option>
            </select>
            <select name="filter_health">
                <option value="">সব অবস্থা</option>
                <option value="healthy"  <?= $filter_health==='healthy'?'selected':'' ?>>✅ সুস্থ</option>
                <option value="sick"     <?= $filter_health==='sick'?'selected':'' ?>>🤒 অসুস্থ</option>
                <option value="injured"  <?= $filter_health==='injured'?'selected':'' ?>>🩹 আহত</option>
                <option value="pregnant" <?= $filter_health==='pregnant'?'selected':'' ?>>🤰 গর্ভবতী</option>
            </select>
            <button type="submit" class="btn btn-green">ফিল্টার করুন</button>
            <a href="animals.php" class="btn btn-outline">রিসেট</a>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table-card">
        <div class="table-head">
            <h2>প্রাণীদের তালিকা</h2>
            <span class="count-badge"><?= $total ?> টি প্রাণী</span>
        </div>
        <?php if ($total > 0): ?>
        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ধরন</th>
                    <th>এলাকা</th>
                    <th>স্বাস্থ্য</th>
                    <th>টিকা</th>
                    <th>বন্ধ্যা</th>
                    <th>বয়স</th>
                    <th>লিঙ্গ</th>
                    <th>খাওয়ার সময়</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($animal = mysqli_fetch_assoc($animals_result)): ?>
                <tr>
                    <td><strong>#<?= $animal['animal_id'] ?></strong></td>
                    <td>
                        <?php if ($animal['type'] === 'cat'): ?>
                            <span class="badge badge-cat">🐱 বিড়াল</span>
                        <?php else: ?>
                            <span class="badge badge-dog">🐶 কুকুর</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($animal['area_name'] ?? '—') ?></td>
                    <td>
                        <?php
                        $h = $animal['health_status'];
                        $map = [
                            'healthy'  => ['badge-healthy',  '✅ সুস্থ'],
                            'sick'     => ['badge-sick',     '🤒 অসুস্থ'],
                            'injured'  => ['badge-injured',  '🩹 আহত'],
                            'pregnant' => ['badge-pregnant', '🤰 গর্ভবতী'],
                        ];
                        [$cls, $lbl] = $map[$h] ?? ['badge-no', $h];
                        ?>
                        <span class="badge <?= $cls ?>"><?= $lbl ?></span>
                    </td>
                    <td><span class="badge badge-<?= $animal['is_vaccinated'] ?>"><?= $animal['is_vaccinated'] === 'yes' ? '✅ হ্যাঁ' : '❌ না' ?></span></td>
                    <td><span class="badge badge-<?= $animal['is_sterilized'] ?>"><?= $animal['is_sterilized'] === 'yes' ? '✅ হ্যাঁ' : '❌ না' ?></span></td>
                    <td><?= $animal['age'] !== null ? $animal['age'] . ' বছর' : '—' ?></td>
                    <td>
                        <?php
                        $g = $animal['gender'];
                        echo $g === 'male' ? '♂ পুরুষ' : ($g === 'female' ? '♀ মহিলা' : '❓');
                        ?>
                    </td>
                    <td><?= $animal['feeding_time'] ? date('h:i A', strtotime($animal['feeding_time'])) : '—' ?></td>
                    <td>
                        <a href="animals.php?delete=<?= $animal['animal_id'] ?>"
                           onclick="return confirm('এই প্রাণীর তথ্য মুছে ফেলবেন?')"
                           class="btn-del">🗑 মুছুন</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="icon">🐾</div>
            <p>এখনো কোনো প্রাণী যোগ করা হয়নি</p>
            <p style="font-size:0.85rem; margin-top:6px; font-weight:400;">উপরের ফর্ম দিয়ে প্রথম প্রাণী যোগ করুন!</p>
        </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
