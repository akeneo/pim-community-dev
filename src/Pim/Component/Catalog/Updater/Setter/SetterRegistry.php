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
class SetterRegistry implements SetterRegistryInterface
{
    /** @var AttributeSetterInterface[] priorized attribute setters */
    protected $attributeSetters = [];

    /** @var FieldSetterInterface[] priorized field setters */
    protected $fieldSetters = [];

    /**
     * {@inheritdoc}
     */
    public function register(SetterInterface $setter)
    {
        if ($setter instanceof FieldSetterInterface) {
            $this->fieldSetters[] = $setter;
        }
        if ($setter instanceof AttributeSetterInterface) {
            $this->attributeSetters[] = $setter;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldSetter($field)
    {
        foreach ($this->fieldSetters as $setter) {
            if ($setter->supportsField($field)) {
                return $setter;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSetter(AttributeInterface $attribute)
    {
        foreach ($this->attributeSetters as $setter) {
            if ($setter->supportsAttribute($attribute)) {
                return $setter;
            }
        }

        return null;
    }
}
