<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Domain\Model\Azure\Blob;

use Psr\Http\Message\ResponseInterface;

final class BlobProperties
{
    /**
     * @param array<string, string> $metadata
     */
    public function __construct(
        public readonly \DateTimeInterface $lastModified,
        public readonly int $contentLength,
        public readonly string $contentType,
        public readonly ?string $contentMD5,
    ) {
    }

    public static function fromResponseHeaders(ResponseInterface $response): self
    {
        return new BlobProperties(
            self::getDateDeserialized($response->getHeaderLine('Last-Modified')),
            (int) $response->getHeaderLine('Content-Length'),
            $response->getHeaderLine('Content-Type'),
            self::deserializeContentMD5($response->getHeaderLine('Content-MD5')),
        );
    }

    public static function fromXml(\SimpleXMLElement $xml): self
    {
        return new self(
            self::getDateDeserialized((string) $xml->{'Last-Modified'}),
            (int) $xml->{'Content-Length'},
            (string) $xml->{'Content-Type'},
            self::deserializeContentMD5((string) $xml->{'Content-MD5'}),
        );
    }

    public static function deserializeContentMD5(string $contentMD5): ?string
    {
        $result = base64_decode($contentMD5, true);
        if ($result === false) {
            return null;
        }

        return bin2hex($result);
    }

    private static function getDateDeserialized(string $date): \DateTimeImmutable
    {
        $dateTime = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC1123, $date);
        if ($dateTime === false) {
            throw new \Exception("Azure returned a malformed date.");
        }

        return $dateTime;
    }
}
