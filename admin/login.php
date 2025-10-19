<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $valid_username = 'admin';
    $valid_password = 'A9x!z'; 

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit();
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="bg-gray-800 p-8 rounded-xl shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-white mb-6 text-center">Admin Login</h2>
        <?php if ($error): ?>
            <div class="bg-red-500 text-white px-4 py-2 rounded mb-4 text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Username</label>
                <input name="username" type="text" required class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Enter username">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                <input name="password" type="password" required class="w-full bg-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" placeholder="Enter password">
            </div>
            <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white px-4 py-3 rounded-lg font-semibold transition">Login</button>
        </form>
    </div>
</body>
</html>
