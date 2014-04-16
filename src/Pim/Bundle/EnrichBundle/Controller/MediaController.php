<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Common\Persistence\ObjectRepository;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

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

    /** @var ObjectRepository */
    protected $mediaRepository;

    /**
     * Constructor
     *
     * @param ImagineInterface $imagine
     * @param FilterManager    $filterManager
     * @param CacheManager     $cacheManager
     * @param ObjectRepository $mediaRepository
     */
    public function __construct(
        ImagineInterface $imagine,
        FilterManager $filterManager,
        CacheManager $cacheManager,
        ObjectRepository $mediaRepository
    ) {
        $this->imagine         = $imagine;
        $this->filterManager   = $filterManager;
        $this->cacheManager    = $cacheManager;
        $this->mediaRepository = $mediaRepository;
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
        $media = $this->mediaRepository->findOneBy(['filename' => $filename]);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('Media "%s" not found', $filename));
        }

        $path = $media->getFilePath();

        if (!file_exists($path)) {
            return new Response('', 404);
        }

        $response = new Response(file_get_contents($path));

        if (($filter = $request->query->get('filter')) && 0 === strpos($media->getMimeType(), 'image')) {
            try {
                $cachePath = $this->cacheManager->resolve($request, $media->getFilename(), $filter);

                if ($cachePath instanceof Response) {
                    $response = $cachePath;
                } else {
                    $image = $this->imagine->open($path);
                    $response = $this->filterManager->get($request, $filter, $image, $path);
                    $response = $this->cacheManager->store($response, $cachePath, $filter);
                }
            } catch (\RuntimeException $e) {
                if (0 === strpos($e->getMessage(), 'Filter not defined')) {
                    throw new HttpException(404, sprintf('The filter "%s" cannot be found', $filter), $e);
                }
                throw $e;
            }
        }

        $response->headers->set('Content-Type', $media->getMimeType());

        return $response;
    }
}
