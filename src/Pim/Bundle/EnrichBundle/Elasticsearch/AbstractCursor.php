<?php

namespace Pim\Bundle\EnrichBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Common logic shared by all our product and product model cursors.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCursor implements CursorInterface
{
    /** @var Client */
    protected $esClient;

    /** @var CursorableRepositoryInterface */
    protected $productRepository;

    /** @var CursorableRepositoryInterface */
    protected $productModelRepository;

    /** @var array */
    protected $items;

    /** @var int */
    protected $count;

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return !empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return $this->count;
    }

    /**
     * Get the next items (hydrated from doctrine repository).
     *
     * @param array $esQuery
     *
     * @return array
     */
    protected function getNextItems(array $esQuery)
    {
        $identifiers = $this->getNextIdentifiers($esQuery);
        if (empty($identifiers)) {
            return [];
        }

        $productIdentifiers = [];
        $productModelIdentifiers = [];

        foreach ($identifiers as $identifier => $type) {
            if ($type === ProductInterface::class) {
                $productIdentifiers[] = $identifier;
            } else {
                $productModelIdentifiers[] = $identifier;
            }
        }

        $hydratedProducts = $this->productRepository->getItemsFromIdentifiers($productIdentifiers);
        $hydratedProductModels = $this->productModelRepository->getItemsFromIdentifiers($productModelIdentifiers);
        $hydratedItems = array_merge($hydratedProducts, $hydratedProductModels);

        $orderedItems = [];

        foreach ($identifiers as $identifier => $type) {
            // sometimes $identifier is only numerical whereas getIdentifer() returns a string
            $identifier = (string) $identifier;
            foreach ($hydratedItems as $hydratedItem) {
                if ($hydratedItem instanceof ProductInterface && $identifier === $hydratedItem->getIdentifier()) {
                    $orderedItems[] = $hydratedItem;
                    break;
                } elseif ($hydratedItem instanceof ProductModelInterface && $identifier === $hydratedItem->getCode()) {
                    $orderedItems[] = $hydratedItem;
                    break;
                }
            }
        }

        return $orderedItems;
    }

    /**
     * Returns an array containing the identifier as keys and the product type as values.
     * The idea is keep the sort of the identifier and to be able to know if it's a product or a product model.
     *
     * For instance
     *      [
     *          'tshirt-red-s'  => 'product',
     *          'tshirt-red'    => 'product_model',
     *          'watch'         => 'product',
     *      ]
     *
     * @return array
     */
    abstract protected function getNextIdentifiers(array $esQuery);
}
