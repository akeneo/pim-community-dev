<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;

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
    /** @var ProductIndexerInterface */
    private $productIndexer;

    /** @var ProductIndexerInterface */
    private $productModelIndexer;

    /**
     * @param ProductIndexerInterface $productIndexer
     * @param ProductIndexerInterface $productModelIndexer
     */
    public function __construct(ProductIndexerInterface $productIndexer, ProductIndexerInterface $productModelIndexer)
    {
        $this->productIndexer = $productIndexer;
        $this->productModelIndexer = $productModelIndexer;
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

        $this->indexProductModelChildren($object->getProductModels(), $options);
        $this->indexProductModelChildren($object->getProducts(), $options);
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
            $this->index($object, $options);
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
     * Recursive method that indexes a list of product model children and their children
     * (products or product models).
     *
     * @param Collection $productModelChildren
     * @param array      $options
     */
    private function indexProductModelChildren(Collection $productModelChildren, array $options = []) : void
    {
        if ($productModelChildren->isEmpty()) {
            return;
        }

        if ($productModelChildren->first() instanceof ProductInterface) {
            $identifiers = [];
            foreach ($productModelChildren as $productModelChild) {
                $identifiers[] = $productModelChild->getIdentifier();
            }

            $this->productIndexer->indexFromProductIdentifiers($identifiers, $options);

            return;
        }

        $this->productModelIndexer->indexFromProductIdentifiers(
            $this->getProductModelCodes($productModelChildren),
            $options
        );

        foreach ($productModelChildren as $productModelChild) {
            $this->indexProductModelChildren($productModelChild->getProductModels(), $options);
            $this->indexProductModelChildren($productModelChild->getProducts(), $options);
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
            $productIds = [];
            foreach ($productModelChildren as $productModelChild) {
                $productIds[] = (string) $productModelChild->getId();
            }

            $this->productIndexer->removeFromProductIds($productIds);

            return;
        }

        $this->productModelIndexer->removeManyFromProductIds($this->getProductModelCodes($productModelChildren));

        foreach ($productModelChildren as $productModelChild) {
            $this->removeProductModelChildren($productModelChild->getProductModels());
            $this->removeProductModelChildren($productModelChild->getProducts());
        }
    }

    /**
     * @param Collection $productModels
     * @return array
     */
    private function getProductModelCodes(Collection $productModels): array
    {
        $codes = [];
        /** @var ProductModelInterface $productModel */
        foreach ($productModels as $productModel) {
            $codes[] = $productModel->getCode();
        }

        return $codes;
    }
}
