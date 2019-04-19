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
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsNumericValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsNumeric) {
            throw new UnexpectedTypeException($constraint, IsNumeric::class);
        }

        if ($value instanceof MetricInterface || $value instanceof ProductPriceInterface) {
            $propertyPath = 'data';
            $value = $value->getData();
        }
        if (null === $value) {
            return;
        }
        if (!is_numeric($value)) {
            $violation = $this->context->buildViolation($constraint->message);
            if (isset($propertyPath)) {
                $violation->atPath($propertyPath);
            }

            $violation->addViolation();
        }
    }
}
