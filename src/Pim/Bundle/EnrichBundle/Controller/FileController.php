<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Component\FileStorage\StreamedFileResponse;
use League\Flysystem\MountManager;
use Liip\ImagineBundle\Controller\ImagineController;
use Pim\Bundle\EnrichBundle\File\DefaultImageProviderInterface;
use Pim\Bundle\EnrichBundle\File\FileTypeGuesserInterface;
use Pim\Bundle\EnrichBundle\File\FileTypes;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileController extends Controller
{
    const DEFAULT_IMAGE_KEY = '__default_image__';

    /** @var ImagineController */
    protected $imagineController;

    /** @var MountManager */
    protected $mountManager;

    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    /** @var FileTypeGuesserInterface */
    protected $fileTypeGuesser;

    /** @var DefaultImageProviderInterface */
    protected $defaultImageProvider;

    /** @var array */
    protected $filesystemAliases;

    /**
     * @param ImagineController             $imagineController
     * @param MountManager                  $mountManager
     * @param FileInfoRepositoryInterface   $fileInfoRepository
     * @param FileTypeGuesserInterface      $fileTypeGuesser
     * @param DefaultImageProviderInterface $defaultImageProvider
     * @param array                         $filesystemAliases
     */
    public function __construct(
        ImagineController $imagineController,
        MountManager $mountManager,
        FileInfoRepositoryInterface $fileInfoRepository,
        FileTypeGuesserInterface $fileTypeGuesser,
        DefaultImageProviderInterface $defaultImageProvider,
        array $filesystemAliases
    ) {
        $this->imagineController    = $imagineController;
        $this->mountManager         = $mountManager;
        $this->fileInfoRepository   = $fileInfoRepository;
        $this->fileTypeGuesser      = $fileTypeGuesser;
        $this->defaultImageProvider = $defaultImageProvider;
        $this->filesystemAliases    = $filesystemAliases;
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

        if (self::DEFAULT_IMAGE_KEY === $filename) {
            return $this->renderDefaultImage(FileTypes::MISC, $filter);
        }

        $file = $this->fileInfoRepository->findOneByIdentifier($filename);
        if (null !== $file) {
            if (FileTypes::IMAGE === $fileType = $this->fileTypeGuesser->guess($file->getMimeType())) {
                try {
                    return $this->imagineController->filterAction($request, $filename, $filter);
                } catch (NotFoundHttpException $e) {
                    return $this->renderDefaultImage(FileTypes::IMAGE, $filter);
                }
            }

            return $this->renderDefaultImage($fileType, $filter);
        }

        return $this->renderDefaultImage(FileTypes::MISC, $filter);
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
            $fs = $this->mountManager->getFilesystem($alias);
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

        throw $this->createNotFoundException(
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
}
