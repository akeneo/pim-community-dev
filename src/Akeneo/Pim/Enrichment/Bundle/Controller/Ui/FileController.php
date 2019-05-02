<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\Ui;

use Akeneo\Pim\Enrichment\Bundle\File\DefaultImageProviderInterface;
use Akeneo\Pim\Enrichment\Bundle\File\FileTypeGuesserInterface;
use Akeneo\Pim\Enrichment\Bundle\File\FileTypes;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Liip\ImagineBundle\Controller\ImagineController;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileController
{
    const DEFAULT_IMAGE_KEY = '__default_image__';

    /** @var ImagineController */
    protected $imagineController;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    /** @var FileTypeGuesserInterface */
    protected $fileTypeGuesser;

    /** @var DefaultImageProviderInterface */
    protected $defaultImageProvider;

    /** @var array */
    protected $filesystemAliases;

    public function __construct(
        ImagineController $imagineController,
        FilesystemProvider $filesystemProvider,
        FileInfoRepositoryInterface $fileInfoRepository,
        FileTypeGuesserInterface $fileTypeGuesser,
        DefaultImageProviderInterface $defaultImageProvider,
        array $filesystemAliases
    ) {
        $this->imagineController = $imagineController;
        $this->filesystemProvider = $filesystemProvider;
        $this->fileInfoRepository = $fileInfoRepository;
        $this->fileTypeGuesser = $fileTypeGuesser;
        $this->defaultImageProvider = $defaultImageProvider;
        $this->filesystemAliases = $filesystemAliases;
    }

    /**
     * @param Request $request
     * @param string  $filename
     * @param string  $filter
     *
     * @return RedirectResponse
     */
    public function showAction(Request $request, $filename, $filter = null)
    {
        $filename = urldecode($filename);

        $result = $this->renderDefaultImage(FileTypes::MISC, $filter);

        if (self::DEFAULT_IMAGE_KEY !== $filename) {
            $fileType = $this->fileTypeGuesser->guess($this->getMimeType($filename));

            $result = $this->renderDefaultImage($fileType, $filter);
            if (FileTypes::IMAGE === $fileType) {
                try {
                    $result = $this->imagineController->filterAction($request, $filename, $filter);
                } catch (NotFoundHttpException $exception) {
                    $result = $this->renderDefaultImage(FileTypes::IMAGE, $filter);
                }
            }
        }

        return $result;
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
            if ($fs->has($filename)) {
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
            $mimeType = MimeTypeGuesser::getInstance()->guess($filename);
        }

        foreach ($this->filesystemAliases as $alias) {
            $filesystem = $this->filesystemProvider->getFilesystem($alias);
            if ($filesystem->has($filename)) {
                $metadata = $filesystem->getMetadata($filename);
                if (isset($metadata['mimetype'])) {
                    return $metadata['mimetype'];
                }
            }
        }

        return $mimeType;
    }
}
