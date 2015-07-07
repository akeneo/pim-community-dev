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
