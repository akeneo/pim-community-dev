<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Component\Product\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;

/**
 * @author    Christophe Chausseray
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DuplicateProductWithoutUniqueValuesHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
    }

    public function handle(DuplicateProductWithoutUniqueValues $query): array
    {
        /** @var Product */
        $product = $this->productRepository->find($query->productId());

        $duplicatedProduct = $this->productBuilder->createProduct(
            $query->identifier(),
            $product->getFamilyId()
        );

        return [$duplicatedProduct];
    }
}
