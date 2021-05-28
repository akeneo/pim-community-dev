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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\MediaFile;

use Akeneo\AssetManager\Domain\Repository\MediaFileNotFoundException;
use Akeneo\AssetManager\Domain\Repository\MediaFileRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DownloadMediaFileAction
{
    private MediaFileRepositoryInterface $mediaFileRepository;

    private FilesystemProvider $filesystemProvider;

    public function __construct(
        MediaFileRepositoryInterface $mediaFileRepository,
        FilesystemProvider $filesystemProvider
    ) {
        $this->mediaFileRepository = $mediaFileRepository;
        $this->filesystemProvider = $filesystemProvider;
    }

    public function __invoke(string $fileCode): Response
    {
        $fileCode = urldecode($fileCode);

        try {
            $fileInfo = $this->mediaFileRepository->getByIdentifier($fileCode);
        } catch (MediaFileNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }

        $filesystem = $this->filesystemProvider->getFilesystem(Storage::FILE_STORAGE_ALIAS);
        if (!$filesystem->has($fileCode)) {
            throw new NotFoundHttpException(sprintf('Media file "%s" is not present on the filesystem.', $fileCode));
        }

        $fileStream = $filesystem->readStream($fileCode);
        if (false === $fileStream) {
            throw new UnprocessableEntityHttpException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileCode)
            );
        }

        $headers = [
            'Content-Type'        => $fileInfo->getMimeType(),
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileInfo->getOriginalFilename())
        ];

        return new StreamedFileResponse($fileStream, 200, $headers);
    }
}
