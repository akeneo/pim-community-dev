<?php

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Measurement\GetMeasurementsFamilyQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpstan-type AttributeSource array{source: string, scope: string|null, locale: string|null, parameters: array{unit: string}}
 * @phpstan-type Attribute array{
 *    attribute_group_code: string,
 *    attribute_group_label: string,
 *    code: string,
 *    default_measurement_unit?: string,
 *    label: string,
 *    localizable: bool,
 *    measurement_family?: string,
 *    scopable: bool,
 *    type: string
 * }
 * @phpstan-import-type RawMeasurementFamily from GetMeasurementsFamilyQueryInterface
 */
final class AttributeSourceContainsValidMetricUnitValidator extends ConstraintValidator
{
    public function __construct(
        private readonly FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private readonly GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeSourceContainsValidMetricUnit) {
            throw new UnexpectedTypeException($constraint, AttributeSourceContainsValidMetricUnit::class);
        }

        if (!\is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        /** @var AttributeSource $value */

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['source']);

        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        $this->validateMetricUnitExists($value['parameters']['unit'], $attribute);
    }

    /**
     * @param Attribute $attribute
     */
    private function validateMetricUnitExists(string $unit, array $attribute): void
    {
        $attributeUnits = [];
        if (isset($attribute['measurement_family'])) {
            /** @var RawMeasurementFamily $measurementFamilies */
            $measurementFamilies = $this->getMeasurementsFamilyQuery->execute($attribute['measurement_family']);
            $attributeUnits = \array_map(static fn (array $row): string => $row['code'], $measurementFamilies['units']);
        }

        if (!\in_array($unit, $attributeUnits)) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.measurement.unit.not_exist')
                ->atPath('[parameters][unit]')
                ->addViolation();
        }
    }
}
