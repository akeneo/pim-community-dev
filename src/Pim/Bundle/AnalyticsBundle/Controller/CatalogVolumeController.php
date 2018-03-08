<?php

namespace Pim\Bundle\AnalyticsBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CatalogVolumeController extends Controller
{
    public function __invoke()
    {
        return new JsonResponse($this->get('pim_catalog_volume')());
    }
}
