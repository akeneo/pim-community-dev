<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductModelCodesQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductsQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\QuantifiedAssociations as QuantifiedAssociationsConstraint;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationsValidator extends ConstraintValidator
{
    const ALLOWED_LINK_TYPES = [
        'products',
        'product_uuids',
        'product_models',
    ];

    const MAX_ASSOCIATIONS = 100;
    const MIN_QUANTITY = 1;
    const MAX_QUANTITY = 2147483647;

    public function __construct(
        private AssociationTypeRepositoryInterface $associationTypeRepository,
        private FindNonExistingProductsQueryInterface $findNonExistingProductsQuery,
        private FindNonExistingProductModelCodesQueryInterface $findNonExistingProductModelCodesQuery
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof QuantifiedAssociationCollection) {
            throw new UnexpectedTypeException($value, QuantifiedAssociationCollection::class);
        }

        if (!$constraint instanceof QuantifiedAssociationsConstraint) {
            throw new UnexpectedTypeException($constraint, QuantifiedAssociationsConstraint::class);
        }

        $this->disablePropertyPathNormalization($constraint);
        $normalized = $value->normalize();

        foreach ($normalized as $associationTypeCode => $targets) {
            $propertyPath = sprintf('%s', $associationTypeCode);
            $productsPropertyPath = sprintf('%s.products', $propertyPath);
            $productModelsPropertyPath = sprintf('%s.product_models', $propertyPath);

            $this->validateAssociationType($associationTypeCode, $propertyPath);
            $this->validateLinkTypes(array_keys($targets), $propertyPath);

            foreach ($targets['products'] as $index => $quantifiedLink) {
                $quantityPropertyPath = sprintf('%s[%d].quantity', $productsPropertyPath, $index);

                $this->validateAssociationQuantity($quantifiedLink['quantity'], $quantityPropertyPath);
            }

            foreach ($targets['product_models'] as $index => $quantifiedLink) {
                $quantityPropertyPath = sprintf('%s[%d].quantity', $productModelsPropertyPath, $index);

                $this->validateAssociationQuantity($quantifiedLink['quantity'], $quantityPropertyPath);
            }

            $this->validateProductsExist($targets['products'], $productsPropertyPath);
            $this->validateProductsExist($targets['product_uuids'] ?? [], $productsPropertyPath);
            $this->validateProductModelsExist($targets['product_models'], $productModelsPropertyPath);

            $totalQuantifiedLinkCount = count($targets['product_models']) + count($targets['products']);
            $this->validateTotalCount($totalQuantifiedLinkCount, $propertyPath);
        }
    }

    private function validateAssociationType(
        string $associationTypeCode,
        string $propertyPath
    ): void {
        $associationType = $this->associationTypeRepository->findOneByIdentifier($associationTypeCode);

        if (null === $associationType) {
            $this->context->buildViolation(
                QuantifiedAssociationsConstraint::ASSOCIATION_TYPE_DOES_NOT_EXIST_MESSAGE
            )
                ->atPath($propertyPath)
                ->addViolation();

            return;
        }

        if (!$associationType->isQuantified()) {
            $this->context->buildViolation(
                QuantifiedAssociationsConstraint::ASSOCIATION_TYPE_IS_NOT_QUANTIFIED_MESSAGE,
                [
                    '{{ association_type }}' => $associationType->getCode(),
                ]
            )
                ->atPath($propertyPath)
                ->addViolation();
        }
    }

    private function validateLinkTypes(array $linkTypes, string $propertyPath): void
    {
        foreach ($linkTypes as $linkType) {
            if (!in_array($linkType, self::ALLOWED_LINK_TYPES)) {
                $this->context->buildViolation(
                    QuantifiedAssociationsConstraint::LINK_TYPE_UNEXPECTED_MESSAGE,
                    [
                        '{{ value }}' => $linkType,
                        '{{ allowed }}' => implode(',', self::ALLOWED_LINK_TYPES),
                    ]
                )
                    ->atPath($propertyPath)
                    ->addViolation();
            }
        }
    }

    private function validateProductsExist(array $quantifiedLinks, string $propertyPath): void
    {
        $productIdentifiers = [];
        $productUuids = [];
        foreach ($quantifiedLinks as $quantifiedLink) {
            if (isset($quantifiedLink['identifier'])) {
                $productIdentifiers[] = $quantifiedLink['identifier'];
            } else {
                $productUuids[] = $quantifiedLink['uuid'];
            }
        }

        $nonExistingProductIdentifiers = $this->findNonExistingProductsQuery->byProductIdentifiers(
            $productIdentifiers
        );

        $nonExistingProductUuids = $this->findNonExistingProductsQuery->byProductUuids(
            $productUuids
        );

        $nonExistingProducts = array_merge($nonExistingProductIdentifiers, $nonExistingProductUuids);

        if (count($nonExistingProducts) > 0) {
            $this->context->buildViolation(
                QuantifiedAssociationsConstraint::PRODUCTS_DO_NOT_EXIST_MESSAGE,
                [
                    '{{ values }}' => implode(', ', $nonExistingProducts),
                ]
            )
                ->atPath($propertyPath)
                ->setCode(QuantifiedAssociations::PRODUCTS_DO_NOT_EXIST_ERROR)
                ->addViolation();
        }
    }

    private function validateProductModelsExist(array $quantifiedLinks, string $propertyPath): void
    {
        $productModelCodes = array_map(
            function ($quantifiedLink) {
                return $quantifiedLink['identifier'];
            },
            $quantifiedLinks
        );

        $nonExistingProductModelCodes = $this->findNonExistingProductModelCodesQuery->execute($productModelCodes);
        if (count($nonExistingProductModelCodes) > 0) {
            $this->context->buildViolation(
                QuantifiedAssociationsConstraint::PRODUCT_MODELS_DO_NOT_EXIST_MESSAGE,
                [
                    '{{ values }}' => implode(', ', $nonExistingProductModelCodes),
                ]
            )
                ->atPath($propertyPath)
                ->setCode(QuantifiedAssociations::PRODUCT_MODELS_DO_NOT_EXIST_ERROR)
                ->addViolation();
        }
    }

    private function validateAssociationQuantity($quantity, string $propertyPath): void
    {
        if (!preg_match('/^[0-9]{1,10}$/', $quantity)
            || intval($quantity) < self::MIN_QUANTITY
            || intval($quantity) > self::MAX_QUANTITY) {
            $this->context->buildViolation(
                QuantifiedAssociationsConstraint::INVALID_QUANTITY_MESSAGE,
                [
                    '{{ value }}' => $quantity,
                    '{{ min }}' => self::MIN_QUANTITY,
                    '{{ max }}' => self::MAX_QUANTITY,
                ]
            )
                ->atPath($propertyPath)
                ->addViolation();
        }
    }

    private function validateTotalCount(int $count, string $propertyPath): void
    {
        if ($count > self::MAX_ASSOCIATIONS) {
            $this->context->buildViolation(
                QuantifiedAssociationsConstraint::MAX_ASSOCIATIONS_MESSAGE,
                [
                    '{{ value }}' => $count,
                    '{{ limit }}' => self::MAX_ASSOCIATIONS,
                ]
            )
                ->atPath($propertyPath)
                ->addViolation();
        }
    }

    /**
     * The Violation normalizer rename property paths with uppercase characters, this method disable this behavior
     * @see https://github.com/akeneo/pim-community-dev/blob/9b9f18385d51f5d2147a79374bd5b5aa9eae3464/src/Akeneo/Tool/Component/Api/Normalizer/Exception/ViolationNormalizer.php#L144
     */
    private function disablePropertyPathNormalization(QuantifiedAssociationsConstraint $constraint): void
    {
        $constraint->payload['normalize_property_path'] = false;
    }
}
