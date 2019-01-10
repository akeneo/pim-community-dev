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
use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service\ResubscribeProductsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductUpdateSubscriber implements EventSubscriberInterface
{
    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /** @var SelectProductIdentifierValuesQueryInterface */
    private $selectProductIdentifierValuesQuery;

    /** @var ResubscribeProductsInterface */
    private $resubscribeProducts;

    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /**
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     * @param SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery
     * @param ResubscribeProductsInterface $resubscribeProducts
     * @param GetConnectionStatusHandler $connectionStatusHandler
     */
    public function __construct(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery,
        ResubscribeProductsInterface $resubscribeProducts,
        GetConnectionStatusHandler $connectionStatusHandler
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->selectProductIdentifierValuesQuery = $selectProductIdentifierValuesQuery;
        $this->resubscribeProducts = $resubscribeProducts;
        $this->connectionStatusHandler = $connectionStatusHandler;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE_ALL => 'computeImpactedSubscriptions',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function computeImpactedSubscriptions(GenericEvent $event): void
    {
        $impactedProductIds = [];
        foreach ($event->getSubject() as $product) {
            if (!$product instanceof ProductInterface) {
                continue;
            }

            if (!$this->isFranklinInsightsActivated()) {
                return;
            }

            // TODO: find many subscriptions by product ids in order to avoid too may queries
            $subscription = $this->subscriptionRepository->findOneByProductId($product->getId());
            if (null === $subscription) {
                continue;
            }
            if (true === $this->wereIdentifierValuesUpdated($subscription)) {
                $impactedProductIds[] = $product->getId();
            }
        }

        if (!empty($impactedProductIds)) {
            $this->launchResubscriptionJob($impactedProductIds);
        }
    }

    /**
     * Compare requested identifier values with new identifier values. Here we only want to compare the non-empty
     * values of the original subscription (we don't want to re-subscribe a product if an identifier value was added).
     *
     * @param ProductSubscription $subscription
     *
     * @return bool
     */
    private function wereIdentifierValuesUpdated(ProductSubscription $subscription): bool
    {
        // TODO: move this logic into a service, this may be useful in other contexts as well
        // (e.g: update identifiers mapping)

        $requestedIdentifierValues = $subscription->requestedIdentifierValues();
        $newIdentifierValues = $this->selectProductIdentifierValuesQuery
            ->execute($subscription->getProductId())
            ->identifierValues();

        foreach ($requestedIdentifierValues as $franklinIdentifierCode => $requestedIdentifierValue) {
            if (($newIdentifierValues[$franklinIdentifierCode] ?? null) !== $requestedIdentifierValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Launches the re-subscription job for the given productIds.
     *
     * @param array $productIds
     */
    private function launchResubscriptionJob(array $productIds): void
    {
        $this->resubscribeProducts->process($productIds);
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
