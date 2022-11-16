<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductWasCompletedOnChannelLocaleCollection;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetUserId;
use Akeneo\Pim\Enrichment\Product\Domain\Clock;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeAndPersistProductCompletenesses
{
    private const CHUNK_SIZE = 1000;

    public function __construct(
        private CompletenessCalculator $completenessCalculator,
        private SaveProductCompletenesses $saveProductCompletenesses,
        private GetProductCompletenesses $getProductCompletenesses,
        private EventDispatcherInterface $eventDispatcher,
        private Clock $clock,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @param UuidInterface[] $productUuids
     */
    public function fromProductUuids(array $productUuids): void
    {
        foreach (array_chunk($productUuids, self::CHUNK_SIZE) as $uuidsChunk) {
            $previousCompletenessCollections = $this->getProductCompletenesses->fromProductUuids($uuidsChunk);
            $completenessCollections = $this->completenessCalculator->fromProductUuids($uuidsChunk);
            $this->saveProductCompletenesses->saveAll($completenessCollections);

            $productWasCompletedEventsCollection = $this->buildProductWasCompletedEventsCollection(
                $completenessCollections,
                $previousCompletenessCollections
            );
            if (null !== $productWasCompletedEventsCollection) {
                $this->eventDispatcher->dispatch($productWasCompletedEventsCollection);
            }
        }
    }

    /**
     * @param array<string, ProductCompletenessWithMissingAttributeCodesCollection> $newProductsCompletenessCollections
     * @param array<string, ProductCompletenessCollection> $previousProductsCompletenessCollections
     */
    private function buildProductWasCompletedEventsCollection(
        array $newProductsCompletenessCollections,
        array $previousProductsCompletenessCollections
    ): ?ProductWasCompletedOnChannelLocaleCollection {
        $now = $this->clock->now();
        /** @var \Akeneo\UserManagement\Component\Model\UserInterface $user */
        $user = $this->tokenStorage->getToken()?->getUser();
        $userId = (string) $user?->getId();

        $productCompletedOnChannelLocaleEvents = [];
        foreach ($newProductsCompletenessCollections as $uuid => $newProductCompletenessCollection) {
            $previousProductCompletenessCollection = $previousProductsCompletenessCollections[$uuid] ?? null;

            $productCompletedOnChannelLocaleEvents = [
                ...$productCompletedOnChannelLocaleEvents,
                ...$newProductCompletenessCollection->buildProductWasCompletedOnChannelLocaleEvents(
                    $userId,
                    $now,
                    $previousProductCompletenessCollection
                )
            ];
        }

        return [] !== $productCompletedOnChannelLocaleEvents
            ? new ProductWasCompletedOnChannelLocaleCollection($productCompletedOnChannelLocaleEvents)
            : null;
    }
}
