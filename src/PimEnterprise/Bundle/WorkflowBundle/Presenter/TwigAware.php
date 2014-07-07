<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

/**
 * Provides twig capabilities default implementation
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
trait TwigAware
{
    /** @var \Twig_Environment */
    protected $twig;

    /**
     * Set the twig environment
     *
     * @param \Twig_Environment $twig
     */
    public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }
}
