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

use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\FileInfo;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\StoreFileInterface;

class UploadStructureFileHandler
{
    public function __construct(
        private StoreFileInterface $storeFile,
    ) {
    }

    public function handle(UploadStructureFileCommand $command): FileInfo
    {
        return $this->storeFile->store($command->getFilePath(), $command->getOriginalFilename());
    }
}
