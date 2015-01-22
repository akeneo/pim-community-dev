<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Util\ProductValueKeyGenerator;

/**
 * Product template model, contains common product values as raw data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplate implements ProductTemplateInterface
{
    /** @var integer $id */
    protected $id;

    /** @var Group $group */
    protected $group;

    /** @var array */
    protected $valuesData = [];

    /**
     * TODO (JJ) nope
     *
     * {@inheritdoc}
     */
    protected $values = [];

    /**
     * {@inheritdoc}
     *
     * TODO (JJ) typehint array
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Get values
     *
     * @return ProductValueInterface[]
     */
    public function getValues()
    {
        // TODO (JJ) you should return an array, not an ArrayCollection
        $values = new ArrayCollection();

        foreach ($this->values as $value) {
            $values[ProductValueKeyGenerator::getKey($value)] = $value;
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesData()
    {
        return $this->valuesData;
    }

    /**
     * {@inheritdoc}
     */
    public function setValuesData(array $valuesData)
    {
        $this->valuesData = $valuesData;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue(ProductValueInterface $value)
    {
        $attributeCode = $value->getAttribute()->getCode();
        if (!isset($this->valuesData[$attributeCode])) {
            return false;
        }

        $valuesData = $this->valuesData[$attributeCode];
        foreach ($valuesData as $valueData) {
            if ($valueData['locale'] === $value->getLocale() && $valueData['scope'] === $value->getScope()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValueForAttribute(AttributeInterface $attribute)
    {
        return isset($this->valuesData[$attribute->getCode()]);
    }

    /**
     * Get attributes of the product template
     * TODO: this method could be optimized by storing the attributes appart in db
     *
     * TODO (JJ) either bad naming (it returns attribute codes) or bad return
     *
     * @return array
     */
    public function getAttributes()
    {
        return array_keys($this->valuesData);
    }
}
