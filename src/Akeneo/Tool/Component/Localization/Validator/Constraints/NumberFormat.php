<?php

namespace Akeneo\Tool\Component\Localization\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Localized number constraint
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFormat extends Constraint
{
    /** @var string */
    public $message = 'This type of value expects the use of {{ decimal_separator }} to separate decimals.';

    /** @var string */
    public $decimalSeparator;

    /** @var string */
    public $path;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_localization_number_format';
    }
}
