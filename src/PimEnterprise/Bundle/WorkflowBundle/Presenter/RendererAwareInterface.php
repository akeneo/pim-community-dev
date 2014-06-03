<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

/**
 * Provides renderer capabilities
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface RendererAwareInterface
{
    /**
     * Set the renderer
     *
     * @param RendererInterface $renderer
     */
    public function setRenderer(RendererInterface $renderer);
}
