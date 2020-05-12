<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

final class AspellDictionaryLocalFilesystem implements AspellDictionaryLocalFilesystemInterface
{
    /** @var Filesystem */
    private $fs;

    public function __construct()
    {
        $this->fs = new Filesystem(new Local($this->getAbsoluteRootPath()));
    }

    public function getFilesystem(): FilesystemInterface
    {
        return $this->fs;
    }

    public function getAbsoluteRootPath(): string
    {
        return sys_get_temp_dir();
    }
}
