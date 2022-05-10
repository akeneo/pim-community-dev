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

namespace Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage;

final class FileToTransfer
{
    public function __construct(
        private string $fileKey,
        private string $storage,
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

    public function isLocal(): bool
    {
        return $this->isLocal;
    }
}
