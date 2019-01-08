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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductFamilyIdQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
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

    /** @var array */
    private $productsToUnsubscribe = [];

    /** @var array */
    private $productsToUpdateSubscriptionFamily = [];

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
            StorageEvents::POST_SAVE => 'onPostSave',
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
            $this->productsToUnsubscribe[] = $product->getId();

            return;
        }

        if ($product->getFamily()->getId() !== $originalFamilyId) {
            $this->productsToUpdateSubscriptionFamily[] = $product->getId();
        }
    }

    /**
     * @param GenericEvent $event
     */
    public function onPostSave(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }
        if (in_array($product->getId(), $this->productsToUnsubscribe, true)) {
            $this->unsubscribeProduct($product->getId());

            return;
        }
        if (in_array($product->getId(), $this->productsToUpdateSubscriptionFamily, true)) {
            $this->updateSubscriptionFamily($product->getId(), $product->getFamily());
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
     * @param FamilyInterface $family
     */
    private function updateSubscriptionFamily(int $productId, FamilyInterface $family): void
    {
        $command = new UpdateSubscriptionFamilyCommand($productId, $family);
        $this->updateSubscriptionFamilyHandler->handle($command);
    }
}
