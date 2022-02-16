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

namespace Akeneo\Platform\TailoredImport\Application\UploadFlatFile;

use Akeneo\Platform\TailoredImport\Domain\Filesystem\FlatFileInfo;
use Akeneo\Platform\TailoredImport\Domain\Filesystem\StoreFlatFileInterface;

class UploadFlatFileHandler
{
    public function __construct(
        private StoreFlatFileInterface $storeFlatFile,
    ) {
    }

    public function handle(UploadFlatFileCommand $command): FlatFileInfo
    {
        return $this->storeFlatFile->store($command->filePath, $command->originalFilename);
    }
}
