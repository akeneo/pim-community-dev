<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Processor\Denormalization\Product;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Find the product to import depending the data given by the reader
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindProductToImport
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var ProductBuilderInterface */
    private $variantProductBuilder;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param ProductBuilderInterface               $productBuilder
     * @param ProductBuilderInterface               $variantProductBuilder
     *
     * @internal param CreateVariantProduct $createVariantProduct
     * @internal param Query\TurnProduct $turnProductQuery
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        ProductBuilderInterface $variantProductBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
        $this->variantProductBuilder = $variantProductBuilder;
    }

    /**
     * Find the product to import
     *
     * @param string $productIdentifierCode
     * @param string $familyCode
     * @param string $parentProductModelCode
     *
     * @return ProductInterface
     */
    public function fromFlatData(
        string $productIdentifierCode,
        string $familyCode,
        string $parentProductModelCode
    ): ProductInterface {
        $product = $this->productRepository->findOneByIdentifier($productIdentifierCode);

        if (null === $product && '' !== $parentProductModelCode) {
            return $this->variantProductBuilder->createProduct($productIdentifierCode, $familyCode);
        }

        if (null === $product) {
            return $this->productBuilder->createProduct($productIdentifierCode, $familyCode);
        }

        return $product;
    }
}
