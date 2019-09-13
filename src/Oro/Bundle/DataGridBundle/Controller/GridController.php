<?php

namespace Oro\Bundle\DataGridBundle\Controller;

use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GridController extends Controller
{
    /** @var ManagerInterface */
    private $manager;

    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $gridName
     *
     * @return JsonResponse
     */
    public function get($gridName)
    {
        $grid = $this->manager->getDatagrid($gridName);
        $result = $grid->getData();

        return new JsonResponse($result->toArray());
    }
}
