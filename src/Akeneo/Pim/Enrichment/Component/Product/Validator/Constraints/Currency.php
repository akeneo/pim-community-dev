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
    const CURRENCY = '9b385d80-5d67-494b-b824-0b59b84d609a';

    public string $message = 'Please specify a valid currency for the %attribute_code% attribute, the %currency_code% code was sent.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_currency_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return [self::PROPERTY_CONSTRAINT, self::CLASS_CONSTRAINT];
    }
}
