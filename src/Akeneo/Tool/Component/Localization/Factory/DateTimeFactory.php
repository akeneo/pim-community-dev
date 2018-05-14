<?php

namespace Akeneo\Tool\Component\Localization\Factory;

/**
 * Create a new instance of IntlDateFormatter
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFactory extends DateFactory
{
    const TYPE_TIME = \IntlDateFormatter::SHORT;
}
