<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageDisplayController extends Controller
{
    /** @var ImagineController */
    protected $imagineController;

    /** @var CacheManager */
    protected $cacheManager;

    public function __construct(ImagineController $imagineController, CacheManager $cacheManager)
    {
        $this->imagineController = $imagineController;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param Request $request
     * @param string  $filename
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function showAction(Request $request, $filename)
    {
        $filter = $request->query->get('filter');

        // TODO: Ensure we receive the filepath and not only the filename to create
        // a cache image with the same path
        $filepath = $filename;

        $imageManagerResponse = $this->imagineController->filterAction(
            $request,
            $filepath,
            $filter
        );

        return $imageManagerResponse;
    }
}
