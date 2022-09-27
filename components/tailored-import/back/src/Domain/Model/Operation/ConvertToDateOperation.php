<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

use Webmozart\Assert\Assert;

class ConvertToDateOperation implements OperationInterface
{
    public const TYPE = 'date';
    public const DATE_FORMAT_TO_PHP_DATE_FORMAT_MAPPING = [
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

    public static function getAvailableDateFormats(): array
    {
        return array_keys(self::DATE_FORMAT_TO_PHP_DATE_FORMAT_MAPPING);
    }

    public function __construct(
        private string $uuid,
        private string $dateFormat,
    ) {
        Assert::uuid($uuid);

        if (!array_key_exists($dateFormat, self::DATE_FORMAT_TO_PHP_DATE_FORMAT_MAPPING)) {
            throw new \InvalidArgumentException(sprintf('Date format "%s" is not supported', $dateFormat));
        }
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getDateFormat(): string
    {
        return $this->dateFormat;
    }

    public function normalize(): array
    {
        return [
            'uuid' => $this->uuid,
            'date_format' => $this->dateFormat,
            'type' => self::TYPE,
            'available_date_format' => self::DATE_FORMAT_TO_PHP_DATE_FORMAT_MAPPING,
        ];
    }
}
