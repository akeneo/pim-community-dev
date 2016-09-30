<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Datagrid view controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridViewController
{
    /** @var EngineInterface */
    protected $templating;

    /**
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * Display the datagrid view selector
     *
     * @param string $alias Grid alias (eg. product-grid)
     *
     * @return Response|JsonResponse
     */
    public function indexAction($alias)
    {
        return $this->templating->renderResponse('PimDataGridBundle:Datagrid:_views.html.twig', ['alias' => $alias]);
    }
}
