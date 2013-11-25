<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Oro\Bundle\FlexibleEntityBundle\Entity\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotDecimalValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof Metric || $value instanceof ProductPrice) {
            $value = $value->getData();
        }
        if (null === $value) {
            return;
        }
        if (is_numeric($value) && floor($value) != $value) {
            $this->context->addViolation($constraint->message);
        }
    }
}
