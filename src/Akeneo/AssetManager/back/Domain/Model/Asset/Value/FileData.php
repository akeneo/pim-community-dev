<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class FileData implements ValueDataInterface
{
    private const KEY = 'filePath';
    private const ORIGINAL_FILENAME = 'originalFilename';
    private const FILE_SIZE = 'size';
    private const MIME_TYPE = 'mimeType';
    private const EXTENSION = 'extension';
    private const UPDATED_AT = 'updatedAt';

    /** @var string */
    private $key;

    /** @var string */
    private $originalFilename;

    /** @var int */
    private $size;

    /** @var string */
    private $mimeType;

    /** @var string */
    private $extension;

    /** @var \DateTime */
    private $updatedAt;

    // TODO: make the optional args mandatory
    private function __construct(
        string $key,
        string $originalFilename,
        ?int $size = 0,
        ?string $mimeType = '',
        ?string $extension = '',
        ?\DateTime $updatedAt = null
    ) {
        Assert::stringNotEmpty($key, 'File data key cannot be empty');
        Assert::stringNotEmpty($originalFilename, 'Original filename data cannot be empty');

        $this->key = $key;
        $this->originalFilename = $originalFilename;
        $this->size = $size;
        $this->mimeType = $mimeType;
        $this->extension = $extension;
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    /**
     * @return array
     */
    public function normalize()
    {
        return [
            self::KEY => $this->key,
            self::ORIGINAL_FILENAME => $this->originalFilename,
            self::FILE_SIZE => $this->size,
            self::MIME_TYPE => $this->mimeType,
            self::EXTENSION => $this->extension,
            self::UPDATED_AT => $this->updatedAt->format(\DateTime::ISO8601),
        ];
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public static function createFromFileinfo(FileInfoInterface $fileInfo): ValueDataInterface
    {
        return new self(
            $fileInfo->getKey(),
            $fileInfo->getOriginalFilename(),
            $fileInfo->getSize(),
            $fileInfo->getMimeType(),
            $fileInfo->getExtension()
        );
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::isArray($normalizedData, 'Normalized file data should be an array');

        $keys = [
            self::KEY,
            self::ORIGINAL_FILENAME,
            self::FILE_SIZE,
            self::MIME_TYPE,
            self::EXTENSION,
            self::UPDATED_AT,
        ];

        foreach ($keys as $key) {
            Assert::keyExists($normalizedData, $key, sprintf(
                'The key "%s" should be present in the normalized file data', $key
            ));
        }

        $updatedAt = \DateTime::createFromFormat(\DateTime::ISO8601, $normalizedData[self::UPDATED_AT]);
        if (false === $updatedAt) {
            var_dump($normalizedData[self::UPDATED_AT]);
            $updatedAt = null;
        }

        return new self(
            $normalizedData[self::KEY],
            $normalizedData[self::ORIGINAL_FILENAME],
            $normalizedData[self::FILE_SIZE],
            $normalizedData[self::MIME_TYPE],
            $normalizedData[self::EXTENSION],
            $updatedAt
        );
    }
}
