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
    /**
     * Whether the file is meant to be archived or not in the output storage
     */
    private bool $sendToOutputStorage;

    private function __construct(
        string $key,
        string $storage,
        string $outputFilepath,
        bool $sendToOutputStorage
    ) {
        $this->sourceKey = $key;
        $this->sourceStorage = $storage;
        $this->outputFilepath = $outputFilepath;
        $this->sendToOutputStorage = $sendToOutputStorage;
    }

    public static function fromFileStorage(
        string $sourceKey,
        string $sourceStorage,
        string $outputFilepath,
        bool $sendToOutputStorage = true
    ): self {
        Assert::stringNotEmpty($sourceKey);
        Assert::stringNotEmpty($sourceStorage);
        Assert::stringNotEmpty($outputFilepath);

        return new self($sourceKey, $sourceStorage, $outputFilepath, $sendToOutputStorage);
    }

    public static function fromLocalFile(
        string $localFilepath,
        string $outputFilepath,
        bool $sendToOutputStorage = true
    ): self {
        return new self($localFilepath, self::LOCAL_FILESYSTEM, $outputFilepath, $sendToOutputStorage);
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

    public function sendToOutputStorage(): bool
    {
        return $this->sendToOutputStorage;
    }
}
