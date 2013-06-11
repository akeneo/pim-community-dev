<?php

namespace Pim\Bundle\ProductBundle\Validator;

use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedAttributeConstraintGuesser implements ConstraintGuesserInterface
{
    protected $guessers = array();

    public function supportAttribute(AbstractAttribute $attribute)
    {
        return $attribute instanceof \Pim\Bundle\ProductBundle\Entity\ProductAttribute;
    }

    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();

        foreach ($this->guessers as $guesser) {
            if ($guesser->supportAttribute($attribute)) {
                $constraints = array_merge(
                    $constraints,
                    $guesser->guessConstraints($attribute)
                );
            }
        }

        return $constraints;
    }

    public function addConstraintGuesser(ConstraintGuesserInterface $guesser)
    {
        $this->guessers[] = $guesser;
    }

    public function getConstraintGuessers()
    {
        return $this->guessers;
    }
}
