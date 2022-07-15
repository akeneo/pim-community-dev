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
    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param ProductBuilderInterface               $productBuilder
     */
    public function __construct(
        private IdentifiableObjectRepositoryInterface $productRepository,
        private ProductBuilderInterface $productBuilder
    ) {
    }

    /**
     * Find the product to import
     */
    public function fromFlatData(
        string $productIdentifierCode,
        string $familyCode,
        ?string $uuid,
    ): ProductInterface {
        $product = null;
        if (null !== $uuid) {
            $product = $this->productRepository->find($uuid);
        }

        if (null === $product) {
            $product = $this->productRepository->findOneByIdentifier($productIdentifierCode);
        }

        if (null === $product) {
            return $this->productBuilder->createProduct($productIdentifierCode, $familyCode);
        }

        return $product;
    }
}
