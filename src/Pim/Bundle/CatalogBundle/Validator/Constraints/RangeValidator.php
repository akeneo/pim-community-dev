<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\RangeValidator as BaseRangeValidator;
use Symfony\Component\Validator\Constraint;
use Pim\Bundle\FlexibleEntityBundle\Entity\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeValidator extends BaseRangeValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof \DateTime) {
            if ($constraint->min && $value < $constraint->min) {
                $this->context->addViolation(
                    $constraint->minDateMessage,
                    array(
                        '{{ limit }}' => $constraint->min->format('Y-m-d')
                    )
                );
            }

            if ($constraint->max && $value > $constraint->max) {
                $this->context->addViolation(
                    $constraint->maxDateMessage,
                    array(
                        '{{ limit }}' => $constraint->max->format('Y-m-d')
                    )
                );
            }

            return;
        }

        if ($value instanceof Metric || $value instanceof ProductPrice) {
            $value = $value->getData();
        }

        parent::validate($value, $constraint);
    }
}
