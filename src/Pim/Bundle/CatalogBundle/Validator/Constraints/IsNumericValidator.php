<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
