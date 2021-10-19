<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFilesystemProviderStub extends FilesystemProvider
{
    private FilesystemOperator $fileSystem;

    public function __construct()
    {
        parent::__construct([]);
        $this->fileSystem = new Filesystem(new InMemoryFilesystemAdapter());
    }

    public function getFileSystem($name): FilesystemOperator
    {
        return $this->fileSystem;
    }
}
