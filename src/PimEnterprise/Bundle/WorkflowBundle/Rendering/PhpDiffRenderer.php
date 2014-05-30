<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Rendering;

/**
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PhpDiffRenderer implements RendererInterface
{
    /** @var \Diff_Renderer_Html_Array */
    protected $renderer;

    /**
     * @param \Diff_Renderer_Html_Array $renderer
     * @param DiffFactory               $factory
     */
    public function __construct(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory = null)
    {
        $this->renderer = $renderer;
        $this->factory = $factory ?: new DiffFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function renderDiff($before, $after)
    {
        return $this->factory->create($before, $after)->render($this->renderer);
    }
}
