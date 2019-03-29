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

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UnsubscribeProductHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductNotSubscribedException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductFamilyIdQueryInterface;
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

    /** @var array */
    private $productsToUnsubscribe = [];

    /** @var array */
    private $productsToUpdateSubscriptionFamily = [];

    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    public function __construct(
        SelectProductFamilyIdQueryInterface $selectProductFamilyIdQuery,
        UnsubscribeProductHandler $unsubscribeProductHandler,
        UpdateSubscriptionFamilyHandler $updateSubscriptionFamilyHandler,
        GetConnectionStatusHandler $connectionStatusHandler
    ) {
        $this->selectProductFamilyIdQuery = $selectProductFamilyIdQuery;
        $this->unsubscribeProductHandler = $unsubscribeProductHandler;
        $this->updateSubscriptionFamilyHandler = $updateSubscriptionFamilyHandler;
        $this->connectionStatusHandler = $connectionStatusHandler;
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

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $originalFamilyId = $this->selectProductFamilyIdQuery->execute($product->getId());
        if (null === $originalFamilyId) {
            return;
        }

        if (null === $product->getFamilyId()) {
            $this->productsToUnsubscribe[] = $product->getId();

            return;
        }

        if ($product->getFamilyId() !== $originalFamilyId) {
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
            // TODO: The family code should be retrieved from a FranklinInsights Product read model
            $familyCode = new FamilyCode($product->getFamily()->getCode());
            $this->updateSubscriptionFamily($product->getId(), $familyCode);
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
     * @param int        $productId
     * @param FamilyCode $familyCode
     */
    private function updateSubscriptionFamily(int $productId, FamilyCode $familyCode): void
    {
        try {
            $command = new UpdateSubscriptionFamilyCommand($productId, $familyCode);
            $this->updateSubscriptionFamilyHandler->handle($command);
        } catch (ProductNotSubscribedException $e) {
            // Silently catch exception if the product is not subscribed
            // We don't check it here as the handler already checks it. No need to do it twice
        }
    }

    /**
     * @return bool
     */
    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }
}
