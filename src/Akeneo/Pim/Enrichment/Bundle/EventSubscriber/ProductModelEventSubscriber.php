<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use  Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductModelEventSubscriber implements EventSubscriberInterface
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
            StorageEvents::POST_SAVE => ['produceModelBusinessSaveEvent', 1000],
        ];
    }

    public function produceModelBusinessSaveEvent(GenericEvent $event): void
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
        if ($event->hasArgument('created') && true === $event->getArgument('created')) {
            $message = new ProductModelCreated($author, $data);
        } else {
            $message = new ProductModelUpdated($author, $data);
        }

        $this->messageBus->dispatch($message);
    }
}