<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use League\Flysystem\MountManager;
use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
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
    /** @var ImagineController */
    protected $imagineController;

    /** @var CacheManager */
    protected $cacheManager;

    /** @var MountManager */
    protected $mountManager;

    /** @var string */
    protected $filesystemName;

    public function __construct(
        ImagineController $imagineController,
        CacheManager $cacheManager,
        MountManager $mountManager,
        $filesystemName
    ) {
        $this->imagineController = $imagineController;
        $this->cacheManager      = $cacheManager;
        $this->mountManager      = $mountManager;
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
        // TODO: Ensure we receive the filepath and not only the filename to create
        // a cache image with the same path
        $filepath = $filename;
        $filter = $request->query->get('filter');

        if (null !== $filter) {
            $imageResponse = $this->imagineController->filterAction($request, $filepath, $filter);
        } else {
            $filesystem = $this->getFilesystem();
            $content = $filesystem->read($filename);
            $mimeType = $filesystem->getMimetype($filename);

            $imageResponse = new Response($content);

            if (null !== $mimeType) {
                $imageResponse->headers->set('Content-Type', $mimeType);
            }
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
}
