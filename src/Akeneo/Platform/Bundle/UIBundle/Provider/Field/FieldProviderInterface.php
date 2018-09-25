<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\Field;

/**
 * Field provider interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldProviderInterface
{
    /**
     * Get the Field for the given element
     *
     * @param mixed $element
     *
     * @throws RuntimeException If no field is found for the given element
     *
     * @return string
     */
    public function getField($element);

    /**
     * Does the Field provider support the element
     *
     * @param mixed $element
     *
     * @return bool
     */
    public function supports($element);
}
