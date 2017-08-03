<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Indexer;

use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Indexer responsible for the indexing of all product model children (the subtree made of variant products and product
 * models).
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelDescendantsIndexer implements
    IndexerInterface,
    BulkIndexerInterface,
    RemoverInterface,
    BulkRemoverInterface
{
    /** @var BulkIndexerInterface */
    protected $productIndexer;

    /** @var BulkRemoverInterface */
    protected $productRemover;

    /** @var BulkIndexerInterface */
    protected $productModelIndexer;

    /** @var BulkRemoverInterface */
    protected $productModelRemover;

    /**
     * @param BulkIndexerInterface $productIndexer
     * @param BulkRemoverInterface $productRemover
     * @param BulkIndexerInterface $productModelIndexer
     * @param BulkRemoverInterface $productModelRemover
     */
    public function __construct(
        BulkIndexerInterface $productIndexer,
        BulkRemoverInterface $productRemover,
        BulkIndexerInterface $productModelIndexer,
        BulkRemoverInterface $productModelRemover
    ) {
        $this->productIndexer = $productIndexer;
        $this->productRemover = $productRemover;
        $this->productModelIndexer = $productModelIndexer;
        $this->productModelRemover = $productModelRemover;
    }

    /**
     * Recursively indexes the given product model children (a subtree made of products and product models).
     *
     * Indexes all product variants in the dedicated index.
     *
     * {@inheritdoc}
     */
    public function index($object, array $options = []) : void
    {
        if (!$object instanceof ProductModelInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Only product models "%s" can be indexed in the search engine, "%s" provided.',
                    ProductModelInterface::class,
                    ClassUtils::getClass($object)
                )
            );
        }

        $this->indexProductModelChildren($object->getProductModels());
        $this->indexProductModelChildren($object->getProducts());
    }

    /**
     * Recursively triggers the indexing of all the given product models children (subtree made of product variants and
     * product models).
     *
     * {@inheritdoc}
     */
    public function indexAll(array $objects, array $options = []) : void
    {
        if (empty($objects)) {
            return;
        }

        foreach ($objects as $object) {
            $this->index($object);
        }
    }

    /**
     * Recursively triggers the removing of all the given product model children from the indexes.
     *
     * Removes all product variants from the dedicated index.
     *
     * {@inheritdoc}
     */
    public function remove($object, array $options = []) : void
    {
        if (!$object instanceof ProductModelInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Only product models "%s" can be indexed in the search engine, "%s" provided.',
                    ProductModelInterface::class,
                    ClassUtils::getClass($object)
                )
            );
        }

        $this->removeProductModelChildren($object->getProductModels());
        $this->removeProductModelChildren($object->getProducts());
    }

    /**
     * Recursively triggers the removing of all the given product models children (subtree made of product variants and
     * product models).
     *
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = []) : void
    {
        if (empty($objects)) {
            return;
        }

        foreach ($objects as $object) {
            $this->remove($object);
        }
    }

    /**
     * Recursive method that indexes the a list of product model children and their children
     * (products or product models).
     *
     * @param Collection $productModelChildren
     */
    private function indexProductModelChildren(Collection $productModelChildren) : void
    {
        if ($productModelChildren->isEmpty()) {
            return;
        }

        if ($productModelChildren->first() instanceof ProductInterface) {
            $this->productIndexer->indexAll($productModelChildren->toArray()) ;
            return;
        }

        $this->productModelIndexer->indexAll($productModelChildren->toArray());

        foreach ($productModelChildren as $productModelChild) {
            $this->indexProductModelChildren($productModelChild->getProductModels());
            $this->indexProductModelChildren($productModelChild->getProducts());
        }
    }

    /**
     * Removes from the indexes the given list of product model children (product variants or product models).
     *
     * @param Collection $productModelChildren
     */
    private function removeProductModelChildren(Collection $productModelChildren) : void
    {
        if ($productModelChildren->isEmpty()) {
            return;
        }

        if ($productModelChildren->first() instanceof ProductInterface) {
            $this->productRemover->removeAll($productModelChildren->toArray()) ;
            return;
        }

        $this->productModelRemover->removeAll($productModelChildren->toArray());

        foreach ($productModelChildren as $productModelChild) {
            $this->removeProductModelChildren($productModelChild->getProductModels());
            $this->removeProductModelChildren($productModelChild->getProducts());
        }
    }
}
