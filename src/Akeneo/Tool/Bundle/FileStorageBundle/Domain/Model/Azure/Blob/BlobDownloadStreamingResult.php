<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob;

use Psr\Http\Message\StreamInterface;

final class BlobDownloadStreamingResult
{
    public function __construct(
        public readonly StreamInterface $content,
        public readonly BlobProperties  $properties,
    ) {
    }
}
