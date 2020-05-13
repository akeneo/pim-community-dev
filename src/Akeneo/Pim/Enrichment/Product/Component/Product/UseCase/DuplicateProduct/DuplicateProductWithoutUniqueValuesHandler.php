<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DuplicateProductWithoutUniqueValuesHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var SaverInterface */
    private $productSaver;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver
    ) {
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
        $this->normalizer = $normalizer;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
    }

    public function handle(DuplicateProductWithoutUniqueValues $query): DuplicateProductResponse
    {
        /** @var Product */
        $product = $this->productRepository->find($query->productId());

        $duplicatedProduct = $this->productBuilder->createProduct(
            $query->identifier(),
            $product->getFamilyId()
        );

        $normalizedProduct = $this->normalizer->normalize(
            $product,
            'standard'
        );
        // @TODO: To remove this line when we will remove the unique values from the duplicated product (https://akeneo.atlassian.net/browse/CHRIS-8)
        unset($normalizedProduct['values']['sku']);

        $this->productUpdater->update($duplicatedProduct, $normalizedProduct);

        $this->productSaver->save($duplicatedProduct);

        return new DuplicateProductResponse([]);
    }
}
