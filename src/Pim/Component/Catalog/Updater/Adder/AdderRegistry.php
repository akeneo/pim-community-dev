<?php

namespace Pim\Component\Catalog\Updater\Adder;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Registry of adders
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AdderRegistry implements AdderRegistryInterface
{
    /** @var AttributeAdderInterface[] priorized attribute adders */
    protected $attributeAdders = [];

    /** @var FieldAdderInterface[] priorized field adders */
    protected $fieldAdders = [];

    /**
     * {@inheritdoc}
     */
    public function register(AdderInterface $adder)
    {
        if ($adder instanceof FieldAdderInterface) {
            $this->fieldAdders[] = $adder;
        }
        if ($adder instanceof AttributeAdderInterface) {
            $this->attributeAdders[] = $adder;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldAdder($field)
    {
        foreach ($this->fieldAdders as $adder) {
            if ($adder->supportsField($field)) {
                return $adder;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeAdder(AttributeInterface $attribute)
    {
        foreach ($this->attributeAdders as $adder) {
            if ($adder->supportsAttribute($attribute)) {
                return $adder;
            }
        }

        return null;
    }
}
