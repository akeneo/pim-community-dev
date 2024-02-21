<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for unique attribute value
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValue extends Constraint
{
    const UNIQUE_VALUE = '4313666f-d637-4c7a-a515-0cf9693ca5ef';

    /** @var string */
    public $message = 'The {{ attribute_code }} attribute can not have the same value more than once. The {{ value }} value is already set on another product.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_unique_value_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
