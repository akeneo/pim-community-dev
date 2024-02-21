<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedAttributeConstraintGuesser implements ConstraintGuesserInterface
{
    protected $guessers = [];

    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        $constraints = [];

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
