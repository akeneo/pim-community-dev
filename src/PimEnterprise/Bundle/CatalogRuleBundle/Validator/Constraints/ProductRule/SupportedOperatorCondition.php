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
 * Validation constraint on an operator condition.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class SupportedOperatorCondition extends Constraint
{
    /** @var string */
    public $message = 'The operator "%operator%" is not supported by the field "%field%".';

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
        return 'pimee_supported_operator_condition_validator';
    }
}
