<?php

namespace Oro\Bundle\GridBundle\Twig;

use Oro\Bundle\GridBundle\Datagrid\DatagridView;
use Oro\Bundle\GridBundle\Renderer\GridRenderer;

class GridRendererExtension extends \Twig_Extension
{
    const NAME = 'oro_grid_renderer';

    /**
     * @var GridRenderer
     */
    private $renderer;

    /**
     * @param GridRenderer $renderer
     */
    public function __construct(GridRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'oro_grid_render_results_json' => new \Twig_Function_Method(
                $this,
                'renderResultsJson',
                array('is_safe' => array('html'))
            ),
        );
    }

    /**
     * @param DatagridView $datagridView
     * @return string
     */
    public function renderResultsJson(DatagridView $datagridView)
    {
        return $this->renderer->getResultsJson($datagridView);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
