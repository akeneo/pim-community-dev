<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductModelIndexerInterface;
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
    BulkIndexerInterface
{
    /** @var ProductIndexerInterface */
    private $productIndexer;

    /** @var ProductModelIndexerInterface */
    private $productModelIndexer;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    public function __construct(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $this->productIndexer = $productIndexer;
        $this->productModelIndexer = $productModelIndexer;
        $this->productModelRepository = $productModelRepository;
    }

    public function fromProductModelCode(string $productModelCode, array $options = []): void
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($productModelCode);

        $this->index($productModel, $options);
    }

    public function fromProductModelCodes(array $productModelCodes, array $options = []): void
    {
        if (empty($productModelCodes)) {
            return;
        }

        foreach ($productModelCodes as $productModelCode) {
            $this->fromProductModelCode($productModelCode, $options);
        }
    }

    /**
     * Recursively indexes the given product model children (a subtree made of products and product models).
     *
     * Indexes all product variants in the dedicated index.
     *
     * {@inheritdoc}
     * @deprecated
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
     * @deprecated
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

        $this->productModelIndexer->indexFromProductModelCodes(
            array_map(function (ProductModelInterface $productModel): string {
                return $productModel->getCode();
            }, $productModelChildren->toArray()),
            $options
        );

        foreach ($productModelChildren as $productModelChild) {
            $this->indexProductModelChildren($productModelChild->getProductModels(), $options);
            $this->indexProductModelChildren($productModelChild->getProducts(), $options);
        }
    }
}
