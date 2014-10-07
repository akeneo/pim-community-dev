<?php

namespace Pim\Bundle\CatalogBundle\Validator;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
     * Tells whether or not the constraint guesser supports the given attribute type
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportAttribute(AttributeInterface $attribute);

    /**
     * Guess the constraints for the given attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return Symfony\Component\Validator\Constraint[]
     */
    public function guessConstraints(AttributeInterface $attribute);
}
