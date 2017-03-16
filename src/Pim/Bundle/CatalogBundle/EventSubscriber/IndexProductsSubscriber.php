<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Index products in the search engine.
 *
 * This is not done directly in the product saver as it's only a technical
 * problem. The product saver only handles business stuff.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductsSubscriber implements EventSubscriberInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var Client */
    protected $indexer;

    /** @var string */
    protected $indexType;

    /**
     * @param NormalizerInterface $normalizer
     * @param Client              $indexer
     * @param string              $indexType
     */
    public function __construct(NormalizerInterface $normalizer, Client $indexer, $indexType)
    {
        $this->normalizer = $normalizer;
        $this->indexer = $indexer;
        $this->indexType = $indexType;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'indexProduct',
            StorageEvents::POST_SAVE_ALL => 'bulkIndexProducts',
        ];
    }

    /**
     * Index one single product.
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

        $normalizedProduct = $this->normalizer->normalize($product, 'indexing');
        $this->indexer->index($this->indexType, $product->getIdentifier(), $normalizedProduct);
    }

    /**
     * Index several products at a time.
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

        $normalizedProducts = [];
        foreach ($products as $product) {
            if (!$product instanceof ProductInterface) {
                throw new \InvalidArgumentException(
                    'Only products "Pim\Component\Catalog\Model\ProductInterface" can be indexed in the search engine.'
                );
            }
            $normalizedProducts[$product->getIdentifier()] = $this->normalizer->normalize($product, 'indexing');
        }

        // TODO TIP-709: bulk index instead => will be done in another PR
        foreach ($normalizedProducts as $identifier => $indexedFormat) {
            $this->indexer->index($this->indexType, $identifier, $indexedFormat);
        }
    }
}
