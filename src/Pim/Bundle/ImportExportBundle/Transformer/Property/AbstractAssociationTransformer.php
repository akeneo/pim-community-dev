<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;

/**
 * Abstract class for association transformers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAssociationTransformer implements AssociationTransformerInterface
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
        $getEntity = function ($value) use ($options) {
            if (isset($options['reference_prefix'])) {
                $value = $options['reference_prefix'] . '.' . $value;
            }
            $entity = $this->getEntity($options['class'], $value);
            if (!$entity) {
                throw new PropertyTransformerException(
                    'No entity of class "%class%" with code "%code%"',
                    array('%class%' => $options['class'], '%code%' => $value)
                );
            }

            return $entity;
        };

        return $multiple
            ? array_map($getEntity, preg_split('/\s*,\s*/', $value))
            : $getEntity($value);
    }
}
