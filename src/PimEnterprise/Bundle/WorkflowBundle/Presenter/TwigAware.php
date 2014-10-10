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

/**
 * Provides twig capabilities default implementation
 *
 * @author Gildas Quemener <gildas@akeneo.com>
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
