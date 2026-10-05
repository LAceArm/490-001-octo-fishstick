<?php
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    chdir(__DIR__ . '/../BrokerComms');
    require_once('path.inc');
    require_once('get_host_info.inc');
    require_once('rabbitMQLib.inc');

    $client = new rabbitMQClient("WebServer.ini", "frontEnd");
    $reply = $client->send_request([
        'type' => 'login',
        'username' => $_POST['username'],
        'password' => $_POST['password'],
    ]);

    if ($reply) {
        $_SESSION['loggedin'] = true;
        header('Location: home.php');
        exit;
    }
    $error = 'Invalid username or password';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-box">
        <h1>Login</h1>
        <p class="welcome">
            Welcome back! Please sign in to continue.
        </p>
        <?php if ($error): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter your username"
                required
            >
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >
            <button class="login-button" type="submit">
                Login
            </button>

        </form>
        <div class="register-section">
            <p>Don't have an account?</p>
            <button
                class="register-button"
                onclick="location.href='./register.php'"
                type="button"
            >
                Create an account
            </button>
        </div>
    </div>
</body>
</html>