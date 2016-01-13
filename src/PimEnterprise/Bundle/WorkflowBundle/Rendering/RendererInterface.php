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
 * A value diff renderer
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
interface RendererInterface
{
    /**
     * Render differences between two variables (original representation)
     *
     * @param mixed $before
     * @param mixed $after
     *
     * @return string
     */
    public function renderOriginalDiff($before, $after);

    /**
     * Render differences between two variables (new representation)
     *
     * @param mixed $before
     * @param mixed $after
     *
     * @return string
     */
    public function renderNewDiff($before, $after);
}
