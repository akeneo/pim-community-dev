<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotDecimal extends Constraint
{
    const NOT_DECIMAL = 'a2ea1e5f-9310-4213-891e-84347c550587';

    /** @var string */
    public $message = 'The %attribute% attribute requires a non-decimal value, and %value% is not a valid value.';

    /** @var string */
    public $attributeCode = '';
}
