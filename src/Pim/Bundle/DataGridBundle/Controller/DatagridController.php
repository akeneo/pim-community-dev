<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Datagrid controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridController
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * Constructor
     *
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * Load a datagrid
     *
     * @param Request $request
     * @param string  $alias
     *
     * @return JsonResponse
     */
    public function loadAction(Request $request, $alias)
    {
        return $this->templating->renderResponse(
            'PimDataGridBundle:Datagrid:load.json.twig',
            [
                'alias'  => $alias,
                'params' => $request->get('params', [])
            ],
            new JsonResponse()
        );
    }


    /**
     * Loads the metadata of the grid
     * 
     * @param Request $request
     * @param string  $alias
     *
     * @return JsonResponse
     */
    public function loadMetaDataAction(Request $request, $alias)
    {
        return $this->templating->renderResponse(
            'PimDataGridBundle:Datagrid:loadMetaData.json.twig',
            [
                'alias'  => $alias,
                'params' => $request->get('params', [])
            ],
            new JsonResponse()
        );
 
    }
}
