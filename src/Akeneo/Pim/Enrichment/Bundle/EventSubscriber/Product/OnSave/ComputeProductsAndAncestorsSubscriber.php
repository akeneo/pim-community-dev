<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Model\Event\SavedProductIdentifier;
use Akeneo\Pim\Enrichment\Component\Product\Model\Event\SavedProductIdentifierCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This subscriber is responsible for
 * - computing and persisting product completeness
 * - reindexing products
 * - reindexing ancestor product models as the completeness could change and impact the product model field 'all_complete' or 'all_incomplete' in the ES projection
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsAndAncestorsSubscriber implements EventSubscriberInterface
{
    /** @var ComputeAndPersistProductCompletenesses */
    private $computeAndPersistProductCompletenesses;

    /** @var ProductAndAncestorsIndexer */
    private $productAndAncestorsIndexer;

    public function __construct(
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductAndAncestorsIndexer $productAndAncestorsIndexer
    ) {
        $this->computeAndPersistProductCompletenesses = $computeAndPersistProductCompletenesses;
        $this->productAndAncestorsIndexer = $productAndAncestorsIndexer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'handleSingleProduct',
            StorageEvents::POST_SAVE_ALL => 'handleMultipleProducts',
        ];
    }

    public function handleSingleProduct(GenericEvent $event): void
    {
        if (false === ($event->getArguments()['unitary'] ?? false)) {
            return;
        }
        $this->handleMultipleProducts(new GenericEvent([$event->getSubject()], $event->getArguments()));
    }

    public function handleMultipleProducts(GenericEvent $event): void
    {
        $productIdentifiers = $event->getSubject();
        if (false === $productIdentifiers instanceof SavedProductIdentifierCollection) {
            return;
        }

        $this->computeAndPersistProductCompletenesses->fromProductIdentifiers($productIdentifiers->getIdentifiers());
        $this->productAndAncestorsIndexer->indexFromProductIdentifiers($productIdentifiers->getIdentifiers());
    }
}
