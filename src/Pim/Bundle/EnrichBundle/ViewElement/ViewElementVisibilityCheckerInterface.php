<?php

namespace Pim\Bundle\EnrichBundle\ViewElement;

/**
 * Interface to determine if a view element is visible or not
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ViewElementVisibilityCheckerInterface
{
    /**
     * Indicate whether or not the view element should be displayed
     *
     * @param array $context The twig context
     *
     * @return boolean
     */
    public function isVisible(array $context = []);
}
