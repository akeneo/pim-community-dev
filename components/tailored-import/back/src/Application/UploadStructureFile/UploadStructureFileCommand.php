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

namespace Akeneo\Platform\TailoredImport\Application\UploadStructureFile;

class UploadStructureFileCommand
{
    public function __construct(
        private string $filePath,
        private string $originalFilename,
    ) {
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }
}
