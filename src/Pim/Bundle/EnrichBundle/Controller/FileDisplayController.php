<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\MountManager;
use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileDisplayController extends Controller
{
    /** @staticvar string */
    const FALLBACK_IMAGE_PATH = 'Resources/public/img/img_generic.png';

    /** @var ImagineController */
    protected $imagineController;

    /** @var CacheManager */
    protected $cacheManager;

    /** @var MountManager */
    protected $mountManager;

    /** @var FilterManager */
    protected $filterManager;

    /** @var string */
    protected $filesystemName;

    public function __construct(
        ImagineController $imagineController,
        CacheManager $cacheManager,
        MountManager $mountManager,
        FilterManager $filterManager,
        $filesystemName
    ) {
        $this->imagineController = $imagineController;
        $this->cacheManager      = $cacheManager;
        $this->mountManager      = $mountManager;
        $this->filterManager     = $filterManager;
        $this->filesystemName    = $filesystemName;
    }

    /**
     * @param Request $request
     * @param string  $filename
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function showAction(Request $request, $filename)
    {
        $filepath = $filename;
        $filter = $request->query->get('filter');

        try {
            if (null !== $filter) {
                $imageResponse = $this->imagineController->filterAction($request, $filepath, $filter);
            } else {
                // TODO: Change the way we read the distant files
                $filesystem = $this->getFilesystem();
                $content = $filesystem->read($filename);
                $mimeType = $filesystem->getMimetype($filename);
                $imageResponse = new Response($content);

                if (null !== $mimeType) {
                    $imageResponse->headers->set('Content-Type', $mimeType);
                }
            }
        } catch (FileNotFoundException $e) {
            $imageResponse = $this->getFallbackImageResponse($filter);
        }

        return $imageResponse;
    }

    /**
     * @return \League\Flysystem\FilesystemInterface
     */
    protected function getFilesystem()
    {
        return $this->mountManager->getFilesystem($this->filesystemName);
    }

    /**
     * @param string $filter
     *
     * @return Response
     */
    protected function getFallbackImageResponse($filter = null)
    {
        $path = realpath(__DIR__ . '/../' . self::FALLBACK_IMAGE_PATH);
        $content = file_get_contents($path);

        $binary = new Binary($content, 'image/png');
        if (null !== $filter) {
            $binary = $this->filterManager->applyFilter($binary, $filter);
        }

        $imageResponse = new Response($binary->getContent());
        $imageResponse->headers->set('Content-Type', 'image/png');

        return $imageResponse;
    }
}
