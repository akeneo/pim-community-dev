<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;

final class AspellDictionaryLocalFilesystem implements AspellDictionaryLocalFilesystemInterface
{
    private FilesystemOperator $fs;

    public function __construct()
    {
        $this->fs = new Filesystem(new LocalFilesystemAdapter($this->getAbsoluteRootPath()));
    }

    public function getFilesystem(): FilesystemOperator
    {
        return $this->fs;
    }

    public function getAbsoluteRootPath(): string
    {
        return sys_get_temp_dir();
    }
}
