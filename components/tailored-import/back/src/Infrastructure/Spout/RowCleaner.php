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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Spout;

class RowCleaner
{
    public function removeTrailingEmptyColumns(array $row): array
    {
        $reversedColumns = array_reverse($row);
        foreach ($reversedColumns as $columnIndex => $cell) {
            if (!empty($cell)) {
                break;
            }

            unset($reversedColumns[$columnIndex]);
        }

        return array_reverse($reversedColumns);
    }

    public function padRowToLength(array $row, int $length): array
    {
        return array_pad($row, $length, '');
    }
}
