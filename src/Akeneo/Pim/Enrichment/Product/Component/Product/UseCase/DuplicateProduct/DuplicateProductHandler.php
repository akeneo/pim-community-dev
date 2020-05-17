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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DuplicateProductHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var SaverInterface */
    private $productSaver;

    /** @var RemoveUniqueAttributeValues */
    private $removeUniqueAttributeValues;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        ProductBuilderInterface $productBuilder,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        RemoveUniqueAttributeValues $removeUniqueAttributeValues
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productBuilder = $productBuilder;
        $this->normalizer = $normalizer;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->removeUniqueAttributeValues = $removeUniqueAttributeValues;
    }

    public function handle(DuplicateProduct $query): DuplicateProductResponse
    {
        /** @var ProductInterface */
        $productToDuplicate = $this->productRepository->findOneByIdentifier($query->productToDuplicateIdentifier());

        $normalizedProductWithoutIdentifier = $this->normalizeProductWithoutIdentifier($productToDuplicate);

        $duplicatedProduct = $this->productBuilder->createProduct(
            $query->duplicatedProductIdentifier(),
            $productToDuplicate->getFamily() !== null ? $productToDuplicate->getFamily()->getCode() : null
        );

        $this->productUpdater->update($duplicatedProduct, $normalizedProductWithoutIdentifier);

        $duplicatedProduct = $this->removeUniqueAttributeValues->fromProduct($duplicatedProduct);

        $removedUniqueAttributeCodesWithoutIdentifier = $this->getRemovedUniqueAttributeCodesWithoutIdentifier($productToDuplicate, $duplicatedProduct);

        $this->productSaver->save($duplicatedProduct);

        return new DuplicateProductResponse($removedUniqueAttributeCodesWithoutIdentifier);
    }

    private function normalizeProductWithoutIdentifier(ProductInterface $productToDuplicate): array
    {
        $normalizedProduct = $this->normalizer->normalize(
            $productToDuplicate,
            'standard'
        );

        unset($normalizedProduct['values'][$this->attributeRepository->getIdentifierCode()]);

        return $normalizedProduct;
    }

    private function getRemovedUniqueAttributeCodesWithoutIdentifier(ProductInterface $productToDuplicate, ProductInterface $duplicatedProduct): array
    {
        $removedUniqueAttributeCodes = array_diff(
            $productToDuplicate->getValues()->getAttributeCodes(),
            $duplicatedProduct->getValues()->getAttributeCodes()
        );

        $removedUniqueAttributeCodesWithoutIdentifier = array_values(array_diff(
            $removedUniqueAttributeCodes,
            [$this->attributeRepository->getIdentifierCode()]
        ));

        return $removedUniqueAttributeCodesWithoutIdentifier;
    }
}
