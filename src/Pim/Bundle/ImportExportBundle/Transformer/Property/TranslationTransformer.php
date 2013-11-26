<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\DefaultTransformer;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo;

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
    public function setValue($object, ColumnInfo $columnInfo, $data, array $options = array())
    {
        $object->setLocale($columnInfo['suffixes'][0]);
        $this->propertyAccessor->setValue($object, 'translation.' . $columnInfo['propertyPath'], $data);
    }
}
