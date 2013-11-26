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
        $value = trim($value);

        $multiple = (isset($options['multiple']) && $options['multiple']);
        if (!$value) {
            return $multiple ? array() : null;
        }

        $entityCache = $this->entityCache;
        $transform = function ($value) use ($options, $entityCache) {
            $entity = $entityCache->find($options['class'], $value);
            if (!$entity) {
                throw new PropertyTransformerException(
                    'No entity of class "%class%" with code "%value%"',
                    array('%class%' => $options['class'], '%value%' => $value)
                );
            }

            return $entity;
        };

        return $multiple
            ? array_map($transform, preg_split('/\s*,\s*/', $value))
            : $transform($value);
    }
}
