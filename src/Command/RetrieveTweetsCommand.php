<?php

declare(strict_types=1);

namespace App\Command;

use Abraham\TwitterOAuth\TwitterOAuth;
use Endroid\Twitter\Client;
use Enqueue\RdKafka\RdKafkaConnectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RetrieveTweetsCommand extends Command
{
    /**
     * @var RdKafkaConnectionFactory
     */
    private $kafkaConnection;

    /**
     * @var Client
     */
    private $twitter;

    public function __construct(RdKafkaConnectionFactory $kafkaConnection, TwitterOAuth $twitter)
    {
        parent::__construct();
        $this->kafkaConnection = $kafkaConnection;
        $this->twitter = $twitter;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:retrieve-tweets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        // TODO : remove endroid/twitter, use https://github.com/jublo/codebird-php

        $lastId = null;

        $context = $this->kafkaConnection->createContext();
        while (true) {
            $tweets = $this->twitter->get('search/tweets', [
                'q' => '#OVHSummit',
                'since_id' => $lastId,
            ]);

            $topic = $context->createTopic('tweets');

            foreach ($tweets->statuses as $tweet) {
                $mess = $context->createMessage((array) $tweet);
                //$mess->setKey('my_key');
                $context->createProducer()->send($topic, $mess);
                $output->writeln(sprintf('Tweet %d sent.', $tweet->id));

                if ($lastId === null || $tweet->id > $lastId) {
                    $lastId = $tweet->id;
                }
            }

            usleep(5000*1000); // 3s
            if (\function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
        }
        $context->close();
    }
}
