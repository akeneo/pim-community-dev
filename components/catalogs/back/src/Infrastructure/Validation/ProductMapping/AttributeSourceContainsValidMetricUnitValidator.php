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
 * @phpstan-type AttributeSource array{source: string, scope: string|null, locale: string|null}
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
 */
final class AttributeSourceContainsValidMetricUnitValidator extends ConstraintValidator
{
    public function __construct(
        private readonly FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private readonly GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery
    )
    {}

    public function validate($value, Constraint $constraint)
    {
       if(!$constraint instanceof AttributeSourceContainsValidMetricUnit) {
           throw new UnexpectedTypeException($constraint, AttributeSourceContainsValidMetricUnit::class);
       }

       if (!\is_array($value)) {
           throw new UnexpectedTypeException($value, 'array');
       }

       /** @var AttributeSource $attribute */
        $attribute = $this->findOneAttributeByCodeQuery->execute($value['source']);

        if (null === $attribute) {
            throw new \LogicException('Attribute not found');
        }

        $this->validateMetricHasUnit($value);
        $this->validateMetricUnitExists($attribute, $value);
    }

    /**
     * @param array $value
     * @return void
     */
    private function validateMetricHasUnit(array $value): void {
        if(!$value['parameters']['unit']) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.measurement.unit.not_empty')
                ->atPath('[parameters][unit]')
                ->addViolation();
        }
    }

    /**
     * @param array $attribute
     * @param array $value
     * @return void
     */
    private function validateMetricUnitExists(array $attribute, array $value): void {
        $measurementFamilies = $this->getMeasurementsFamilyQuery->execute($attribute['measurement_family']);
        $units = \array_map(static fn (array $row) => $row['code'], $measurementFamilies['units']);

        if (!\in_array($value['parameters']['unit'], $units)) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_mapping.source.measurement.unit.not_exist')
                ->atPath('[parameters][unit]')
                ->addViolation();
        }
    }
}
