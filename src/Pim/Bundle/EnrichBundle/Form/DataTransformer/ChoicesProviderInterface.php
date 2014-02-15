<?php

namespace Pim\Bundle\EnrichBundle\Form\DataTransformer;

/**
 * Describes a choices provider class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see       \Pim\Bundle\EnrichBundle\Form\DataTransformer\EntityToIdentifierTransformer
 */
interface ChoicesProviderInterface
{
    /**
     * Get choices
     *
     * @param array $options
     *
     * @return array
     *
     * Example:
     *     array(
     *         1 => 'foo',
     *         2 => 'bar'
     *     )
     */
    public function getChoices(array $options);
}
