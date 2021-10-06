<?php

namespace App;

use Stomp\Client;
use Stomp\Exception\ConnectionException;
use Stomp\Network\Connection;
use Stomp\Network\Observer\HeartbeatEmitter;
use Stomp\StatefulStomp;
use Stomp\Transport\Frame;
use Stomp\Transport\Message;

class Broker
{
    // The internal Stomp client
    private StatefulStomp $client;
    // A list of subscriptions held by this broker
    private array $subscriptions = [];

    /**
     * For our constructor, we'll pass in the hostname (or IP address) and port number.
     * @throws ConnectionException|\Stomp\Exception\StompException
     */
    public function __construct(string $host, int $port)
    {
        $connection = new Connection('tcp://' . $host . ':' . $port);
        $client = new Client($connection);
        // Once we've created the Stomp connection and client, we will add a heartbeat
        // to periodically let ActiveMQ know our connection is alive and healthy.
        $client->setHeartbeat(500);
        $connection->setReadTimeout(0, 250000);
        // We add a HeartBeatEmitter and attach it to the connection to automatically send these signals.
        $emitter = new HeartbeatEmitter($client->getConnection());
        $client->getConnection()->getObservers()->addObserver($emitter);
        // Lastly, we create our internal Stomp client which will be used in our methods to interact with ActiveMQ.
        $this->client = new StatefulStomp($client);
        $client->connect();
    }

    public function sendQueue(string $queueName, string $message, array $headers = []): bool
    {
        $destination = '/queue/' . $queueName;
        return $this->client->send($destination, new Message($message, $headers + ['persistent' => 'true']));
    }

    public function subscribeQueue(string $queueName, ?string $selector = null): void
    {
        $destination = '/queue/' . $queueName;
        $this->subscriptions[$destination] = $this->client->subscribe($destination, $selector, 'client-individual');
    }

    public function unsubscribeQueue(?string $queueName = null): void
    {
        if ($queueName) {
            $destination = '/queue/' . $queueName;
            if (isset($this->subscriptions[$destination])) {
                $this->client->unsubscribe($this->subscriptions[$destination]);
            }
        } else {
            $this->client->unsubscribe();
        }
    }

    public function read(): ?Frame
    {
        return ($frame = $this->client->read()) ? $frame : null;
    }

    public function ack(Frame $message): void
    {
        $this->client->ack($message);
    }

    public function nack(Frame $message): void
    {
        $this->client->nack($message);
    }
}