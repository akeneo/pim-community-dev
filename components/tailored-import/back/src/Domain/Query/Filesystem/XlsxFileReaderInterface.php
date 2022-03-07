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

namespace Akeneo\Platform\TailoredImport\Domain\Query\Filesystem;

interface XlsxFileReaderInterface
{
    public function selectSheet(?string $sheetName): void;

    public function readLine(int $line): array;

    public function getSheetList(): array;
}
