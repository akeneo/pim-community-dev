<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Infrastructure\Controller\ExternalApi;

use Akeneo\Platform\Syndication\Domain\Query\MediaFile\FindMediaFileInterface;
use Akeneo\Platform\Syndication\Domain\Query\MediaFile\MediaFileNotFoundException;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DownloadMediaFileAction
{
    public function __construct(
        private FindMediaFileInterface $findMediaFile,
        private FilesystemProvider $filesystemProvider,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $fileCode = $request->get('fileCode');

        try {
            $fileInfo = $this->findMediaFile->getByIdentifier($fileCode);
        } catch (MediaFileNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }

        $filesystem = $this->filesystemProvider->getFilesystem($fileInfo->getStorage());
        if (!$filesystem->fileExists($fileCode)) {
            throw new NotFoundHttpException(sprintf('Media file "%s" is not present on the filesystem.', $fileCode));
        }

        $fileStream = $filesystem->readStream($fileCode);
        /** @phpstan-ignore-next-line */
        if (false === $fileStream) {
            throw new UnprocessableEntityHttpException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileCode)
            );
        }

        $headers = [
            'Content-Type' => $fileInfo->getMimeType(),
        ];

        return new StreamedFileResponse($fileStream, 200, $headers);
    }
}
