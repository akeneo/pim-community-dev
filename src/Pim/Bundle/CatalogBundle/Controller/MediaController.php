<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Symfony\Component\HttpFoundation\Response;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Media controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaController extends AbstractDoctrineController
{
    /** @var \Imagine\Image\ImagineInterface */
    protected $imagine;

    /** @var \Liip\ImagineBundle\Imagine\Filter\FilterManager */
    protected $filterManager;

    /** @var \Liip\ImagineBundle\Imagine\Cache\CacheManager */
    protected $cacheManager;

    /**
     * Constructor
     *
     * @param ImagineInterface $imagine
     * @param FilterManager    $filterManager
     * @param CacheManager     $cacheManager
     */
    public function __construct(
        ImagineInterface $imagine,
        FilterManager $filterManager,
        CacheManager $cacheManager
    ) {
        $this->imagine       = $imagine;
        $this->filterManager = $filterManager;
        $this->cacheManager  = $cacheManager;
    }

    /**
     * @param Request $request
     * @param string  $filename
     *
     * @return Response
     */
    public function showAction(Request $request, $filename)
    {
        $media = $this->getRepository('OroFlexibleEntityBundle:Media')->findOneBy(
            array(
                'filename' => $filename
            )
        );

        if (!$media) {
            throw $this->createNotFoundException(sprintf('Media "%s" not found', $filename));
        }

        $path     = $media->getFilePath();
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
