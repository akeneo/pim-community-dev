<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\FileStorage\StreamedFileResponse;
use League\Flysystem\MountManager;
use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
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
    /** @var ImagineController */
    protected $imagineController;

    /** @var CacheManager */
    protected $cacheManager;

    /** @var FilterManager */
    protected $filterManager;

    /** @var MountManager */
    protected $mountManager;

    /** @var string */
    protected $filesystemAliases;

    /**
     * @param ImagineController $imagineController
     * @param CacheManager      $cacheManager
     * @param FilterManager     $filterManager
     * @param MountManager      $mountManager
     * @param array             $filesystemAliases
     */
    public function __construct(
        ImagineController $imagineController,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        MountManager $mountManager,
        array $filesystemAliases
    ) {
        $this->imagineController = $imagineController;
        $this->cacheManager      = $cacheManager;
        $this->filterManager     = $filterManager;
        $this->mountManager      = $mountManager;
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

        try {
            return $this->imagineController->filterAction($request, $filename, $filter);
        } catch (NotFoundHttpException $e) {
            return new RedirectResponse($request->getUriForPath('/bundles/pimenrich/img/img_generic.png'));
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
}
