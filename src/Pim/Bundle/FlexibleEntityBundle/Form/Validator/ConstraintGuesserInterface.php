<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Validator;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Constraint guesser interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConstraintGuesserInterface
{
    /**
     * Tells wether or not the constraint guesser supports the given attribute type
     *
     * @param AbstractAttribute $attribute
     *
     * @return bool
     */
    public function supportAttribute(AbstractAttribute $attribute);

    /**
     * Guess the constraints for the given attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return Symfony\Component\Validator\Constraint[]
     */
    public function guessConstraints(AbstractAttribute $attribute);
}
