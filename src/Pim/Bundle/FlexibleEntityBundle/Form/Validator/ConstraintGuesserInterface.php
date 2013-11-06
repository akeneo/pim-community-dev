<?php

namespace Pim\Bundle\FlexibleEntityBundle\Form\Validator;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Constraint guesser interface
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
