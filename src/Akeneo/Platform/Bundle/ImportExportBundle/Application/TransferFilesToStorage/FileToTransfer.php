<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage;

final class FileToTransfer
{
    public function __construct(
        private string $fileKey,
        private string $storage,
        private string $outputFileName,
        private bool $isLocal
    ) {
    }

    public function getFileKey(): string
    {
        return $this->fileKey;
    }

    public function getStorage(): string
    {
        return $this->storage;
    }

    public function getOutputFileName(): string
    {
        return $this->outputFileName;
    }

    public function isLocal(): bool
    {
        return $this->isLocal;
    }
}
