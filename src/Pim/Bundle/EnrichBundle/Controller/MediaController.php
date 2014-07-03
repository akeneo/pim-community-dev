<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Gaufrette\Filesystem;

/**
 * Media controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaController
{
    /** @var ImagineInterface */
    protected $imagine;

    /** @var FilterManager */
    protected $filterManager;

    /** @var CacheManager */
    protected $cacheManager;

    /** @var Filesystem */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param ImagineInterface $imagine
     * @param FilterManager    $filterManager
     * @param CacheManager     $cacheManager
     * @param Filesystem       $filesystem
     */
    public function __construct(
        ImagineInterface $imagine,
        FilterManager $filterManager,
        CacheManager $cacheManager,
        Filesystem $filesystem
    ) {
        $this->imagine = $imagine;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
        $this->filesystem = $filesystem;
    }

    /**
     * @param Request $request
     * @param string  $filename
     *
     * @return Response
     * @throws NotFoundHttpException If media is not found
     */
    public function showAction(Request $request, $filename)
    {
        if (!$this->filesystem->has($filename)) {
            throw new NotFoundHttpException(sprintf('Media "%s" not found', $filename));
        }

        $response = new Response($content = $this->filesystem->read($filename));

        $mime = $this->filesystem->mimeType($filename);
        if (($filter = $request->query->get('filter')) && null !== $mime && 0 === strpos($mime, 'image')) {
            try {
                $cachePath = $this->cacheManager->resolve($request, $filename, $filter);

                if ($cachePath instanceof Response) {
                    $response = $cachePath;
                } else {
                    $image = $this->imagine->load($content);
                    $response = $this->filterManager->get($request, $filter, $image, $filename);
                    $response = $this->cacheManager->store($response, $cachePath, $filter);
                }
            } catch (\RuntimeException $e) {
                if (0 === strpos($e->getMessage(), 'Filter not defined')) {
                    throw new HttpException(404, sprintf('The filter "%s" cannot be found', $filter), $e);
                }
                throw $e;
            }
        }

        if ($mime) {
            $response->headers->set('Content-Type', $mime);
        }

        return $response;
    }
}
