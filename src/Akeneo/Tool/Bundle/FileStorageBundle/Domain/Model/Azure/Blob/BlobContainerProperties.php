<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob;

use Akeneo\Tool\Bundle\FileStorageBundle\Helpers\Azure\MetadataHelper;
use Psr\Http\Message\ResponseInterface;

final class BlobContainerProperties
{
    /**
     * @param array<string, string> $metadata
     */
    public function __construct(
        public readonly \DateTimeInterface $lastModified,
        public readonly array $metadata,
    ) {
    }

    public static function fromResponseHeaders(ResponseInterface $response): self
    {
        $lastModified = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC1123, $response->getHeaderLine('Last-Modified'));
        if ($lastModified === false) {
            throw new \Exception("Azure returned a malformed date.");
        }

        return new self($lastModified, MetadataHelper::headersToMetadata($response->getHeaders()));
    }
}
