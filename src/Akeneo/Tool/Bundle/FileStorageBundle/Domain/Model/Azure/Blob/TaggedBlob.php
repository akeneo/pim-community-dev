<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob;

final class TaggedBlob
{
    /**
     * @param array<string, string> $tags
     */
    public function __construct(
        public readonly string $name,
        public readonly string $containerName,
        public readonly array $tags,
    ) {
    }

    public static function fromXml(\SimpleXMLElement $xml): self
    {
        $tags = [];
        foreach ($xml->Tags->TagSet->children() as $tag) {
            $tags[(string) $tag->Key] = (string) $tag->Value;
        }

        return new self(
            (string) $xml->Name,
            (string) $xml->ContainerName,
            $tags,
        );
    }
}
