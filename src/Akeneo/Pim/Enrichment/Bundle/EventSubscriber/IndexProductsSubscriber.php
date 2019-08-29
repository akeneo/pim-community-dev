<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Index products in the search engine.
 *
 * This is not done directly in the product saver as it's only a technical
 * problem. The product saver only handles business stuff.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductsSubscriber implements EventSubscriberInterface
{
    /** @var ProductIndexerInterface */
    private $productIndexer;

    /**
     * @param ProductIndexerInterface $productIndexer
     */
    public function __construct(ProductIndexerInterface $productIndexer)
    {
        $this->productIndexer = $productIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            StorageEvents::POST_SAVE     => ['indexProduct', 300],
            StorageEvents::POST_SAVE_ALL => ['bulkIndexProducts', 300],
            StorageEvents::PRE_REMOVE    => ['deleteProduct', 300],
        ];
    }

    /**
     * Index one single product.
     *
     * @param GenericEvent $event
     */
    public function indexProduct(GenericEvent $event) : void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->productIndexer->indexFromProductIdentifier($product->getIdentifier());
    }

    /**
     * Index several products at a time.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProducts(GenericEvent $event) : void
    {
        $products = $event->getSubject();
        if (!is_array($products)) {
            return;
        }

        $identifiers = [];
        foreach ($products as $product) {
            if ($product instanceof ProductInterface) {
                $identifiers[] = $product->getIdentifier();
            }
        }

        if (empty($identifiers)) {
            return;
        }

        $this->productIndexer->indexFromProductIdentifiers($identifiers);
    }

    /**
     * Delete one single product from ES index
     *
     * @param RemoveEvent $event
     */
    public function deleteProduct(RemoveEvent $event) : void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->productIndexer->removeFromProductId((string) $product->getId());
    }
}
