<?php

namespace Pim\Bundle\EnrichBundle\ViewElement\Checker;

/**
 * Interface to determine if a tab is visible or not.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VisibilityCheckerInterface
{
    /**
     * Return whether or not the tab should be displayed
     *
     * @param array $context Ths twig context
     *
     * @return boolean
     */
    public function isVisible(array $context = []);

    /**
     * Set the checker context
     * @param array $context
     */
    public function setContext(array $context = []);
}
