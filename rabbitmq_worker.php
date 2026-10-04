<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/request_processor.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$config = parse_ini_file("SQLMQ.ini", true);
$sqlServer = $config["sqlServer"];

$connection = new AMQPStreamConnection(
    $sqlServer["BROKER_HOST"],
    $sqlServer["BROKER_PORT"],
    $sqlServer["USER"],
    $sqlServer["PASSWORD"],
    $sqlServer["VHOST"]
);

$channel = $connection->channel();

$channel->exchange_declare(
    $sqlServer["EXCHANGE"],
    $sqlServer["EXCHANGE_TYPE"],
    false,
    true,
    false
);

$channel->queue_declare(
    $sqlServer["QUEUE"],
    false,
    true,
    false,
    true
);

$channel->queue_bind(
    $sqlServer["QUEUE"],
    $sqlServer["EXCHANGE"]
);

echo "Seif SQL/Auth worker is waiting for requests...\n";

$callback = function ($msg) {

    echo "Received request\n";

    $request = json_decode($msg->body, true);

    $response = requestProcessor($request);

    echo "Response:\n";
    print_r($response);
};

$channel->basic_consume(
    $sqlServer["QUEUE"],
    "",
    false,
    true,
    false,
    false,
    $callback
);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();

?>
