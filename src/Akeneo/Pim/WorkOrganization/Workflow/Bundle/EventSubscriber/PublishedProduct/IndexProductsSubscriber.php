<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\PublishedProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\PublishedProductIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Index products and published products in the search engine.
 *
 * This is not done directly in the product saver as it's only a technical
 * problem. The product saver only handles business stuff.
 *
 * This subscriber is also responsible for the indexing of published products. As the PublishedProduct class inherit
 * from AbstractProduct, we need to route the saved entity to the correct indexer depending on its type.
 *
 * Note: We override the CE service `pim_catalog.event_subscriber.index_published_products` in order to avoid
 * potential side effects as we define a clear and explicit routing logic in this class.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductsSubscriber implements EventSubscriberInterface
{
    /** @var ProductIndexerInterface */
    protected $productIndexer;

    /** @var PublishedProductIndexer */
    protected $publishedProductIndexer;

    /**
     * @param ProductIndexerInterface $productIndexer
     * @param PublishedProductIndexer $publishedProductIndexer
     */
    public function __construct(
        ProductIndexerInterface $productIndexer,
        PublishedProductIndexer $publishedProductIndexer
    ) {
        $this->productIndexer = $productIndexer;
        $this->publishedProductIndexer = $publishedProductIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => ['indexProduct', 300],
            StorageEvents::POST_SAVE_ALL => ['bulkIndexProducts', 300],
            StorageEvents::POST_REMOVE => ['deleteProduct', 300],
        ];
    }

    /**
     * Index one single product or published product.
     *
     * @param GenericEvent $event
     */
    public function indexProduct(GenericEvent $event)
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if ($product instanceof PublishedProductInterface) {
            $this->publishedProductIndexer->index($product);
        } else {
            $this->productIndexer->indexFromProductIdentifier($product->getIdentifier());
        }
    }

    /**
     * Index several products or published products at a time.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProducts(GenericEvent $event)
    {
        $products = $event->getSubject();
        if (!is_array($products)) {
            return;
        }

        if (!current($products) instanceof ProductInterface) {
            return;
        }

        if (current($products) instanceof PublishedProductInterface) {
            $this->publishedProductIndexer->indexAll($products);
        } else {
            $this->productIndexer->indexFromProductIdentifiers(
                array_map(
                    function (ProductInterface $product) {
                        return $product->getIdentifier();
                    },
                    $products
                )
            );
        }
    }

    /**
     * Delete one single product or published product from the right ES index
     *
     * @param RemoveEvent $event
     */
    public function deleteProduct(RemoveEvent $event)
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if ($product instanceof PublishedProductInterface) {
            $this->publishedProductIndexer->remove($event->getSubjectId());
        } else {
            $this->productIndexer->removeFromProductId((string) $event->getSubjectId());
        }
    }
}
