<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;

/**
 * Sets data in a product. Data must respect the PIM standard format.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeSetterInterface extends SetterInterface
{
    /**
     * Set attribute data
     *
     * @param EntityWithValuesInterface $entityWithValues
     * @param AttributeInterface        $attribute The attribute of the product to modify
     * @param mixed                     $data      The data to set
     * @param array                     $options   Options passed to the setter
     *
     * @throws PropertyException
     */
    public function setAttributeData(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    );

    /**
     * Supports the attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute);
}
