<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\FileSystem;

use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteImageFromStorage
{
    public function __construct(
        private readonly FileSystemProvider $fileSystemProvider,
    ) {

    }

    public function deleteImage(): void
    {
        $imageKey = '5/1/b/e/51be07a8cf706c0d4534e960ba4f1b15cc0f7f81_me_batman.jpg';
        $fileSystem = $this->fileSystemProvider->getFilesystem('categoryStorage');
        $fileSystem->delete($imageKey);
        $toto = '';
    }
}
