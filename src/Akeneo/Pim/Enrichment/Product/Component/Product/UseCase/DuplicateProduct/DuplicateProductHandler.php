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
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Component\Authorization\FetchUserRightsOnProductInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DuplicateProductHandler
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private AttributeRepositoryInterface $attributeRepository,
        private RemoveUniqueAttributeValues $removeUniqueAttributeValues,
        private ProductBuilderInterface $productBuilder,
        private NormalizerInterface $normalizer,
        private ObjectUpdaterInterface $productUpdater,
        private ValidatorInterface $validator,
        private SaverInterface $productSaver,
        private SecurityFacade $securityFacade,
        private FetchUserRightsOnProductInterface $fetchUserRightsOnProduct
    ) {
    }

    public function handle(DuplicateProduct $duplicateProductCommand): DuplicateProductResponse
    {
        if (!$this->isUserAllowedToDuplicateProduct($duplicateProductCommand->productToDuplicateUuid(), $duplicateProductCommand->userId())) {
            throw new ObjectNotFoundException(
                sprintf(
                    'Product "%s" is not editable by user id "%s".',
                    $duplicateProductCommand->productToDuplicateUuid()->toString(),
                    $duplicateProductCommand->userId()
                )
            );
        }

        /** @var ProductInterface $productToDuplicate */
        $productToDuplicate = $this->productRepository->find($duplicateProductCommand->productToDuplicateUuid());

        $normalizedProduct = $this->normalizeProductWithNewIdentifier($productToDuplicate, $duplicateProductCommand->duplicatedProductIdentifier());

        $duplicatedProduct = $this->productBuilder->createProduct(
            $duplicateProductCommand->duplicatedProductIdentifier(),
            $productToDuplicate->getFamily()?->getCode()
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

    private function normalizeProductWithNewIdentifier(ProductInterface $productToDuplicate, ?string $newIdentifier): array
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

    private function isUserAllowedToDuplicateProduct(UuidInterface $productToDuplicateUuid, int $userId): bool
    {
        $userRightsOnProduct = $this->fetchUserRightsOnProduct->fetchByUuid(
            $productToDuplicateUuid,
            $userId
        );

        return $this->securityFacade->isGranted('pimee_enrichment_product_duplicate')
            && $this->securityFacade->isGranted('pim_enrich_product_create')
            && $userRightsOnProduct->isProductEditable();
    }
}
