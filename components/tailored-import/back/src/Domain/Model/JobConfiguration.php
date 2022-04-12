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

namespace Akeneo\Platform\TailoredImport\Domain\Model;

use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;

class JobConfiguration
{
    public function __construct(
        private string $fileKey,
        private FileStructure $fileStructure,
        private ColumnCollection $columns,
    ) {
    }

    public function getFileKey(): string
    {
        return $this->fileKey;
    }

    public function getFileStructure(): FileStructure
    {
        return $this->fileStructure;
    }

    public function getColumns(): ColumnCollection
    {
        return $this->columns;
    }
}
