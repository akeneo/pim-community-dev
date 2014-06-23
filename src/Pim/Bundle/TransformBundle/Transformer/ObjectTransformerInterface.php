<?php

namespace Pim\Bundle\TransformBundle\Transformer;

/**
 * Common interface for objects transformers
 *
 * @author    Benoit Jacquemont <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TransformerInterface
{
    /**
     * Transforms an object to an instance of another class
     *
     * @param object $sourceObject
     * @param array  $context
     *
     * @return object target object instance
     */
    public function transform($sourceObject, array $defaults = array());
}
