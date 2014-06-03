<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Rendering;

/**
 * A value diff renderer
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface RendererInterface
{
    /**
     * Render differences between two variables
     *
     * @param mixed $before
     * @param mixed $after
     *
     * @return string
     */
    public function renderDiff($before, $after);
}
