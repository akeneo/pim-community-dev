<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\PublishedProductIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SavePublishedProductCompletenesses;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * - computes and persists completeness of published products
 * - index published products in the search engine
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
final class ComputePublishedProductsSubscriber implements EventSubscriberInterface
{
    /** @var SavePublishedProductCompletenesses */
    private $savePublishedProductCompletenesses;

    /** @var CompletenessCalculator */
    private $completenessCalculator;

    /** @var PublishedProductIndexer */
    private $publishedProductIndexer;

    public function __construct(
        SavePublishedProductCompletenesses $savePublishedProductCompletenesses,
        CompletenessCalculator $completenessCalculator,
        PublishedProductIndexer $publishedProductIndexer
    ) {
        $this->savePublishedProductCompletenesses = $savePublishedProductCompletenesses;
        $this->completenessCalculator = $completenessCalculator;
        $this->publishedProductIndexer = $publishedProductIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'computePublishedProduct',
            StorageEvents::POST_SAVE_ALL => 'computeMultiplePublishedProducts',
        ];
    }

    public function computePublishedProduct(GenericEvent $event): void
    {
        if (!$event->hasArgument('unitary') || true !== $event->getArgument('unitary')) {
            return;
        }

        $this->computeMultiplePublishedProducts(new GenericEvent([$event->getSubject()]));
    }

    public function computeMultiplePublishedProducts(GenericEvent $event)
    {
        $publishedProducts = $event->getSubject();
        if (!is_array($publishedProducts)) {
            return;
        }
        $publishedProducts = array_filter($publishedProducts, function ($publishedProduct) {
            return $publishedProduct instanceof PublishedProductInterface;
        });
        if (!empty($publishedProducts)) {
            $this->computeAndPersistCompletenesses($publishedProducts);
            $this->publishedProductIndexer->indexAll($publishedProducts, ['index_refresh' => Refresh::disable()]);
        }
    }

    /**
     * Calculates the completenesses of original products, transforms them into PublishedProductCompleteness
     * and persists them
     *
     * @param PublishedProductInterface[] $publishedProducts
     */
    private function computeAndPersistCompletenesses(array $publishedProducts): void
    {
        $mappedUuids = [];
        foreach ($publishedProducts as $publishedProduct) {
            $mappedUuids[$publishedProduct->getOriginalProduct()->getUuid()->toString()] = $publishedProduct->getId();
        }

        $originalProductCompletenessCollections = $this->completenessCalculator->fromProductUuids(
            array_map(fn (string $uuid): UuidInterface => Uuid::fromString($uuid), array_keys($mappedUuids))
        );

        foreach ($mappedUuids as $uuid => $publishedProductId) {
            $originalProductCompletenesses = [];
            if (isset($originalProductCompletenessCollections[$uuid])) {
                $originalProductCompletenesses = iterator_to_array($originalProductCompletenessCollections[$uuid]);
            }

            $publishedProductCompletenesses = array_map(
                function (ProductCompletenessWithMissingAttributeCodes $productCompleteness): PublishedProductCompleteness {
                    return new PublishedProductCompleteness(
                        $productCompleteness->channelCode(),
                        $productCompleteness->localeCode(),
                        $productCompleteness->requiredCount(),
                        $productCompleteness->missingAttributeCodes()
                    );
                },
                $originalProductCompletenesses
            );

            $publishedProductCompletenessCollection = new PublishedProductCompletenessCollection(
                $publishedProductId,
                $publishedProductCompletenesses
            );
            $this->savePublishedProductCompletenesses->save($publishedProductCompletenessCollection);
        }
    }
}
