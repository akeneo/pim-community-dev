<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\MessageProcessor;

use Enqueue\Client\TopicSubscriberInterface;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;

class ProductEventProcessor implements Processor, TopicSubscriberInterface
{
    public static function getSubscribedTopics()
    {
        return ['product'];
    }

    public function process(Message $message, Context $context)
    {
        dd($message);

        return self::ACK;
    }
}
