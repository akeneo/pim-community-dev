<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Measurement\GetMeasurementsFamilyQueryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpstan-type AttributeCriterion array{field: string, scope: string|null, locale: string|null}
 * @phpstan-import-type Attribute from FindOneAttributeByCodeQueryInterface
 */
final class AttributeCriterionContainsValidMeasurementValidator extends ConstraintValidator
{
    public function __construct(
        private GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery,
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
    ) {
    }

    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeCriterionContainsValidMeasurement) {
            throw new UnexpectedTypeException($constraint, AttributeCriterionContainsValidMeasurement::class);
        }

        /** @var array{field: string, value: array{unit: string}|null}|null $value */
        if (null === $value || null === $value['value']) {
            return;
        }

        $attribute = $this->findOneAttributeByCodeQuery->execute($value['field']);

        if (null === $attribute || !isset($attribute['measurement_family'])) {
            throw new \LogicException('Attribute not found');
        }

        /** @var array{code: string, units: array<array{code: string, label: string}>}|null $measurementFamily */
        $measurementFamily = $this->getMeasurementsFamilyQuery->execute($attribute['measurement_family'], 'en_US');

        if (null === $measurementFamily) {
            throw new \LogicException('Measurement family not found');
        }

        $units = \array_map(static fn (array $row): string => $row['code'], $measurementFamily['units']);

        if (!\in_array($value['value']['unit'], $units, true)) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.measurement.unit.not_exist', [
                    '{{ field }}' => $value['field'],
                ])
                ->atPath('[locale]')
                ->addViolation();
        }
    }
}
