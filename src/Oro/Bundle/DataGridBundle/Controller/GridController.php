<?php

namespace Oro\Bundle\DataGridBundle\Controller;

use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class GridController
{
    private ManagerInterface $manager;

    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function get(string $gridName): JsonResponse
    {
        $grid = $this->manager->getDatagrid($gridName);
        $result = $grid->getData();

        return new JsonResponse($result->toArray());
    }
}
