<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

/**
 * Abstract class for association transformers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAssociationTransformer implements PropertyTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        $value = trim($value);

        $multiple = isset($options['multiple']) && $options['multiple'];

        if (!$value) {
            return $multiple ? array() : null;
        }
        $getReference = function ($value) use ($options) {
            return $this->getReference($options['class'], $value);
        };

        return $multiple
            ? array_map($getReference, preg_split('/\s*,\s*/', $value))
            : $getReference($value);
    }

    /**
     * Returns an object for a given class and code
     *
     * @param string $class
     * @param string $code
     *
     * @return object
     */
    abstract public function getReference($class, $value);
}
