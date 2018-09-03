<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

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
