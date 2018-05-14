<?php

namespace Akeneo\Tool\Component\Localization\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Localized date constraint
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFormat extends Constraint
{
    /** @var string */
    public $message = 'This type of value expects the use of the format {{ date_format }} for dates.';

    /** @var string */
    public $dateFormat;

    /** @var string */
    public $path;

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_localization_date_format';
    }
}
