<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Translation transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslationTransformer extends DefaultTransformer implements EntityUpdaterInterface
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
        if ($columnInfo->getLocale()) {
            $locale = $columnInfo->getLocale();
        } else {
            $suffixes = $columnInfo->getSuffixes();
            $locale = array_shift($suffixes);
        }
        $object->setLocale($locale);
        $this->propertyAccessor->setValue($object, 'translation.' . $columnInfo->getPropertyPath(), $data);
    }
}
