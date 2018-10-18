<?php

declare(strict_types=1);

namespace App\Command;

use Elastica\Client;
use Elastica\Document;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RdKafka\TopicConf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeTweetsCommand extends Command
{
    /**
     * @var Conf
     */
    private $kafkaConf;

    /**
     * @var Client
     */
    private $elasticaClient;

    public function __construct(Conf $kafkaConf, Client $elasticaClient)
    {
        parent::__construct();
        $this->kafkaConf = $kafkaConf;
        $this->elasticaClient = $elasticaClient;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:consume-tweets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $topicConf = new TopicConf();
        $topicConf->set('auto.offset.reset', 'smallest');
        $this->kafkaConf->setDefaultTopicConf($topicConf);

        /** @see https://arnaud-lb.github.io/php-rdkafka/phpdoc/rdkafka.examples-high-level-consumer.html */
        $consumer = new KafkaConsumer($this->kafkaConf);
        $consumer->subscribe(['tweets']);

        while (true) {
            $message = $consumer->consume(5000); // 5s
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    usleep(200000);
                    break;
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $this->handleMessage($output, $message);
                    $consumer->commitAsync($message);
                    break;
                default:
                    throw new \LogicException($message->errstr(), $message->err);
            }

            if (\function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
        }

        $consumer->unsubscribe();
    }

    /**
     * TODO : extract to a TweetMessageHandler.
     *
     * @param OutputInterface $output
     * @param Message         $message
     */
    private function handleMessage(OutputInterface $output, Message $message): void
    {
        /** @var array $tweet */
        $tweet = \json_decode($message->payload, true)['body'];
//        dump($message->topic_name, $message->partition, $message->key, $message->offset);
        $output->writeln(sprintf('Tweet %d consumed.', $tweet['id']));

        $index = $this->elasticaClient->getIndex('tweets');
        if (!$index->exists()) {
            $index->create([
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
                // analysis
            ]);
        }

        $type = $index->getType('tweet');
        $type->addDocument(new Document($tweet['id_str'], $tweet));
    }
}
