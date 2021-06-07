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
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Memory\MemoryAdapter;
use League\Flysystem\MountManager;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFilesystemProviderStub extends FilesystemProvider
{
    private FilesystemInterface $fileSystem;

    public function __construct(MountManager $mountManager)
    {
        parent::__construct($mountManager);

        $this->fileSystem = new Filesystem(new MemoryAdapter());
    }

    public function getFileSystem($name): FilesystemInterface
    {
        return $this->fileSystem;
    }
}
