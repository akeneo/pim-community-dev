<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Exception\NoCompatibleAttributeTypeFoundException;
use Akeneo\Catalogs\Application\Mapping\TargetTypeConverter;
use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\GetProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ProductMapping from Catalog
 * @phpstan-import-type ProductMappingSchema from GetProductMappingSchemaQueryInterface
 * @phpstan-import-type ProductMappingSchemaTarget from GetProductMappingSchemaQueryInterface
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ProductMappingRespectsSchemaValidator extends ConstraintValidator
{
    public function __construct(
        private GetProductMappingSchemaQueryInterface $getProductMappingSchemaQuery,
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private TargetTypeConverter $targetTypeConverter,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductMappingRespectsSchema) {
            throw new UnexpectedTypeException($constraint, ProductMappingRespectsSchema::class);
        }

        if (!$value instanceof Catalog) {
            throw new UnexpectedTypeException($value, Catalog::class);
        }

        if ([] === $value->getProductMapping()) {
            return;
        }

        /** @var ProductMappingSchema $schema */
        $schema = $this->getProductMappingSchemaQuery->execute($value->getId());

        if (!$this->validateTargetsList($value->getProductMapping(), $schema)) {
            return;
        }

        $this->validateTargetsTypes($value->getProductMapping(), $schema);

        $this->validateRequiredTargets($value->getProductMapping(), $schema);
    }

    /**
     * @param ProductMapping $value
     * @param ProductMappingSchema $schema
     */
    private function validateTargetsList(array $value, array $schema): bool
    {
        $missingTargets = \array_diff_key($schema['properties'], $value);
        if ([] !== $missingTargets) {
            $this->context
                ->buildViolation(
                    'akeneo_catalogs.validation.product_mapping.schema.missing_targets',
                    ['{{ targets }}' => \sprintf('"%s"', \implode('", "', \array_keys($missingTargets)))],
                )
                ->addViolation();

            return false;
        }

        $additionalTargets = \array_diff_key($value, $schema['properties']);
        if ([] !== $additionalTargets) {
            $this->context
                ->buildViolation(
                    'akeneo_catalogs.validation.product_mapping.schema.additional_targets',
                    ['{{ targets }}' => \sprintf('"%s"', \implode('", "', \array_keys($additionalTargets)))],
                )
                ->addViolation();

            return false;
        }

        return true;
    }

    /**
     * @param ProductMapping $value
     * @param ProductMappingSchema $schema
     */
    private function validateTargetsTypes(array $value, array $schema): void
    {
        foreach ($value as $targetCode => $sourceAssociation) {
            if ('uuid' === $targetCode) {
                continue;
            }

            try {
                $attributeTypes = $this->targetTypeConverter->toAttributeTypes(
                    $this->targetTypeConverter->flattenTargetType($schema['properties'][$targetCode]),
                    $schema['properties'][$targetCode]['format'] ?? '',
                );
            } catch (NoCompatibleAttributeTypeFoundException $exception) {
                throw new \LogicException(
                    \sprintf(
                        'The combination type "%s" and format "%s" are not supported.',
                        $schema['properties'][$targetCode]['type'],
                        $schema['properties'][$targetCode]['format'] ?? '',
                    ),
                    0,
                    $exception,
                );
            }

            if (null === $sourceAssociation['source']) {
                continue;
            }

            $attributeType = match ($sourceAssociation['source']) {
                'categories' => 'categories',
                default => null,
            };

            if (null === $attributeType) {
                $attribute = $this->findOneAttributeByCodeQuery->execute($sourceAssociation['source']);
                $attributeType = $attribute['type'] ?? null;
            }

            if (null === $attributeType || !\in_array($attributeType, $attributeTypes)) {
                $this->context
                    ->buildViolation(
                        'akeneo_catalogs.validation.product_mapping.schema.incorrect_type',
                        ['{{ expected_type }}' => $schema['properties'][$targetCode]['type']],
                    )
                    ->atPath("[$targetCode][source]")
                    ->addViolation();
            }
        }
    }

    /**
     * @param ProductMapping $productMapping
     * @param ProductMappingSchema $schema
     */
    private function validateRequiredTargets(array $productMapping, array $schema): bool
    {
        if (!isset($schema['required'])) {
            return true;
        }

        foreach ($schema['required'] as $targetCode) {
            if (null === $productMapping[$targetCode]['source']) {
                $this->context
                    ->buildViolation(
                        'akeneo_catalogs.validation.product_mapping.source.required',
                    )
                    ->atPath("productMapping[$targetCode][source]")
                    ->addViolation();
            }
        }

        return false;
    }
}
