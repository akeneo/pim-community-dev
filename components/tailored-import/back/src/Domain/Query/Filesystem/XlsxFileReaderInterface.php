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

namespace Akeneo\Platform\TailoredImport\Domain\Query\Filesystem;

interface XlsxFileReaderInterface
{
    /**
     * @return array<array<string>>
     */
    public function readRows(?string $sheetName, int $start, int $length): array;

    /**
     * @return array<string>
     */
    public function readColumnValues(?string $sheetName, int $productLine, int $columnIndex): array;

    /**
     * @return array<string>
     */
    public function getSheetNames(): array;
}
