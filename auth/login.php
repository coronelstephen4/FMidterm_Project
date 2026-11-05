<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: /FMidterm_Project/');
    exit;
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) && is_string($_POST['username'])
        ? trim($_POST['username'])
        : '';
    $password = isset($_POST['password']) && is_string($_POST['password'])
        ? $_POST['password']
        : '';

    if ($username === '' || $password === '') {
        $_SESSION['error'] = 'Enter your username and password.';
        header('Location: /FMidterm_Project/auth/login.php');
        exit;
    }

    try {
        $database = new Database();
        $userModel = new User($database->connect());
        $user = $userModel->getByUsername($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['user_id'];
            $_SESSION['role'] = $user['role'];

            header('Location: /FMidterm_Project/');
            exit;
        }

        $_SESSION['error'] = 'Invalid username or password.';
    } catch (Throwable) {
        $_SESSION['error'] = 'Login is temporarily unavailable. Please try again later.';
    }

    header('Location: /FMidterm_Project/auth/login.php');
    exit;
}

$pageTitle = 'Sign in';
require __DIR__ . '/../includes/header.php';
?>
<section class="mx-auto max-w-md rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <h1 class="text-2xl font-bold text-slate-900">Sign in</h1>
    <p class="mt-2 text-sm text-slate-600">Use your AIS account to continue.</p>

    <form method="post" action="/FMidterm_Project/auth/login.php" class="mt-6 space-y-5">
        <div>
            <label for="username" class="mb-1 block text-sm font-medium text-slate-700">Username</label>
            <input type="text" id="username" name="username" required autocomplete="username"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600">
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600">
        </div>

        <button type="submit"
                class="w-full rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
            Sign in
        </button>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
