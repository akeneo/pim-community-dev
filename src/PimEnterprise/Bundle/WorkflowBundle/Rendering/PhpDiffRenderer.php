<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Rendering;

/**
 * Diff renderer based on the PHP-Diff library
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class PhpDiffRenderer implements RendererInterface
{
    /** @var \Diff_Renderer_Html_Array */
    protected $baseRenderer;

    /** @var \Diff_Renderer_Html_Array */
    protected $changedRenderer;

    /** @var DiffFactory */
    protected $factory;

    /**
     * @param \Diff_Renderer_Html_Array $renderer
     * @param \Diff_Renderer_Html_Array $renderer
     * @param DiffFactory               $factory
     */
    public function __construct(
        \Diff_Renderer_Html_Array $baseRenderer,
        \Diff_Renderer_Html_Array $changedRenderer,
        DiffFactory $factory
    ) {
        $this->baseRenderer = $baseRenderer;
        $this->changedRenderer = $changedRenderer;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function renderOriginalDiff($before, $after)
    {
        return $this->factory->create($before, $after)->render($this->baseRenderer);
    }

    /**
     * {@inheritdoc}
     */
    public function renderNewDiff($before, $after)
    {
        return $this->factory->create($before, $after)->render($this->changedRenderer);
    }
}
