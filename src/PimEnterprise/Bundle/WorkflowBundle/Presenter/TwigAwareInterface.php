<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

/**
 * Provides twig capabilities
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface TwigAwareInterface
{
    /**
     * Set the twig environment
     *
     * @param RendererInterface $renderer
     */
    public function setTwig(\Twig_Environment $twig);
}
