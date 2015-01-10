<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint on a value.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ValueCondition extends Constraint
{
    /** @var string */
    public $message = '%message%';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_constraint_value_condition_validator';
    }
}
