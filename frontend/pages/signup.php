<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect(base_url('frontend/pages/home.php'));
}

$errors = [];
$old = ['first_name' => '', 'last_name' => '', 'email' => '', 'dob' => '', 'looking_for' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['first_name']  = trim($_POST['first_name'] ?? '');
    $old['last_name']   = trim($_POST['last_name'] ?? '');
    $old['email']       = trim($_POST['email'] ?? '');
    $old['dob']         = trim($_POST['dob'] ?? '');
    $old['looking_for'] = trim($_POST['looking_for'] ?? '');
    $password           = $_POST['password'] ?? '';
    $confirmPassword    = $_POST['confirm_password'] ?? '';
    $agree              = isset($_POST['agree_rules']);

    if ($old['first_name'] === '' || $old['last_name'] === '' || $old['email'] === '' || $old['dob'] === '' || $old['looking_for'] === '') {
        $errors[] = 'Please fill in every field.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Your password needs to be at least 8 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    if ($old['dob'] !== '') {
        $age = calculate_age($old['dob']);
        if ($age < 18) {
            $errors[] = 'You must be 18 or older to join What\'s Left.';
        }
    }
    if (!$agree) {
        $errors[] = 'You need to acknowledge the Verdict Policy before joining.';
    }

    if (empty($errors)) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$old['email']]);
        if ($check->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (first_name, last_name, email, password_hash, dob, looking_for)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $old['first_name'], $old['last_name'], $old['email'], $hash, $old['dob'], $old['looking_for']
        ]);
        $newUserId = (int) $pdo->lastInsertId();

        // Default personality row so sliders have something to show later.
        $pdo->prepare('INSERT INTO user_personality (user_id) VALUES (?)')->execute([$newUserId]);

        $_SESSION['user_id'] = $newUserId;
        redirect(base_url('frontend/pages/edit_profile.php') . '?welcome=1');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign up - What's Left</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= e(base_url('frontend/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<nav class="wl-navbar">
    <a href="<?= e(base_url('frontend/pages/login.php')) ?>" class="wl-brand">
        <img src="<?= e(base_url('frontend/assets/images/logo.svg')) ?>" alt="What's Left logo">
        What&rsquo;s Left
    </a>
    <div>
        <a href="<?= e(base_url('frontend/pages/login.php')) ?>">Login</a>
    </div>
</nav>

<div class="wl-page" style="background: var(--navy); min-height: calc(100vh - 70px);">
    <div class="wl-auth-card mx-auto" style="max-width: 42rem;">
        <div class="text-center">
            <div class="wl-auth-eyebrow">Join What&rsquo;s Left</div>
            <h1 style="color:var(--white)">Create your account</h1>
            <p class="subtitle">Read carefully.</p>
        </div>

        <?php foreach ($errors as $err): ?>
            <div class="wl-alert-error"><?= e($err) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="first_name">First Name:</label>
                    <input type="text" class="form-control wl-input" id="first_name" name="first_name" placeholder="Sam" value="<?= e($old['first_name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name">Last Name:</label>
                    <input type="text" class="form-control wl-input" id="last_name" name="last_name" placeholder="Williams" value="<?= e($old['last_name']) ?>" required>
                </div>
                <div class="col-12">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control wl-input" id="email" name="email" placeholder="exsample@gmail.com" value="<?= e($old['email']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="dob">Date of birth:</label>
                    <input type="date" class="form-control wl-input" id="dob" name="dob" value="<?= e($old['dob']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="looking_for">Looking for:</label>
                    <select class="form-control wl-input" id="looking_for" name="looking_for" required>
                        <option value="" disabled <?= $old['looking_for'] === '' ? 'selected' : '' ?>>Select&hellip;</option>
                        <option value="Male" <?= $old['looking_for'] === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $old['looking_for'] === 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Everyone" <?= $old['looking_for'] === 'Everyone' ? 'selected' : '' ?>>Everyone</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control wl-input" id="password" name="password" placeholder="At least 8 characters" required>
                </div>
                <div class="col-md-6">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" class="form-control wl-input" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                </div>
            </div>

            <div class="wl-rules-box mt-4">
                <div class="wl-rules-title">BEFORE YOU JOIN &mdash; THE VERDICT POLICY</div>
                <ul>
                    <li>You may skip exactly one profile, ever.</li>
                    <li>You get exactly one undo, ever &mdash; spend it wisely.</li>
                    <li>Passing on someone requires a public, detailed reason.</li>
                    <li>Matching moves your account to a new shared space &mdash; you can&rsquo;t browse again unless it ends.</li>
                    <li>Nothing you do here can ever be deleted.</li>
                </ul>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="agree_rules" name="agree_rules" <?= isset($_POST['agree_rules']) ? 'checked' : '' ?> required>
                    <label class="form-check-label" for="agree_rules" style="color:#5c2a1a;">
                        I understand and agree these decisions are permanent.
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-wl-primary w-100 mt-4">Create Account</button>
            <div class="text-center my-3 subtitle">OR</div>
            <p class="text-center subtitle">
                Already have an account?
                <a href="<?= e(base_url('frontend/pages/login.php')) ?>">Log in</a>
            </p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
