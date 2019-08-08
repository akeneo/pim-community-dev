<?php

namespace Oro\Bundle\DataGridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GridController extends Controller
{
    /**
     * @param string $gridName
     *
     * @return Response
     */
    public function getAction($gridName)
    {
        $grid = $this->get('oro_datagrid.datagrid.manager')->getDatagrid($gridName);
        $result = $grid->getData();

        return new JsonResponse($result->toArray());
    }
}
