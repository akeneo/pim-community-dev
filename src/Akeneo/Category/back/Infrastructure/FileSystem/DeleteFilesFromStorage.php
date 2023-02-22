<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\FileSystem;

use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteFilesFromStorage
{
    public function __construct(
        private readonly FileSystemProvider $fileSystemProvider,
    ) {
    }

    public function delete(array $fileKeys): void
    {
//        $fileKeys = ['0/c/e/3/0ce3f361699f459316c2a120c67057e76a3dc67b_Screenshot_from_2023_01_18_10_57_52.png'];
        $fileSystem = $this->fileSystemProvider->getFilesystem('categoryStorage');
        foreach ($fileKeys as $fileKey){
            $fileSystem->delete($fileKey);
        }
        $toto = 'http://localhost:8080/category/template/yolo';
    }
}
