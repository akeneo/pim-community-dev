<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociations as QuantifiedAssociationsModel;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductIdentifiersQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductModelCodesQueryInterface;
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
    const ALLOWED_TARGET_TYPES = [
        'products',
        'product_models',
    ];

    const MAX_ASSOCIATIONS = 100;
    const MIN_QUANTITY = 1;
    const MAX_QUANTITY = 9999;

    /** @var AssociationTypeRepositoryInterface */
    private $associationTypeRepository;

    /** @var FindNonExistingProductIdentifiersQueryInterface */
    private $findNonExistingProductIdentifiersQuery;

    /** @var FindNonExistingProductModelCodesQueryInterface */
    private $findNonExistingProductModelCodesQuery;

    public function __construct(
        AssociationTypeRepositoryInterface $associationTypeRepository,
        FindNonExistingProductIdentifiersQueryInterface $findNonExistingProductIdentifiersQuery,
        FindNonExistingProductModelCodesQueryInterface $findNonExistingProductModelCodesQuery
    ) {
        $this->associationTypeRepository = $associationTypeRepository;
        $this->findNonExistingProductIdentifiersQuery = $findNonExistingProductIdentifiersQuery;
        $this->findNonExistingProductModelCodesQuery = $findNonExistingProductModelCodesQuery;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof QuantifiedAssociationsModel) {
            throw new UnexpectedTypeException($value, QuantifiedAssociationsModel::class);
        }

        if (!$constraint instanceof QuantifiedAssociationsConstraint) {
            throw new UnexpectedTypeException($constraint, QuantifiedAssociationsConstraint::class);
        }

        $normalized = $value->normalize();

        foreach ($normalized as $associationTypeCode => $targets) {
            $propertyPath = sprintf('[%s]', $associationTypeCode);

            $this->validateAssociationTypeExists($associationTypeCode, $propertyPath);
            $this->validateTargetTypes(array_keys($targets), $propertyPath);

            foreach ($targets['products'] as $index => $quantifiedLink) {
                $quantityPropertyPath = sprintf('%s[%d][\'products\']', $propertyPath, $index);

                $this->validateAssociationQuantity($quantifiedLink['quantity'], $quantityPropertyPath);
            }

            foreach ($targets['product_models'] as $index => $quantifiedLink) {
                $quantityPropertyPath = sprintf('%s[%d][\'product_models\']', $propertyPath, $index);

                $this->validateAssociationQuantity($quantifiedLink['quantity'], $quantityPropertyPath);
            }

            $this->validateProductsExist($targets['products']);
            $this->validateProductModelsExist($targets['product_models']);

            $totalQuantifiedLinkCount = count($targets['product_models']) + count($targets['products']);
            $this->validateTotalCount($totalQuantifiedLinkCount);
        }
    }

    private function validateAssociationTypeExists($associationTypeCode, string $propertyPath = ''): void
    {
        $associationType = $this->associationTypeRepository->findOneByIdentifier($associationTypeCode);

        if (null === $associationType) {
            $this->context->buildViolation(
                QuantifiedAssociationsConstraint::ASSOCIATION_TYPE_DOES_NOT_EXIST_MESSAGE
            )
                ->atPath($propertyPath)
                ->addViolation();
        }
    }

    private function validateTargetTypes(array $targetTypes, string $propertyPath = ''): void
    {
        foreach ($targetTypes as $targetType) {
            if (!in_array($targetType, self::ALLOWED_TARGET_TYPES)) {
                $this->context->buildViolation(
                    QuantifiedAssociationsConstraint::TARGET_TYPE_UNEXPECTED_MESSAGE,
                    [
                        'value' => $targetType,
                        'allowed' => implode(',', self::ALLOWED_TARGET_TYPES),
                    ]
                )
                    ->atPath($propertyPath)
                    ->addViolation();
            }
        }
    }

    private function validateProductsExist(array $quantifiedLinks): void
    {
        $productIdentifiers = array_map(
            function ($quantifiedLink) {
                return $quantifiedLink['identifier'];
            },
            $quantifiedLinks
        );

        $nonExistingProductIdentifiers = $this->findNonExistingProductIdentifiersQuery->execute(
            $productIdentifiers
        );
        if (count($nonExistingProductIdentifiers) > 0) {
            $this->context->addViolation(
                QuantifiedAssociationsConstraint::PRODUCTS_DO_NOT_EXIST_MESSAGE,
                [
                    'values' => implode(', ', $nonExistingProductIdentifiers),
                ]
            );
        }
    }

    private function validateProductModelsExist(array $quantifiedLinks): void
    {
        $productModelCodes = array_map(
            function ($quantifiedLink) {
                return $quantifiedLink['identifier'];
            },
            $quantifiedLinks
        );

        $nonExistingProductModelCodes = $this->findNonExistingProductModelCodesQuery->execute($productModelCodes);
        if (count($nonExistingProductModelCodes) > 0) {
            $this->context->addViolation(
                QuantifiedAssociationsConstraint::PRODUCT_MODELS_DO_NOT_EXIST_MESSAGE,
                [
                    'values' => implode(', ', $nonExistingProductModelCodes),
                ]
            );
        }
    }

    private function validateAssociationQuantity($quantity, string $propertyPath = ''): void
    {
        if (!preg_match('/^[0-9]{1,4}$/', $quantity)
            || intval($quantity) < self::MIN_QUANTITY
            || intval($quantity) > self::MAX_QUANTITY) {
            $this->context->buildViolation(
                QuantifiedAssociationsConstraint::INVALID_QUANTITY_MESSAGE,
                [
                    'value' => $quantity,
                    'min' => self::MIN_QUANTITY,
                    'max' => self::MAX_QUANTITY,
                ]
            )
                ->atPath($propertyPath)
                ->addViolation();
        }
    }

    private function validateTotalCount(int $count): void
    {
        if ($count > self::MAX_ASSOCIATIONS) {
            $this->context->addViolation(
                QuantifiedAssociationsConstraint::MAX_ASSOCIATIONS_MESSAGE,
                [
                    'value' => $count,
                    'limit' => self::MAX_ASSOCIATIONS,
                ]
            );
        }
    }
}
