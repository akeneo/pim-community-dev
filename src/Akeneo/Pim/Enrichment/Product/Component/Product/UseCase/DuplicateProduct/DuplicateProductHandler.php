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
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Permission\Component\Authorization\FetchUserRightsOnProductInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DuplicateProductHandler
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var RemoveUniqueAttributeValues */
    private $removeUniqueAttributeValues;

    /** @var ProductBuilderInterface */
    private $productBuilder;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $productSaver;

    /** @var SecurityFacade */
    private $securityFacade;

    /** @var FetchUserRightsOnProductInterface */
    private $fetchUserRightsOnProduct;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        RemoveUniqueAttributeValues $removeUniqueAttributeValues,
        ProductBuilderInterface $productBuilder,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $validator,
        SaverInterface $productSaver,
        SecurityFacade $securityFacade,
        FetchUserRightsOnProductInterface $fetchUserRightsOnProduct
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->removeUniqueAttributeValues = $removeUniqueAttributeValues;
        $this->productBuilder = $productBuilder;
        $this->normalizer = $normalizer;
        $this->productUpdater = $productUpdater;
        $this->validator = $validator;
        $this->productSaver = $productSaver;
        $this->securityFacade = $securityFacade;
        $this->fetchUserRightsOnProduct = $fetchUserRightsOnProduct;
    }

    public function handle(DuplicateProduct $duplicateProductCommand): DuplicateProductResponse
    {
        if (!$this->isUserAllowedToDuplicateProduct($duplicateProductCommand->productToDuplicateIdentifier(), $duplicateProductCommand->userId())) {
            throw new ObjectNotFoundException(
                sprintf(
                    'Product "%s" is not editable by user id "%s".',
                    $duplicateProductCommand->productToDuplicateIdentifier(),
                    $duplicateProductCommand->userId()
                )
            );
        }

        /** @var ProductInterface */
        $productToDuplicate = $this->productRepository->findOneByIdentifier($duplicateProductCommand->productToDuplicateIdentifier());

        $normalizedProduct = $this->normalizeProductWithNewIdentifier($productToDuplicate, $duplicateProductCommand->duplicatedProductIdentifier());

        $duplicatedProduct = $this->productBuilder->createProduct(
            $duplicateProductCommand->duplicatedProductIdentifier(),
            $productToDuplicate->getFamily() !== null ? $productToDuplicate->getFamily()->getCode() : null
        );

        $this->productUpdater->update($duplicatedProduct, $normalizedProduct);

        $duplicatedProduct = $this->removeUniqueAttributeValues->fromProduct($duplicatedProduct);

        $violations = $this->validator->validate($duplicatedProduct);

        if (0 === $violations->count()) {
            $this->productSaver->save($duplicatedProduct, ['add_default_values' => false]);
            $removedUniqueAttributeCodesWithoutIdentifier = $this->getRemovedUniqueAttributeCodesWithoutIdentifier(
                $productToDuplicate,
                $duplicatedProduct
            );

            return DuplicateProductResponse::ok($duplicatedProduct, $removedUniqueAttributeCodesWithoutIdentifier);
        }

        return DuplicateProductResponse::error($violations);
    }

    private function normalizeProductWithNewIdentifier(ProductInterface $productToDuplicate, string $newIdentifier): array
    {
        $normalizedProduct = $this->normalizer->normalize(
            $productToDuplicate,
            'standard'
        );
        $normalizedProduct['values'][$this->attributeRepository->getIdentifierCode()] = [
            ['data' => $newIdentifier, 'locale' => null, 'scope' => null],
        ];

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

    private function isUserAllowedToDuplicateProduct(string $productToDuplicateIdentifier, int $userId): bool
    {
        $userRightsOnProduct = $this->fetchUserRightsOnProduct->fetchByIdentifier(
            $productToDuplicateIdentifier,
            $userId
        );

        return $this->securityFacade->isGranted('pimee_enrichment_product_duplicate')
            && $this->securityFacade->isGranted('pim_enrich_product_create')
            && $userRightsOnProduct->isProductEditable();
    }
}
