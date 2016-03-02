<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Pim\Bundle\TransformBundle\Exception\PropertyTransformerException;

/**
 * Interface for transformer classes
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
 */
interface PropertyTransformerInterface
{
    /**
     * Returns transformed value
     *
     * @param string|array $value
     * @param array        $options
     *
     * @throws PropertyTransformerException
     *
     * @return mixed
     */
    public function transform($value, array $options = []);
}
