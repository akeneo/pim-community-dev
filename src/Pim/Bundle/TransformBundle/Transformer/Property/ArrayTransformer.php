<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Transforms values in arrays
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArrayTransformer extends DefaultTransformer implements EntityUpdaterInterface
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
    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = array())
    {
        $suffixes = $columnInfo->getSuffixes();
        $this->propertyAccessor->setValue(
            $object,
            sprintf('%s[%s]', $columnInfo->getPropertyPath(), array_pop($suffixes)),
            $data
        );
    }
}
