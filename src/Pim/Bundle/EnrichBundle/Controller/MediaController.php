<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Doctrine\Common\Persistence\ObjectRepository;

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

    /** @var ObjectRepository */
    protected $mediaRepository;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param RegistryInterface        $doctrine
     * @param ImagineInterface         $imagine
     * @param FilterManager            $filterManager
     * @param CacheManager             $cacheManager
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        ImagineInterface $imagine,
        FilterManager $filterManager,
        CacheManager $cacheManager,
        ObjectRepository $mediaRepository
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

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
     */
    public function showAction(Request $request, $filename)
    {
        $media = $this->mediaRepository->findOneBy(['filename' => $filename]);

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
