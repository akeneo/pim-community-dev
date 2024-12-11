<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob;

final class UploadBlobOptions
{
    public function __construct(
        public ?string $contentType = null,
        public int $initialTransferSize = 256_000_000,
        public int $maximumTransferSize = 8_000_000,
        public int $maximumConcurrency = 25,
    ) {
    }
}
