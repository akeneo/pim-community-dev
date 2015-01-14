<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

/**
 * Provides renderer capabilities default implementation
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
trait RendererAware
{
    /** @var RendererInterface */
    protected $renderer;

    /**
     * Set the renderer
     *
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }
}
