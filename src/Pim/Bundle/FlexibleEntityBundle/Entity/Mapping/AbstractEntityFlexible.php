<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * Base Doctrine ORM entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractEntityFlexible extends AbstractFlexible
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AbstractEntityFlexibleValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * @var array
     *
     * Values indexed by attribute_code
     */
    protected $indexedValues;

    /**
     * @var boolean
     *
     * States that the indexedValues are outdated
     */
    protected $indexedValuesOutdated = true;

    /**
     * Value class used to create new value
     *
     * @var string
     */
    protected $valueClass;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
    }

    /**
     * Set value class
     *
     * @param string $valueClass
     *
     * @return AbstractEntityFlexible
     */
    public function setValueClass($valueClass)
    {
        $this->valueClass = $valueClass;

        return $this;
    }

    /**
     * Add value, override to deal with relation owner side
     *
     * @param FlexibleValueInterface $value
     *
     * @return AbstractEntityFlexible
     */
    public function addValue(FlexibleValueInterface $value)
    {
        $this->values[] = $value;
        $this->indexedValues[$value->getAttribute()->getCode()][] = $value;
        $value->setEntity($this);

        return $this;
    }

    /**
     * Remove value
     *
     * @param FlexibleValueInterface $value
     *
     * @return AbstractEntityFlexible
     */
    public function removeValue(FlexibleValueInterface $value)
    {
        $this->removeIndexedValue($value);
        $this->values->removeElement($value);

        return $this;
    }

    /**
     * Remove a value from the indexedValues array
     *
     * @param FlexibleValueInterface $value
     *
     * @return AbstractEntityFlexible
     */
    protected function removeIndexedValue(FlexibleValueInterface $value)
    {
        $attributeCode = $value->getAttribute()->getCode();
        $possibleValues =& $this->indexedValues[$attributeCode];

        if (is_array($possibleValues)) {
            foreach ($possibleValues as $key => $possibleValue) {
                if ($value === $possibleValue) {
                    unset($possibleValues[$key]);
                    break;
                }
            }
        } else {
            unset($this->indexedValues[$attributeCode]);
        }

        return $this;
    }

    /**
     * Get the list of used attribute code from the indexed values
     *
     * @return array
     */
    public function getUsedAttributeCodes()
    {
        return array_keys($this->getIndexedValues());
    }

    /**
     * Get values
     *
     * @return ArrayCollection
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Build the values indexed by attribute code array
     *
     * @return array indexedValues
     */
    protected function getIndexedValues()
    {
        $this->indexValuesIfNeeded();

        return $this->indexedValues;
    }

    /**
     * Mark the indexed as outdated
     *
     * @return AbstractEntityFlexible
     */
    public function markIndexedValuesOutdated()
    {
        $this->indexedValuesOutdated = true;

        return $this;
    }

    /**
     * Build the indexed values if needed. First step
     * is to make sure that the values are initialized
     * (loaded from DB)
     *
     * @return AbstractEntityFlexible
     */
    protected function indexValuesIfNeeded()
    {
        if ($this->indexedValuesOutdated) {
            $this->indexedValues = array();
            foreach ($this->values as $value) {
                $this->indexedValues[$value->getAttribute()->getCode()][] = $value;
            }
            $this->indexedValuesOutdated = false;
        }

        return $this;
    }

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     * @param string $localeCode
     * @param string $scopeCode
     *
     * @return FlexibleValueInterface
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null)
    {
        $indexedValues = $this->getIndexedValues();

        if (!isset($indexedValues[$attributeCode])) {
            return null;
        }

        $value = null;
        $possibleValues = $indexedValues[$attributeCode];

        if (is_array($possibleValues) && count($possibleValues>0)) {

            foreach ($possibleValues as $possibleValue) {
                $valueLocale = null;
                $valueScope = null;

                if (null !== $possibleValue->getLocale()) {
                    $valueLocale = ($localeCode) ? $localeCode : $this->getLocale();
                }
                if (null !== $possibleValue->getScope()) {
                    $valueScope = ($scopeCode) ? $scopeCode : $this->getScope();
                }
                if ($possibleValue->getLocale() === $valueLocale && $possibleValue->getScope() === $valueScope) {
                    $value = $possibleValue;
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * Get whether or not an attribute is part of a product
     *
     * @param AbstractEntityAttribute $attribute
     *
     * @return boolean
     */
    public function hasAttribute(AbstractAttribute $attribute)
    {
        $indexedValues = $this->getIndexedValues();

        return isset($indexedValues[$attribute->getCode()]);
    }

    /**
     * Check if a field or attribute exists
     *
     * @param string $attributeCode
     *
     * @return boolean
     */
    public function __isset($attributeCode)
    {
        $indexedValues = $this->getIndexedValues();

        return isset($indexedValues[$attributeCode]);
    }

    /**
     * Get value data by attribute code
     *
     * @param string $attCode
     *
     * @return mixed
     */
    public function __get($attCode)
    {
        return $this->getValue($attCode);
    }
}
