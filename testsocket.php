<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

// Create a Websocket server
$ws_worker = new Worker("websocket://0.0.0.0:2346");

// 4 processes
$ws_worker->count = 1;

// Emitted when new connection come
$ws_worker->onConnect = function($connection)
{
    echo "New connection\n";
 };

// Emitted when data received
$ws_worker->onMessage = function($connection, $data)use($ws_worker)
{
    // Send hello $data
    //$connection->send('hello ' . $data);
    foreach($ws_worker->connections as $connection)
    {
        $connection->send($data);
    }
};

// Emitted when connection closed
$ws_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();