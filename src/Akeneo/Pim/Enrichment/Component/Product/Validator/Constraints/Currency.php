<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for price attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Currency extends Constraint
{
    /**
     * Violation message for invalid currency
     *
     * @var string
     */
    public $unitMessage = 'Please specify a valid currency';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_currency_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
