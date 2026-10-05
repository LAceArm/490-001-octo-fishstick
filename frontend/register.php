<?php

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Make sure both passwords match
    if ($password !== $confirmPassword) {

        $error = 'Passwords do not match';

    } else {

        chdir(__DIR__ . '/../BrokerComms');

        require_once('path.inc');
        require_once('get_host_info.inc');
        require_once('rabbitMQLib.inc');

        $client = new rabbitMQClient("WebServer.ini", "frontEnd");

        $reply = $client->send_request([
            'type' => 'register',
            'username' => $username,
            'password' => $password,
        ]);

        if ($reply) {

            // Registration successful
            header('Location: login.php');
            exit;

        } else {

            $error = 'Registration failed';

        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="login-box">

        <h1>Register</h1>

        <p class="welcome">
            Create your account.
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
                placeholder="Choose a username"
                required
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Choose a password"
                required
            >

            <label for="confirm_password">Confirm Password</label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                placeholder="Confirm your password"
                required
            >

            <button class="login-button" type="submit">
                Register
            </button>

        </form>

        <div class="register-section">

            <p>Already have an account?</p>

            <button
                class="register-button"
                onclick="location.href='./login.php'"
                type="button"
            >
                Back to Login
            </button>

        </div>

    </div>

</body>

</html>