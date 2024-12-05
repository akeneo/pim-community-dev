<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob;

final class Blob
{
    public function __construct(
        public readonly string         $name,
        public readonly BlobProperties $properties,
    ) {
    }

    public static function fromXml(\SimpleXMLElement $xml): self
    {
        return new self(
            (string) $xml->Name,
            BlobProperties::fromXml($xml->Properties),
        );
    }
}
