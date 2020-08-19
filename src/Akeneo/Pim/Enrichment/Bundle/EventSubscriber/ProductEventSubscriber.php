<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Bundle\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductEventSubscriber implements EventSubscriberInterface
{
    private $security;
    private $normalizer;
    private $messageBus;

    public function __construct(Security $security, NormalizerInterface $normalizer, MessageBusInterface $messageBus)
    {
        $this->security = $security;
        $this->normalizer = $normalizer;
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => ['produceBusinessEvent', 1000],
        ];
    }

    public function produceBusinessEvent(GenericEvent $event): void
    {
        /** @var ProductInterface */
        $product = $event->getSubject();
        if (false === $product instanceof ProductInterface) {
            return;
        }

        if (null === $user = $this->security->getUser()) {
            // Throw | Skip | Ignore ?
            return;
        }

        $author = $user->getUsername();
        $data = $this->normalizer->normalize($product);

        $message = null;
        if ($event->hasArgument('created') && true === $event->getArgument('created')) {
            $message = new ProductCreated($author, $data);
        } else {
            $message = new ProductUpdated($author, $data);
        }

        $this->messageBus->dispatch($message);
    }
}
