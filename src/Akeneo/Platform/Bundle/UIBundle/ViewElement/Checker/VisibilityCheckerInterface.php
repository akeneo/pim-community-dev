<?php

namespace Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker;

/**
 * Interface to determine if a view element is visible or not.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VisibilityCheckerInterface
{
    /**
     * Return whether or not the view element should be displayed in the given context
     *
     * @param array $config  The visibility checker configuration
     * @param array $context The twig context
     *
     * @return bool
     */
    public function isVisible(array $config = [], array $context = []);
}
