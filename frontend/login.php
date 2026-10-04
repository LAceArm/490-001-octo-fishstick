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
<head><title>Test</title></head>
<body>
    <h1>Registration</h1>
    <button onclick="location.href='./register.php'" type="button">Register</button>
</body>
</html>