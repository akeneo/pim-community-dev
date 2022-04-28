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
            case $cell instanceof \DateTime:
                return $cell->format('c');
            case is_bool($cell):
                return $cell ? 'TRUE' : 'FALSE';
            case is_string($cell):
            case is_numeric($cell):
                return (string) $cell;
            case is_null($cell):
                /* TODO validate the error message that we want expose to the user */
                throw new \RuntimeException('');
            default:
                throw new \RuntimeException(sprintf('Unsupported cell format "%s"', gettype($cell)));
        }
    }
}
