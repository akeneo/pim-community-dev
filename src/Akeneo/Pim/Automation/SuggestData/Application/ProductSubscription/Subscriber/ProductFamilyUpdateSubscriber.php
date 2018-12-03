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
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UpdateSubscriptionFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UpdateSubscriptionFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Query\Product\SelectProductFamilyIdQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductFamilyUpdateSubscriber implements EventSubscriberInterface
{
    /** @var SelectProductFamilyIdQueryInterface */
    private $selectProductFamilyIdQuery;

    /** @var UnsubscribeProductHandler */
    private $unsubscribeProductHandler;

    /** @var UpdateSubscriptionFamilyHandler */
    private $updateSubscriptionFamilyHandler;

    /**
     * @param SelectProductFamilyIdQueryInterface $selectProductFamilyIdQuery
     * @param UnsubscribeProductHandler $unsubscribeProductHandler
     * @param UpdateSubscriptionFamilyHandler $updateSubscriptionFamilyHandler
     */
    public function __construct(
        SelectProductFamilyIdQueryInterface $selectProductFamilyIdQuery,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        UpdateSubscriptionFamilyHandler $updateSubscriptionFamilyHandler
    ) {
        $this->selectProductFamilyIdQuery = $selectProductFamilyIdQuery;
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
        $this->updateSubscriptionFamilyHandler = $updateSubscriptionFamilyHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'onPreSave',
        ];
    }

    /**
     * Pre-save event action.
     *
     * @param GenericEvent $event
     */
    public function onPreSave(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (null === $product->getId()) {
            return;
        }

        $originalFamilyId = $this->selectProductFamilyIdQuery->execute($product->getId());
        if (null === $originalFamilyId) {
            return;
        }

        if (null === $product->getFamily()) {
            $this->unsubscribeProduct($product->getId());

            return;
        }

        if ($product->getFamily()->getId() !== $originalFamilyId) {
            $this->updateSubscriptionFamily($product->getId());
        }
    }

    /**
     * Call product unsubscription.
     *
     * @param int $productId
     */
    private function unsubscribeProduct(int $productId): void
    {
        try {
            $command = new UnsubscribeProductCommand($productId);
            $this->unsubscribeProductHandler->handle($command);
        } catch (ProductNotSubscribedException $e) {
            // Silently catch exception if the product is not subscribed
            // We don't check it here as the handler already checks it. No need to do it twice
            return;
        }
    }

    /**
     * @param int $productId
     */
    private function updateSubscriptionFamily(int $productId): void
    {
        try {
            $this->updateSubscriptionFamilyHandler->handle(new UpdateSubscriptionFamilyCommand());
        } catch (ProductNotSubscribedException $e) {
            // Silently catch exception if the product is not subscribed
            // We don't check it here as the handler already checks it. No need to do it twice
            return;
        }
    }
}
