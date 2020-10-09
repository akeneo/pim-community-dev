<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
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
final class DispatchProductModelBusinessEventSubscriber implements EventSubscriberInterface
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
            StorageEvents::POST_SAVE => ['produceBusinessSaveEvent', 1000],
            StorageEvents::POST_REMOVE => ['produceBusinessRemoveEvent', 1000],
        ];
    }

    public function produceBusinessSaveEvent(GenericEvent $event): void
    {
        /** @var ProductModelInterface */
        $productModel = $event->getSubject();

        if (false === $productModel instanceof ProductModelInterface) {
            return;
        }

        if (null === $user = $this->security->getUser()) {
            throw new \LogicException('User should not be null.');
        }

        $author = $user->getUsername();
        $data = $this->normalizer->normalize($productModel, 'standard');

        $message = null;

        if ($event->hasArgument('is_new') && true === $event->getArgument('is_new')) {
            $message = new ProductModelCreated($author, $data);
        } else {
            $message = new ProductModelUpdated($author, $data);
        }

        $this->messageBus->dispatch($message);
    }

    public function produceBusinessRemoveEvent(GenericEvent $event): void
    {
        /** @var ProductInterface */
        $product = $event->getSubject();
        if (false === $product instanceof ProductModelInterface) {
            return;
        }

        if (null === $user = $this->security->getUser()) {
            throw new \LogicException('User should not be null.');
        }

        $author = $user->getUsername();
        $data = $this->normalizer->normalize($product, 'standard');

        $message = new ProductModelRemoved($author, $data);

        $this->messageBus->dispatch($message);
    }
}
