<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Response;

use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\Blob;
use Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob\BlobPrefix;

final class ListBlobsResponseBody
{
    /**
     * @param  Blob[]  $blobs
     * @param  BlobPrefix[]  $blobPrefixes
     */
    private function __construct(
        public readonly string $prefix,
        public readonly string $marker,
        public readonly int $maxResults,
        public readonly string $nextMarker,
        public readonly array $blobs,
        public readonly array $blobPrefixes,
        public readonly ?string $delimiter = null,
    ) {
    }

    public static function fromXml(\SimpleXMLElement $xml): self
    {
        $blobs = [];
        $blobPrefixes = [];

        foreach ($xml->Blobs->children() as $blobOrPrefix) {
            switch ($blobOrPrefix->getName()) {
                case 'Blob':
                    $blobs[] = Blob::fromXml($blobOrPrefix);
                    break;
                case 'BlobPrefix':
                    $blobPrefixes[] = BlobPrefix::fromXml($blobOrPrefix);
                    break;
            }
        }

        return new self(
            (string) $xml->Prefix,
            (string) $xml->Marker,
            (int) $xml->MaxResults,
            (string) $xml->NextMarker,
            $blobs,
            $blobPrefixes,
            (string) $xml->Delimiter,
        );
    }
}
