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

use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;

class CellsFormatter
{
    /**
     * @params array<mixed> $cells
     *
     * @return array<string>
     */
    public function formatCells(array $cells): array
    {
        return array_map(fn ($cell) => $this->formatCell($cell), $cells);
    }

    private function formatCell(mixed $cell): string
    {
        switch (true) {
            case is_bool($cell):
                return $cell ? 'TRUE' : 'FALSE';
            case is_string($cell):
                if (is_numeric($cell)) {
                    return rtrim((string) number_format((float) $cell, decimals: MeasureConverter::SCALE, thousands_separator: ''), '0');
                }

                return $cell;
            case is_int($cell):
                return (string) $cell;
            case is_float($cell):
                return rtrim((string) number_format($cell, decimals: MeasureConverter::SCALE, thousands_separator: ''), '0');
            case is_null($cell):
                /* TODO validate the error message that we want expose to the user */
                throw new \RuntimeException('');
            case $cell instanceof \DateTime:
            default:
                throw new \RuntimeException(sprintf('Unsupported cell format "%s"', gettype($cell)));
        }
    }
}
