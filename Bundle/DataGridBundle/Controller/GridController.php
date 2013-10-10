<?php

namespace Oro\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class GridController extends Controller
{
    /**
     * @Route("/{gridName}", name="oro_datagrid_index")
     *
     * @param string $gridName
     *
     * @return Response
     */
    public function massActionAction($gridName)
    {
        /**
         * @TODO add ACL check here
         */
        $request = $this->getRequest();

        $grid =  $this->get('oro_grid.datagrid.manager')->getDatagrid($gridName);
        $result = $grid->getData();
        return new JsonResponse($result);
    }
}
