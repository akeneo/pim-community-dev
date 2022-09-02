<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck;

use League\Flysystem\FilesystemOperator;

interface AspellDictionaryLocalFilesystemInterface
{
    public function getFilesystem(): FilesystemOperator;

    public function getAbsoluteRootPath(): string;
}
