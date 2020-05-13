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
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetUniqueAttributeCodes;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DuplicateProductHandler
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

    /** @var GetUniqueAttributeCodes */
    private $getUniqueAttributeCodes;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductBuilderInterface $productBuilder,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        GetUniqueAttributeCodes $getUniqueAttributeCodes
    ) {
        $this->productRepository = $productRepository;
        $this->productBuilder = $productBuilder;
        $this->normalizer = $normalizer;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->getUniqueAttributeCodes = $getUniqueAttributeCodes;
    }

    public function handle(DuplicateProduct $query): DuplicateProductResponse
    {
        /** @var ProductInterface */
        $productToDuplicate = $this->productRepository->findOneByIdentifier($query->productToDuplicateIdentifier());
        $familyCode = $productToDuplicate->getFamily()->getCode();

        $uniqueAttributeCodes = $this->getUniqueAttributeCodes->fromFamilyCode($familyCode);
        RemoveUniqueAttributeValues::fromCollection(
            $productToDuplicate->getValues(),
            $uniqueAttributeCodes
        );

        $normalizedProduct = $this->normalizer->normalize(
            $productToDuplicate,
            'standard'
        );

        $duplicatedProduct = $this->productBuilder->createProduct(
            $query->duplicatedProductIdentifier(),
            $familyCode
        );

        $this->productUpdater->update($duplicatedProduct, $normalizedProduct);

        $this->productSaver->save($duplicatedProduct);

        return new DuplicateProductResponse([$uniqueAttributeCodes]);
    }
}
