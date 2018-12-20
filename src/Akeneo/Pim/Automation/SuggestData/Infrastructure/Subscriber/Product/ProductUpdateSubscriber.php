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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Subscriber\Product;

use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
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

    /**
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     * @param SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery
     */
    public function __construct(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->selectProductIdentifierValuesQuery = $selectProductIdentifierValuesQuery;
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
     *
     * @return array
     */
    public function computeImpactedSubscriptions(GenericEvent $event): array
    {
        $impactedProductIds = [];
        foreach ($event->getSubject() as $product) {
            if (!$product instanceof ProductInterface) {
                continue;
            }
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

        // TODO APAI-501: to remove (only useful for the spec)
        return $impactedProductIds;
    }

    /**
     * Compare requested identifier values with new identifier values.
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
        // TODO APAI-501: implement
    }
}
