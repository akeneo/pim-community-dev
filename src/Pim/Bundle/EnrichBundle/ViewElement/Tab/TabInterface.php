<?php

namespace Pim\Bundle\EnrichBundle\ViewElement\Tab;

use Pim\Bundle\EnrichBundle\ViewElement\ViewElementInterface;

/**
 * Tab interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TabInterface extends ViewElementInterface
{
    /**
     * Get the tab title (which will be displayed in the tab)
     *
     * @param array $context The twig context
     *
     * @return string
     */
    public function getTitle(array $context = []);

    /**
     * Return whether or not the tab should be displayed
     *
     * @param array $context Ths twig context
     *
     * @return boolean
     */
    public function isVisible(array $context = []);
}
