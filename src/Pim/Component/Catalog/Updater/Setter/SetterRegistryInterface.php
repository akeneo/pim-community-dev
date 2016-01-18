<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Registry of setters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SetterRegistryInterface
{
    /**
     * Register a setter
     *
     * @param SetterInterface $setter
     *
     * @return SetterRegistryInterface
     */
    public function register(SetterInterface $setter);

    /**
     * Get the field setter
     *
     * @param string $field the field
     *
     * @return FieldSetterInterface|null
     */
    public function getFieldSetter($field);

    /**
     * Get the attribute setter
     *
     * @param AttributeInterface $attribute
     *
     * @return AttributeSetterInterface|null
     */
    public function getAttributeSetter(AttributeInterface $attribute);
}
