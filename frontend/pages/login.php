<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Het gebruik om reguit na home te gaan as jy al ingeteken was - maar dit was
// lastig om te demo, want jy moes eers die uitteken-skakel kry voor jy van
// rekening kon verwissel. Nou wanneer jy op die login bladsy land, teken dit
// jou outomaties uit enige rekening en wys die vorm weer.
// if (!empty($_SESSION['user_id'])) {
//     redirect(base_url('frontend/pages/home.php'));
// }
if (!empty($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['user_id']);
}

$activePage = 'login';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Please fill in both your email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            redirect(base_url('frontend/pages/home.php'));
        } else {
            $errors[] = 'That email and password combination doesn\'t match our records.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Log in - What's Left</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= e(base_url('frontend/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>

<nav class="login-nav-bar-top">
    <a href="<?= e(base_url('frontend/pages/login.php')) ?>" class="login-nav-logo-top">
        <img src="<?= e(base_url('frontend/assets/images/logo.svg')) ?>" alt="What's Left logo">
        What&rsquo;s Left
    </a>
    <div>
        <a href="<?= e(base_url('frontend/pages/signup.php')) ?>" class="login-nav-signup-link-top">Create an account</a>
    </div>
</nav>

<div class="login-authwrap-main">
    <div class="login-authside-main" style="background-image: linear-gradient(180deg, rgba(50,93,128,.55), rgba(50,93,128,.85)), url('<?= e(base_url('frontend/assets/images/priscilla-du-preez-g86airJZ4Gs-unsplash.jpg')) ?>');">
        <blockquote>
            &ldquo;You get one undo, ever. Log in and remember what that means.&rdquo;
        </blockquote>
        <p>What&rsquo;s Left</p>
    </div>

    <div class="login-authform-main">
        <div class="login-authcard-main">
            <div class="login-tinytext-top">Welcome back</div>
            <h1>Log in</h1>
            <p class="login-subtitle-tagline">Your record is exactly as you left it.</p>

            <?php foreach ($errors as $err): ?>
                <div class="uhohbox"><?= e($err) ?></div>
            <?php endforeach; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control login-input-email" id="email" name="email" placeholder="exsample@gmail.com" required>
                </div>
                <div class="mb-4">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control login-input-password" id="password" name="password" placeholder="Your password" required>
                </div>
                <button type="submit" class="btn login-button-submit-main w-100">Log in</button>
                <div class="text-center my-3 login-subtitle-or">OR</div>
                <p class="text-center login-subtitle-signup-prompt mb-0">
                    Don&rsquo;t have an account?
                    <a href="<?= e(base_url('frontend/pages/signup.php')) ?>">Sign up</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
