<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use League\Flysystem\FilesystemInterface;

interface AspellDictionaryLocalFilesystemInterface
{
    public function getFilesystem(): FilesystemInterface;

    public function getAbsoluteRootPath(): string;
}
