<?php
require_once __DIR__ . '/vendor/autoload.php';
use Workerman\Worker;

global $ws_worker;

$context = array(
    // 更多ssl选项请参考手册 http://php.net/manual/zh/context.ssl.php
    'ssl' => array(
        // 请使用绝对路径
        'local_cert'                 => '/usr/local/nginx/conf/cert/1540508248174.pem', // 也可以是crt文件
        'local_pk'                   => '/usr/local/nginx/conf/cert/1540508248174.key',
        'verify_peer'                => false,
        // 'allow_self_signed' => true, //如果是自签名证书需要开启此选项
    )
);
// Create a Websocket server
$ws_worker = new Worker("websocket://0.0.0.0:2346",$context);
$ws_worker->transport = 'ssl';

// 4 processes
$ws_worker->count = 1;

$ws_worker->onWorkerStart = function()
{
	$inner_text_worker = new Worker('Text://0.0.0.0:2347');
	$inner_text_worker->onMessage = function($connection, $buffer)
    {
    	global $ws_worker;
		foreach($ws_worker->connections as $c)
	    {
	        $c->send($buffer);
	    }
    };
    $inner_text_worker->listen();
};

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