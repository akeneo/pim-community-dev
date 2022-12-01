<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Storage\CatalogsMappingStorageInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProductMappingSchema array{
 *      properties: array<string, array{type: string}>
 * }
 * @phpstan-import-type ProductMapping from Catalog
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ProductMappingRespectsSchemaValidator extends ConstraintValidator
{
    private const ALLOWED_TYPE_ASSOCIATIONS = [
        'string' => [
            'pim_catalog_text',
        ],
    ];

    public function __construct(
        private CatalogsMappingStorageInterface $catalogsMappingStorage,
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductMappingRespectsSchema) {
            throw new UnexpectedTypeException($constraint, ProductMappingRespectsSchema::class);
        }

        if (!\is_array($value) || (!empty($value) && \array_is_list($value))) {
            return;
        }

        /** @var ProductMappingSchema $schema */
        $schema = \json_decode($this->fetchProductMappingSchema($constraint->productMappingSchemaFile), true, 512, JSON_THROW_ON_ERROR);

        /** @var ProductMapping $value */
        if (!$this->validateTargetsList($value, $schema)) {
            return;
        }

        $this->validateTargetsTypes($value, $schema);
    }

    private function fetchProductMappingSchema(string $productMappingSchemaFile): string
    {
        $productMappingSchema = \stream_get_contents(
            $this->catalogsMappingStorage->read($productMappingSchemaFile)
        );

        if (false === $productMappingSchema) {
            throw new \LogicException('Product mapping schema is unreadable.');
        }

        return $productMappingSchema;
    }

    /**
     * @param ProductMapping $value
     * @param ProductMappingSchema $schema
     */
    private function validateTargetsList(array $value, array $schema): bool
    {
        $missingTargets = \array_diff_key($schema['properties'], $value);
        if (!empty($missingTargets)) {
            $this->context
                ->buildViolation(
                    'akeneo_catalogs.validation.product_mapping.schema.missing_targets',
                    ['{{ targets }}' => \sprintf('"%s"', \implode('", "', \array_keys($missingTargets)))]
                )
                ->addViolation();

            return false;
        }

        $additionalTargets = \array_diff_key($value, $schema['properties']);
        if (!empty($additionalTargets)) {
            $this->context
                ->buildViolation(
                    'akeneo_catalogs.validation.product_mapping.schema.additional_targets',
                    ['{{ targets }}' => \sprintf('"%s"', \implode('", "', \array_keys($additionalTargets)))]
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

            if (!\array_key_exists($schema['properties'][$targetCode]['type'], self::ALLOWED_TYPE_ASSOCIATIONS)) {
                throw new \LogicException(\sprintf('Type "%s" is not supported.', $schema['properties'][$targetCode]['type']));
            }

            if (null === $sourceAssociation['source']) {
                continue;
            }

            $attribute = $this->findOneAttributeByCodeQuery->execute($sourceAssociation['source']);
            $attributeType = $attribute['type'] ?? null;

            if (null === $attributeType || !\in_array($attributeType, self::ALLOWED_TYPE_ASSOCIATIONS[$schema['properties'][$targetCode]['type']])) {
                $this->context
                    ->buildViolation(
                        'akeneo_catalogs.validation.product_mapping.schema.incorrect_type',
                        ['{{ expected_type }}' => $schema['properties'][$targetCode]['type']]
                    )
                    ->atPath("[$targetCode][source]")
                    ->addViolation();
            }
        }
    }
}
