<?php

namespace Pim\Bundle\EnrichBundle\View;

/**
 * View Interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ViewInterface
{
    /**
     * Get the tab content (which will be displayed in the tab pane)
     * @param array $context The twig context
     *
     * @return string
     */
    public function getContent(array $context = []);
}
