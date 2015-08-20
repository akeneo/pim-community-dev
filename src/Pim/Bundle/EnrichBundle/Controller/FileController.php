<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\FileStorage\Repository\FileRepositoryInterface;
use Akeneo\Component\FileStorage\StreamedFileResponse;
use League\Flysystem\MountManager;
use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
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
    const DEFAULT_IMAGE_KEY = '__default__image__';

    /** @var ImagineController */
    protected $imagineController;

    /** @var MountManager */
    protected $mountManager;

    /** @var FileRepositoryInterface */
    protected $fileRepository;

    /** @var FileTypeGuesserInterface */
    protected $fileTypeGuesser;

    /** @var DefaultImageProviderInterface */
    protected $defaultImageProvider;

    /** @var array */
    protected $filesystemAliases;

    /**
     * @param ImagineController             $imagineController
     * @param MountManager                  $mountManager
     * @param FileRepositoryInterface       $fileRepository
     * @param FileTypeGuesserInterface      $fileTypeGuesser
     * @param DefaultImageProviderInterface $defaultImageProvider
     * @param array                         $filesystemAliases
     */
    public function __construct(
        ImagineController $imagineController,
        MountManager $mountManager,
        FileRepositoryInterface $fileRepository,
        FileTypeGuesserInterface $fileTypeGuesser,
        DefaultImageProviderInterface $defaultImageProvider,
        array $filesystemAliases
    ) {
        $this->imagineController    = $imagineController;
        $this->mountManager         = $mountManager;
        $this->fileRepository       = $fileRepository;
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
            return $this->renderDefaultImage(FileTypes::UNKNOWN, $filter);
        }

        $file = $this->fileRepository->findOneByIdentifier($filename);
        if (null !== $file &&
            FileTypes::IMAGE !== $fileType = $this->fileTypeGuesser->guess($file->getMimeType())
        ) {
            return $this->renderDefaultImage($fileType, $filter);
        }

        try {
            return $this->imagineController->filterAction($request, $filename, $filter);
        } catch (NotFoundHttpException $e) {
            return $this->renderDefaultImage(FileTypes::IMAGE, $filter);
        }
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

                return new StreamedFileResponse($stream);
            }
        }

        throw $this->createNotFoundException(
            sprintf('File with key "%s" could not be found.', $filename)
        );
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
