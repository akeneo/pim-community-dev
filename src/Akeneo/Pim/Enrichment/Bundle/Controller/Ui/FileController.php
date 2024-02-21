<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\Ui;

use Akeneo\Pim\Enrichment\Bundle\File\DefaultImageProviderInterface;
use Akeneo\Pim\Enrichment\Bundle\File\FileTypeGuesserInterface;
use Akeneo\Pim\Enrichment\Bundle\File\FileTypes;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Exception\LogicException;
use Liip\ImagineBundle\Imagine\Cache\Helper\PathHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\MimeTypes;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileController
{
    const DEFAULT_IMAGE_KEY = '__default_image__';
    const SVG_MIME_TYPES = ['image/svg', 'image/svg+xml'];

    public function __construct(
        protected ImagineController $imagineController,
        protected FilesystemProvider $filesystemProvider,
        protected FileInfoRepositoryInterface $fileInfoRepository,
        protected FileTypeGuesserInterface $fileTypeGuesser,
        protected DefaultImageProviderInterface $defaultImageProvider,
        protected array $filesystemAliases,
        protected array $supportedImageTypes,
    ) {
    }

    public function showAction(Request $request, string $filename, ?string $filter = null): Response
    {
        $filename = urldecode($filename);
        $fileInfo = $this->fileInfoRepository->findOneByIdentifier($filename);
        if (null === $fileInfo) {
            return $this->renderDefaultImage(FileTypes::MISC, $filter);
        }

        $mimeType = $this->getMimeType($filename);
        $fileType = $this->fileTypeGuesser->guess($mimeType);

        if (self::DEFAULT_IMAGE_KEY === $filename || FileTypes::IMAGE !== $fileType) {
            return $this->renderDefaultImage($fileType, $filter);
        }

        if (in_array($mimeType, self::SVG_MIME_TYPES)) {
            return $this->getFileResponse($filename, 'image/svg+xml');
        }

        try {
            return $this->imagineController->filterAction($request, $filename, $filter);
        } catch (NotFoundHttpException | LogicException | \RuntimeException) {
            return $this->renderDefaultImage(FileTypes::IMAGE, $filter);
        }
    }

    private function getFileResponse(string $filename, string $mimeType): Response
    {
        foreach ($this->filesystemAliases as $alias) {
            $fs = $this->filesystemProvider->getFilesystem($alias);

            $response = new Response($fs->read($filename));
            $response->headers->set('Content-Type', $mimeType);

            return $response;
        }

        throw new NotFoundHttpException(
            sprintf('File with key "%s" could not be found.', $filename)
        );
    }

    /**
     * In case of multiple frontend architecture, the request that ask the cache generation on one frontend and
     * the request that ask the generated media could be on another frontend. This action is a "last chance" to get the
     * media generated in cache and delivered.
     *
     * @param Request $request
     * @param string  $path
     * @param string  $filter
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return RedirectResponse
     */
    public function cacheAction(Request $request, $path, $filter)
    {
        $filename = urldecode($path);

        /** @var FileInfoInterface $fileInfo */
        $fileInfo = $this->fileInfoRepository->findOneByIdentifier($filename);

        if (null === $fileInfo || !$this->isValidImage($fileInfo, $path)) {
            return $this->renderDefaultImage(FileTypes::IMAGE, $filter);
        }

        return $this->imagineController->filterAction($request, $path, $filter);
    }

    /**
     * @param string $filename
     *
     * @throws NotFoundHttpException
     *
     * @return StreamedFileResponse
     */
    public function downloadAction($filename)
    {
        $filename = urldecode($filename);

        foreach ($this->filesystemAliases as $alias) {
            $fs = $this->filesystemProvider->getFilesystem($alias);
            if ($fs->fileExists($filename)) {
                $stream = $fs->readStream($filename);
                $headers = [];

                if (null !== $fileInfo = $this->fileInfoRepository->findOneByIdentifier($filename)) {
                    $headers['Content-Disposition'] = sprintf(
                        'attachment; filename="%s"',
                        $fileInfo->getOriginalFilename()
                    );
                }

                return new StreamedFileResponse($stream, 200, $headers);
            }
        }

        throw new NotFoundHttpException(
            sprintf('File with key "%s" could not be found.', $filename)
        );
    }

    /**
     * Get the default thumbnail from a mime type
     *
     * @param string $mimeType
     *
     * @return RedirectResponse
     */
    public function defaultThumbnailAction($mimeType)
    {
        $fileType = $this->fileTypeGuesser->guess($mimeType);

        return $this->renderDefaultImage($fileType, 'thumbnail');
    }

    /**
     * @param string $fileType
     * @param string $filter
     *
     * @return RedirectResponse
     */
    protected function renderDefaultImage($fileType, $filter)
    {
        $imageUrl = $this->defaultImageProvider->getImageUrl($fileType, $filter);

        return new RedirectResponse($imageUrl, 301);
    }

    /**
     * Returns the Mime type of a file.
     * If the file is linked to a FileInfo, returns its Mime type.
     */
    protected function getMimeType(string $filename): ?string
    {
        $mimeType = null;

        $file = $this->fileInfoRepository->findOneByIdentifier($filename);
        if (null !== $file) {
            $mimeType = $file->getMimeType();
        }
        if (null === $mimeType && file_exists($filename)) {
            $mimeType = (new MimeTypes())->guessMimeType($filename);
        }

        return $mimeType;
    }

    protected function isValidImage(FileInfoInterface $fileInfo, string $path): bool
    {
        $supportedMimeTypes = \array_merge(...\array_values($this->supportedImageTypes));

        $guessedExtension = strtolower($fileInfo->getExtension() ?? '');
        $originalExtension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $guessedMimeType = strtolower($fileInfo->getMimeType() ?? '');
        $detector = new FinfoMimeTypeDetector();
        $originalMimeType = strtolower($detector->detectMimeTypeFromPath($path));

        if (!array_key_exists($originalExtension, $this->supportedImageTypes)) {
            return false;
        }

        if (!in_array($originalMimeType, $supportedMimeTypes, true)) {
            return false;
        }

        if ($guessedExtension !== $originalExtension) {
            return false;
        }

        if ($guessedMimeType !== $originalMimeType) {
            return false;
        }

        if (!in_array($originalMimeType, $this->supportedImageTypes[$originalExtension], true)) {
            return false;
        }

        return true;
    }
}
