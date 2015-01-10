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
 * Validation constraint to check that the value is not empty (except for EMPTY operator)
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class NonEmptyValueCondition extends Constraint
{
    /** @var string */
    public $message = 'The key "value" is missing or empty.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
