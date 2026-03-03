<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-type ImageData array{
 *     size: int,
 *     extension: string,
 *     file_path: string,
 *     mime_type: string,
 *     original_filename: string,
 * }
 */
class ImageDataValue
{
    private function __construct(
        private readonly int $size,
        private readonly string $extension,
        private readonly string $filePath,
        private readonly string $mimeType,
        private readonly string $originalFilename,
    ) {
    }

    /**
     * @param array{
     *     size: int,
     *     extension: string,
     *     file_path: string,
     *     mime_type: string,
     *     original_filename: string,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        Assert::keyExists($data, 'size');
        Assert::integer($data['size']);
        Assert::keyExists($data, 'extension');
        Assert::stringNotEmpty($data['extension']);
        Assert::keyExists($data, 'file_path');
        Assert::stringNotEmpty($data['file_path']);
        Assert::keyExists($data, 'mime_type');
        Assert::stringNotEmpty($data['mime_type']);
        Assert::keyExists($data, 'original_filename');
        Assert::stringNotEmpty($data['original_filename']);

        return new self(
            size: $data['size'],
            extension: $data['extension'],
            filePath: $data['file_path'],
            mimeType: $data['mime_type'],
            originalFilename: $data['original_filename'],
        );
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    /**
     * @return array{
     *     size: int,
     *     extension: string,
     *     file_path: string,
     *     mime_type: string,
     *     original_filename: string,
     * }
     */
    public function normalize(): array
    {
        return [
            'size' => $this->size,
            'extension' => $this->extension,
            'file_path' => $this->filePath,
            'mime_type' => $this->mimeType,
            'original_filename' => $this->originalFilename,
        ];
    }
}
