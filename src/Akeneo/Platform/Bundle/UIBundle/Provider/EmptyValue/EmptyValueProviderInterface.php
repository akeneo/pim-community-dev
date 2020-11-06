<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue;

/**
 * EmptyValue provider interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EmptyValueProviderInterface
{
    /**
     * Get the EmptyValue for the given element
     *
     * @param mixed $element
     *
     * @throws RuntimeException If no empty value is found for the given element
     */
    public function getEmptyValue($element): string;

    /**
     * Does the EmptyValue provider support the element
     *
     * @param mixed $element
     */
    public function supports($element): bool;
}
