<?php

namespace Akeneo\Category\Infrastructure\Controller\ExternalApi;

use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class DownloadCategoryMediaFileController
{
    private const CATEGORY_STORAGE_ALIAS = 'categoryStorage';

    public function __construct(
        private readonly SecurityFacade $securityFacade,
        private readonly ApiResourceRepositoryInterface $mediaRepository,
        private readonly FilesystemProvider $filesystemProvider,
        private readonly FileFetcherInterface $fileFetcher,
    ) {
    }

    public function __invoke(string $code)
    {
        if ($this->securityFacade->isGranted('pim_api_category_list') === false) {
            throw new AccessDeniedException();
        }

        $filename = urldecode($code);

        $fileInfo = $this->mediaRepository->findOneBy([
            'key' => $filename,
            'storage' => self::CATEGORY_STORAGE_ALIAS,
        ]);

        if (null === $fileInfo) {
            throw new NotFoundHttpException(sprintf('Media file "%s" does not exist.', $filename));
        }

        $fs = $this->filesystemProvider->getFilesystem(self::CATEGORY_STORAGE_ALIAS);
        $options = [
            'headers' => [
                'Content-Type' => $fileInfo->getMimeType(),
                'Content-Disposition' => sprintf('attachment; filename="%s"', $fileInfo->getOriginalFilename()),
            ],
        ];

        try {
            return $this->fileFetcher->fetch($fs, $filename, $options);
        } catch (FileNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Media file "%s" is not present on the filesystem.', $filename), $e);
        }
    }
}
