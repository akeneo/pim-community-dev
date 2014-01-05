<?php

namespace Pim\Bundle\CatalogBundle\Validator;

use Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedAttributeConstraintGuesser implements ConstraintGuesserInterface
{
    protected $guessers = array();

    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return $attribute instanceof \Pim\Bundle\CatalogBundle\Model\AttributeInterface;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function addConstraintGuesser(ConstraintGuesserInterface $guesser)
    {
        $this->guessers[] = $guesser;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintGuessers()
    {
        return $this->guessers;
    }
}
