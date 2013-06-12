<?php

namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\RangeValidator as BaseRangeValidator;
use Symfony\Component\Validator\Constraint;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeValidator extends BaseRangeValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof ProductPrice) {
            $data = $value->getData();
        }

        parent::validate($data, $constraint);
    }
}

