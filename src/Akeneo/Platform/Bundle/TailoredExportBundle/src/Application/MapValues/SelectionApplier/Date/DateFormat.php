<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\MapValues\SelectionApplier\Date;

final class DateFormat
{
    private const DATE_FORMAT_TO_PHP_DATE_FORMAT_MAPPING = [
        'yyyy-mm-dd' => 'Y-m-d',
        'yyyy/mm/dd' => 'Y/m/d',
        'yyyy.mm.dd' => 'Y.m.d',
        'yy.m.dd' => 'y.n.d',
        'mm-dd-yyyy' => 'm-d-Y',
        'mm/dd/yyyy' => 'm/d/Y',
        'mm.dd.yyyy' => 'm.d.Y',
        'dd-mm-yyyy' => 'd-m-Y',
        'dd/mm/yyyy' => 'd/m/Y',
        'dd.mm.yyyy' => 'd.m.Y',
        'dd-mm-yy' => 'd-m-y',
        'dd.mm.yy' => 'd.m.y',
        'dd/mm/yy' => 'd/m/y',
        'dd-m-yy' => 'd-n-y',
        'dd/m/yy' => 'd/n/y',
        'dd.m.yy' => 'd.n.y',
    ];

    public static function getAvailableFormats(): array
    {
        return array_keys(self::DATE_FORMAT_TO_PHP_DATE_FORMAT_MAPPING);
    }

    /**
     * @throws \LogicException
     */
    public static function format(\DateTimeInterface $date, string $format): string
    {
        if (!self::isValidFormat($format)) {
            throw new \LogicException(sprintf('Date format "%s" is not supported', $format));
        }

        $phpDateFormat = self::DATE_FORMAT_TO_PHP_DATE_FORMAT_MAPPING[$format];

        return $date->format($phpDateFormat);
    }

    public static function isValidFormat(string $format): bool
    {
        return array_key_exists($format, self::DATE_FORMAT_TO_PHP_DATE_FORMAT_MAPPING);
    }
}
