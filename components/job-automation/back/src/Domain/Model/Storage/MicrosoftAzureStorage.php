<?php

namespace Akeneo\Platform\JobAutomation\Domain\Model\Storage;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Webmozart\Assert\Assert;

final class MicrosoftAzureStorage implements StorageInterface
{
    public const TYPE = 'microsoft_azure';

    public function __construct(
        private readonly string $connectionString,
        private readonly string $containerName,
        private readonly string $filePath,
    ) {
        Assert::notFalse(base64_decode($connectionString));
    }

    public function getConnectionString(): string
    {
        return $this->connectionString;
    }

    public function getContainerName(): string
    {
        return $this->containerName;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
