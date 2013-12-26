<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Cache\EntityCache;
use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;

/**
 * Transform entity codes in entity arrays
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTransformer implements PropertyTransformerInterface
{
    /**
     * @var EntityCache
     */
    protected $entityCache;

    /**
     * Constructor
     *
     * @param EntityCache $entityCache
     */
    public function __construct(EntityCache $entityCache)
    {
        $this->entityCache = $entityCache;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        if (is_scalar($value)) {
            $value = trim($value);
        }

        $multiple = isset($options['multiple']) && $options['multiple'];

        if ($value) {
            return $this->doTransform(
                $value,
                $options['class'],
                $multiple,
                isset($options['reference_prefix']) ? $options['reference_prefix'] . '.' : ''
            );
        } else {
            return $multiple ? array() : null;
        }
    }

    /**
     * Transform non empty value
     *
     * @param string|array $value
     * @param string       $class
     * @param boolean      $multiple
     * @param string       $referencePrefix
     *
     * @return object|array
     *
     * @throws PropertyTransformerException
     */
    protected function doTransform($value, $class, $multiple, $referencePrefix)
    {
        $getEntity = function ($value) use ($class, $referencePrefix) {
            $entity = $this->getEntity($class, $referencePrefix . $value);
            if (!$entity) {
                throw new PropertyTransformerException(
                    'No entity of class "%class%" with code "%code%"',
                    array('%class%' => $class, '%code%' => $referencePrefix . $value)
                );
            }

            return $entity;
        };

        if ($multiple && !is_array($value)) {
            $value = preg_split('/\s*,\s*/', $value);
        }

        return $multiple
            ? array_map($getEntity, $value)
            : $getEntity($value);
    }

    /**
     * Finds the entity in the database
     *
     * @param string $class
     * @param string $value
     *
     * @return object
     */
    protected function getEntity($class, $value)
    {
        return $this->entityCache->find($class, $value);
    }
}
