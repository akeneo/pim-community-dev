<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Domain\Query\MediaFileInfo;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFileInfo
{
    private string $fileKey;
    private string $originalFilename;
    private string $storage;

    public function __construct(string $fileKey, string $originalFilename, string $storage)
    {
        $this->fileKey = $fileKey;
        $this->originalFilename = $originalFilename;
        $this->storage = $storage;
    }

    public function getFileKey(): string
    {
        return $this->fileKey;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getStorage(): string
    {
        return $this->storage;
    }
}
