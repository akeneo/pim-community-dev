<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Constraint
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsNumericValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsNumeric) {
            throw new UnexpectedTypeException($constraint, IsNumeric::class);
        }

        $propertyPath = null;

        if ($value instanceof MetricInterface || $value instanceof ProductPriceInterface) {
            $propertyPath = 'data';
            $value = $value->getData();
        }

        if (null === $value) {
            return;
        }

        if (!is_numeric($value)) {
            $this->buildViolation($constraint, IsNumeric::SHOULD_BE_NUMERIC_MESSAGE, $value, $propertyPath);
        }

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        if (is_string($value) && str_contains($value, ' ')) {
            $this->buildViolation($constraint, IsNumeric::SHOULD_NOT_CONTAINS_SPACE_MESSAGE, $value, $propertyPath);
        }
    }

    private function buildViolation(IsNumeric $constraint, string $message, mixed $value, ?string $path): void
    {
        $violation = $this->context->buildViolation(
            $message,
            [
                '{{ attribute }}' => $constraint->attributeCode,
                '{{ value }}' => $value,
            ]
        )
            ->setCode(IsNumeric::IS_NUMERIC);

        if ($path) {
            $violation->atPath($path);
        }

        $violation->addViolation();
    }
}
