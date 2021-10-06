<?php

namespace App;

use Exception;
use Stomp\Transport\Frame;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueListenerCommand extends Command
{
    protected static $defaultName = 'queue:listen';

    protected function configure(): void
    {
        $this->setDescription('Listens to orders queue and prints messages.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $broker = new Broker('localhost', 61613);
        } catch (Exception $e) {
            $output->writeln('<error>Failed to connect to broker</error>');
            return Command::FAILURE;
        }
        $output->writeln('<comment>Connected to broker, listening for messages...</comment>');

        $broker->subscribeQueue('orders');

        while (true) {
            $message = $broker->read();
            if ($message instanceof Frame) {
                if ($message['type'] === 'terminate') {
                    $output->writeln('<comment>Received shutdown command</comment>');
                    return Command::SUCCESS;
                }
                $output->writeln('<info>Processed message: ' . $message->getBody() . '</info>');
                $broker->ack($message);
            }
            usleep(100000);
        }
    }
}