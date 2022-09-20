<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Handler;

use Akeneo\Category\Domain\Filesystem\Storage;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StoreUploadedFile
{
    public function __construct(
        private FileStorer $fileStorer,
        private FileInfoRepositoryInterface $fileInfoRepository,
    ) {
    }

    public function __invoke(UploadedFile $uploadedFile): FileInfoInterface
    {
        $hash = sha1_file($uploadedFile->getPathname());
        $originalFilename = $uploadedFile->getClientOriginalName();
        $file = $this->fileInfoRepository->findOneBy([
            'hash' => $hash,
            'originalFilename' => $originalFilename,
            'storage' => Storage::CATEGORY_STORAGE_ALIAS,
        ]);

        if (null === $file) {
            $file = $this->fileStorer->store($uploadedFile, Storage::CATEGORY_STORAGE_ALIAS);
        }

        return $file;
    }
}
