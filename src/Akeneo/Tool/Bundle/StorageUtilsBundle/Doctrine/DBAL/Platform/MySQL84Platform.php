<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\Platform;

use Doctrine\DBAL\Platforms\MySQL80Platform;

class MySQL84Platform extends MySQL80Platform
{
    /**
     * MySQL returns DATETIME(6) values with microseconds (e.g. "2026-06-02 15:52:16.000000")
     * and regular DATETIME values without (e.g. "2026-06-02 15:52:16"). When a value is
     * provided, the format is detected from it directly, since DBAL 2.x does not pass column
     * metadata to Type::convertToPHPValue.
     */
    public function getDateTimeFormatString(string $value = ''): string
    {
        return str_contains($value, '.') ? 'Y-m-d H:i:s.u' : 'Y-m-d H:i:s';
    }
}
