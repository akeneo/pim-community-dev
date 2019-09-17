<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductModelIndexerInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Indexer responsible for the indexing of all product model children (the subtree made of variant products and product
 * models).
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelDescendantsIndexer {
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

    /**
     * Recursively indexes the given product model children (a subtree made of products and product models).
     * Indexes all product variants in the dedicated index.
     *
     * @argument string $productModelCode
     * @argument array  $options
     */
    public function fromProductModelCode(string $productModelCode, array $options = []): void
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($productModelCode);

        if (null === $productModel) {
            throw new InvalidArgumentException(
                ProductModelDescendantsIndexer::class,
                sprintf('ProductModel with code "%s" not found', $productModelCode)
            );
        }

        $this->indexProductModelChildren($productModel->getProductModels(), $options);
        $this->indexProductModelChildren($productModel->getProducts(), $options);
    }

    /**
     * Recursively triggers the indexing of all the given product models children (subtree made of product variants and
     * product models).
     *
     * @argument string[] $productModelCodes
     * @argument array    $options
     */
    public function fromProductModelCodes(array $productModelCodes, array $options = []): void
    {
        foreach ($productModelCodes as $productModelCode) {
            $this->fromProductModelCode($productModelCode, $options);
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
