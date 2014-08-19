<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

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
     * @param \Twig_Environment $twig
     */
    public function setTwig(\Twig_Environment $twig);
}
