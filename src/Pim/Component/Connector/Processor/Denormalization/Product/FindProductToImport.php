<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Processor\Denormalization\Product;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
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

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param ProductBuilderInterface               $productBuilder
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
    }

    /**
     * Find the product to import
     *
     * @param string $productIdentifierCode
     * @param string $familyCode
     *
     * @return ProductInterface
     */
    public function fromFlatData(
        string $productIdentifierCode,
        string $familyCode
    ): ProductInterface {
        $product = $this->productRepository->findOneByIdentifier($productIdentifierCode);

        if (null === $product) {
            return $this->productBuilder->createProduct($productIdentifierCode, $familyCode);
        }

        return $product;
    }
}
