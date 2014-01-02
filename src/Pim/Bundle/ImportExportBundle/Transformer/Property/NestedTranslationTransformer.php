<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Nested translation transformer
 *
 * The following options are required:
 *
 * - propertyPath: the name of the translated property
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NestedTranslationTransformer implements PropertyTransformerInterface, EntityUpdaterInterface
{
    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * Constructor
     *
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        if (!is_array($value)) {
            throw new PropertyTransformerException('Data should be an array');
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = array())
    {
        if (!isset($options['propertyPath'])) {
            throw new \InvalidArgumentException('propertyPath option is required');
        }

        foreach ($data as $locale => $value) {
            $object->setLocale($locale);
            $this->propertyAccessor->setValue($object, 'translation.'.$options['propertyPath'], $value);
        }
    }
}
