<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product indexer, define custom logic and options for product indexing in the search engine.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIndexer implements IndexerInterface, BulkIndexerInterface
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
    public function index($product, array $options = [])
    {
        $this->validateProduct($product);

        $normalizedProduct = $this->normalizer->normalize($product, 'indexing');
        $this->indexer->index($this->indexType, $product->getIdentifier(), $normalizedProduct);
    }

    /**
     * {@inheritdoc}
     */
    public function indexAll(array $products, array $options = [])
    {
        $normalizedProducts = [];
        foreach ($products as $product) {
            $this->validateProduct($product);
            $normalizedProducts[$product->getIdentifier()] = $this->normalizer->normalize($product, 'indexing');
        }

        $this->indexer->bulkIndexes($this->indexType, $normalizedProducts, 'identifier');
    }

    /**
     * @param mixed $product
     */
    protected function validateProduct($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Only products "Pim\Component\Catalog\Model\ProductInterface" can be indexed in the search engine, "%s" provided.',
                    ClassUtils::getClass($product)
                )
            );
        }
    }
}
