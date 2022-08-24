<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Akeneo\Catalogs\Application\Persistence\GetMeasurementsFamilyQueryInterface;
use Akeneo\Catalogs\Domain\Operator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
 * @phpstan-type Attribute array{code: string, label: string, type: string, scopable: bool, localizable: bool}
 */
final class AttributeCriterionContainsValidMeasurementValidator extends ConstraintValidator
{
    public function __construct(
        private GetMeasurementsFamilyQueryInterface $getMeasurementsFamilyQuery,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeCriterionContainsValidMeasurement) {
            throw new UnexpectedTypeException($constraint, AttributeCriterionContainsValidMeasurement::class);
        }

        if (!\in_array($value['operator'], [Operator::IS_EMPTY, Operator::IS_NOT_EMPTY], true)) {
            /** @var array{code: string, measurements: array<array{code: string, label: string}>}|null $measurementFamily */
            $measurementFamily = $this->getMeasurementsFamilyQuery->execute($value['field'], 'en_US');

            if (null === $measurementFamily) {
                throw new NotFoundHttpException('The measurements family is not found');
            }
            $measurementCodes = \array_map(static fn (array $row) => $row['code'], $measurementFamily['measurements']);

            if (!\in_array($value['value']['unit'], $measurementCodes, true)) {
                $this->context
                    ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.measurement.unit', [
                        '{field}' => $value['field'],
                    ])
                    ->atPath('[locale]')
                    ->addViolation();
            }
        }
    }
}
