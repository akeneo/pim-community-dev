<?php

namespace Oro\Bundle\GridBundle\Renderer;

use Symfony\Bundle\FrameworkBundle\Templating\PhpEngine;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\GridBundle\Datagrid\DatagridView;

class GridRenderer
{
    /**
     * @var PhpEngine
     */
    private $engine;

    /**
     * @var string
     */
    private $listJsonPhpTemplateName;

    /**
     * @param PhpEngine $engine
     * @param $listJsonPhpTemplateName
     */
    public function __construct(PhpEngine $engine, $listJsonPhpTemplateName)
    {
        $this->engine = $engine;
        $this->listJsonPhpTemplateName = $listJsonPhpTemplateName;
    }

    /**
     * Renders datagrid results JSON response
     *
     * @param DatagridView $datagridView
     * @param Response $response
     * @return Response
     */
    public function renderResultsJsonResponse(DatagridView $datagridView, Response $response = null)
    {
        return $this->engine->renderResponse(
            $this->listJsonPhpTemplateName,
            array('datagrid' => $datagridView),
            $response
        );
    }

    /**
     * Get datagrid results as JSON string
     *
     * @param DatagridView $datagridView
     * @return string
     */
    public function getResultsJson(DatagridView $datagridView)
    {
        return $this->engine->render($this->listJsonPhpTemplateName, array('datagrid' => $datagridView));
    }
}
