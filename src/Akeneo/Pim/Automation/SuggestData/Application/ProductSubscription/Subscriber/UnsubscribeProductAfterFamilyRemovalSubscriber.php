<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service\DoesPersistedProductHaveFamilyInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class UnsubscribeProductAfterFamilyRemovalSubscriber implements EventSubscriberInterface
{
    /** @var DoesPersistedProductHaveFamilyInterface */
    private $doesPersistedProductHaveFamily;

    /** @var UnsubscribeProductHandler */
    private $unsubscribeProductHandler;

    /**
     * @param DoesPersistedProductHaveFamilyInterface $doesPersistedProductHaveFamily
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     */
    public function __construct(
        DoesPersistedProductHaveFamilyInterface $doesPersistedProductHaveFamily,
        UnsubscribeProductHandler $unsubscribeProductHandler
    ) {
        $this->doesPersistedProductHaveFamily = $doesPersistedProductHaveFamily;
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'unsubscribeSingleProduct',
            StorageEvents::PRE_SAVE_ALL => 'unsubscribeMultipleProducts',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function unsubscribeSingleProduct(GenericEvent $event): void
    {
        if (false === $event->getArgument('unitary')) {
            return;
        }

        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (null === $product->getId() || null !== $product->getFamily()) {
            return;
        }

        if ($this->doesPersistedProductHaveFamily->check($product)) {
            $command = new UnsubscribeProductCommand($product->getId());
            try {
                $this->unsubscribeProductHandler->handle($command);
            } catch (ProductSubscriptionException $exception) {
                return;
            }
        }
    }

    /**
     * @param GenericEvent $event
     */
    public function unsubscribeMultipleProducts(GenericEvent $event): void
    {
        if (true === $event->getArgument('unitary')) {
            return;
        }

        $products = $event->getSubject();
        foreach ($products as $product) {
            if (!$product instanceof ProductInterface) {
                continue;
            }

            if (null === $product->getId() || null !== $product->getFamily()) {
                continue;
            }

            if ($this->doesPersistedProductHaveFamily->check($product)) {
                $command = new UnsubscribeProductCommand($product->getId());
                try {
                    $this->unsubscribeProductHandler->handle($command);
                } catch (ProductSubscriptionException $exception) {
                    continue;
                }
            }
        }
    }
}
