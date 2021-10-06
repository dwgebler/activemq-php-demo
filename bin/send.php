<?php

use App\Broker;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $broker = new Broker('localhost', 61613);
} catch (Exception $e) {
    echo "Failed to connect to broker\n";
    exit(1);
}

$broker->sendQueue('orders', json_encode([
    'items' => [
        [
            'code' => '3AF09',
            'description' => 'Jeans',
            'size' => 'M',
            'colour' => 'Yellow',
            'price' => '24.99',
        ],
        [
            'code' => '3AF16',
            'description' => 'Sweatshirt',
            'size' => 'M',
            'colour' => 'Black',
            'price' => '32.49',
        ],
    ],
    'customer' => 1,
]),
    [
        'type' => 'order',
    ]);

$broker->sendQueue('orders', json_encode([
    'items' => [
        'code' => '3AF11',
        'description' => 'T-Shirt',
        'size' => 'M',
        'colour' => 'Grey',
        'price' => '11.99'
    ],
    'customer' => 2,
]),
    [
        'type' => 'order',
    ]);

exit("Messages sent\n");