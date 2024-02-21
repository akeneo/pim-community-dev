<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Writer\File;

use Webmozart\Assert\Assert;

/**
 * Describes a file written during an export
 * It can either be in the local filesystem (e.g: generated flat files) or in a file storage (e.g: media files)
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class WrittenFileInfo
{
    private const LOCAL_FILESYSTEM = 'localFilesystem';

    private string $sourceKey;
    private string $sourceStorage;
    private string $outputFilepath;

    private function __construct(string $sourceKey, string $sourceStorage, string $outputFilepath)
    {
        Assert::stringNotEmpty($sourceKey);
        Assert::stringNotEmpty($sourceStorage);
        Assert::stringNotEmpty($outputFilepath);

        $this->sourceKey = $sourceKey;
        $this->sourceStorage = $sourceStorage;
        $this->outputFilepath = $outputFilepath;
    }

    public static function fromFileStorage(string $sourceKey, string $sourceStorage, string $outputFilepath): self
    {
        return new self($sourceKey, $sourceStorage, $outputFilepath);
    }

    public static function fromLocalFile(string $localFilepath, string $outputFilepath): self
    {
        return new self($localFilepath, self::LOCAL_FILESYSTEM, $outputFilepath);
    }

    public function sourceKey(): string
    {
        return $this->sourceKey;
    }

    public function sourceStorage(): string
    {
        return $this->sourceStorage;
    }

    public function outputFilepath(): string
    {
        return $this->outputFilepath;
    }

    public function isLocalFile(): bool
    {
        return self::LOCAL_FILESYSTEM === $this->sourceStorage;
    }
}
