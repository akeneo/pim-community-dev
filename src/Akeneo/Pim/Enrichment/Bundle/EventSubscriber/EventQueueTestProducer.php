<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Interop\Queue\Context;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EventQueueTestProducer implements EventSubscriberInterface
{
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => ['produceTestMessage', 1000],
        ];
    }

    public function produceTestMessage(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $topic = $this->context->createTopic('product');
        $message = $this->context->createMessage('Hello world!');

        $this->context->createProducer()->send($topic, $message);
    }
}
