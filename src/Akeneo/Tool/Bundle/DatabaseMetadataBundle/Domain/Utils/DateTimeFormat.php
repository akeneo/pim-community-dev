<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils;

use Closure;
use DateTime;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class DateTimeFormat extends \DateTime
{
    public static function formatFromIso(): Closure
    {
        return function (string &$date) {
            $time = strtotime($date);
            return (new DateTime)->setTimestamp($time);
        };
    }

    public static function formatFromString(): Closure
    {
        return function (string &$date) {
            return new DateTime($date);
        };
    }

    public static function formatFromInt(): Closure
    {
        return function (int &$date) {
            return (new DateTime)->setTimestamp($date);
        };
    }
}
