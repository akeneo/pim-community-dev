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
     * Get the view element alias
     *
     * @param array $context The twig context
     *
     * @return string
     */
    public function getAlias(array $context = []);

    /**
     * Indicates if this element supports the current context
     *
     * @param array $context The twig context
     *
     * @return boolean
     */
    public function supportsContext(array $context = []);

    /**
     * Get the template
     *
     * @return string
     */
    public function getTemplate();

    /**
     * Get additional template parameters
     *
     * @return array
     */
    public function getParameters();
}
