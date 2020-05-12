<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell;

use League\Flysystem\FilesystemInterface;

interface AspellDictionaryLocalFilesystemInterface
{
    public function getFilesystem(): FilesystemInterface;

    public function getAbsoluteRootPath(): string;
}
