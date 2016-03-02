<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Pim\Bundle\TransformBundle\Exception\PropertyTransformerException;

/**
 * Transform entity codes in entity / entity arrays
 *
 * The following options are required:
 *
 * - class:             the class of the related entity
 *
 * The following options are optional:
 *
 * - multiple:          set to true to return an array of entities
 * - reference_prefix:  a string to be prepended to the entity references
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
 */
class RelationTransformer implements PropertyTransformerInterface
{
    /**
     * @var DoctrineCache
     */
    protected $doctrineCache;

    /**
     * Constructor
     *
     * @param DoctrineCache $doctrineCache
     */
    public function __construct(DoctrineCache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = [])
    {
        if (!isset($options['class'])) {
            throw new \InvalidArgumentException('class option is required');
        }
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
            return $multiple ? [] : null;
        }
    }

    /**
     * Transform non empty value
     *
     * @param string|array $value
     * @param string       $class
     * @param bool         $multiple
     * @param string       $referencePrefix
     *
     * @throws PropertyTransformerException
     *
     * @return object|array
     */
    protected function doTransform($value, $class, $multiple, $referencePrefix)
    {
        $findObject = function ($value) use ($class, $referencePrefix) {
            $object = $this->findObject($class, $referencePrefix . $value);
            if (!$object) {
                $tokens = explode('\\', $class);
                $objectName = end($tokens);
                throw new PropertyTransformerException(
                    'The "%objectName%" with code "%code%" is unknown',
                    ['%objectName%' => $objectName, '%code%' => $referencePrefix.$value]
                );
            }

            return $object;
        };

        if ($multiple && !is_array($value)) {
            $value = preg_split('/\s*,\s*/', $value);
        }

        return $multiple
            ? array_map($findObject, $value)
            : $findObject($value);
    }

    /**
     * Finds the object in the cache
     *
     * @param string $class
     * @param string $value
     *
     * @return object
     */
    protected function findObject($class, $value)
    {
        return $this->doctrineCache->find($class, $value);
    }
}
