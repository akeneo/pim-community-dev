<?php

namespace Pim\Bundle\EnrichBundle\ViewElement;

/**
 * View element interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ViewElementInterface
{
    /**
     * Get the content
     *
     * @param array $context the twig context
     *
     * @return string
     */
    public function getContent(array $context = []);
}
