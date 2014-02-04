<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue;

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
     * Associative array of defined attributes
     *
     * @var array
     */
    protected $allAttributes;

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
        $this->allAttributes = array();
        $this->values        = new ArrayCollection();
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAllAttributes()
    {
        return $this->allAttributes;
    }

    /**
     * Set attributes
     *
     * @param array $attributes
     *
     * @return AbstractEntityFlexible
     */
    public function setAllAttributes($attributes)
    {
        $this->allAttributes = $attributes;

        return $this;
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
        $this->allAttributes[$value->getAttribute()->getCode()] = $value->getAttribute();
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
        foreach ($possibleValues as $key => $possibleValue) {
            if ($value === $possibleValue) {
                unset($possibleValues[$key]);
                break;
            }
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

        $attribute = $this->getAttribute($attributeCode);

        $valueLocale = null;
        $valueScope = null;
        if ($attribute->isLocalizable()) {
            $valueLocale = ($localeCode) ? $localeCode : $this->getLocale();
        }
        if ($attribute->isScopable()) {
            $valueScope = ($scopeCode) ? $scopeCode : $this->getScope();
        }

        $value = null;
        $possibleValues = $indexedValues[$attributeCode];

        foreach ($possibleValues as $possibleValue) {
            if ($possibleValue->getLocale() === $valueLocale && $possibleValue->getScope() === $valueScope) {
                $value = $possibleValue;
                break;
            }
        }

        return $value;
    }

    /**
     * Get attribute from its code by using the cache integrated to the product
     *
     * @param string $attributeCode
     *
     * @throws InvalidParameterException
     *
     * @return AbstractAttribute
     */
    protected function getAttribute($attributeCode)
    {
        if (isset($this->allAttributes[$attributeCode])) {
            return $this->allAttributes[$attributeCode];
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not find attribute "%s" in %s.',
                    $attributeCode,
                    print_r(array_keys($this->allAttributes), true)
                )
            );
        }
    }

    /**
     * Get wether or not an attribute is part of a product
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
     * Create a new value
     *
     * @param string $attributeCode
     * @param string $locale
     * @param string $scope
     *
     * @throws \Exception
     *
     * @return AbstractFlexibleValue
     */
    public function createValue($attributeCode, $locale = null, $scope = null)
    {
        $attribute = $this->getAttribute($attributeCode);
        $value = new $this->valueClass();
        $value->setAttribute($attribute);
        if ($attribute->isLocalizable()) {
            $value->setLocale($locale);
        }
        if ($attribute->isScopable()) {
            $value->setScope($scope);
        }

        return $value;
    }

    /**
     * Check if a field or attribute exists
     *
     * @param string $name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        // to authorize call to dynamic __get by twig, should be filter on existing attributes
        // cf http://twig.sensiolabs.org/doc/recipes.html#using-dynamic-object-properties
        $values = $this->getValues();

        if (empty($values)) {
            return false;
        }

        $values = $values->filter(
            function ($value) use ($name) {
                if ($value->getAttribute()->getCode() == $name) {
                    return true;
                }
            }
        );

        return (count($values) >= 1);
    }

    /**
     * Add support of magic method getAttributeCode, setAttributeCode, addAttributeCode
     *
     * @param string $method
     * @param string $arguments
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (preg_match('/get(.*)/', $method, $matches)) {
            $attributeCode = Inflector::tableize($matches[1]);

            return $this->getValue($attributeCode);
        }

        $attributeCode = null;
        if (preg_match('/set(.*)/', $method, $matches)) {
            $attributeCode = Inflector::tableize($matches[1]);
            $method        = 'setData';
        } elseif (preg_match('/add(.*)/', $method, $matches)) {
            $attributeCode = Inflector::tableize($matches[1]);
            $method        = 'addData';
        }

        return $this->updateValue($attributeCode, $method, $arguments);
    }

    /**
     * Update the value with passed method and arguments
     *
     * @param string $attributeCode
     * @param string $method
     * @param array  $arguments
     *
     * @throws \Exception
     *
     * @return AbstractEntityFlexible
     */
    protected function updateValue($attributeCode, $method, $arguments)
    {
        $attribute = $this->getAttribute($attributeCode);

        $data   = $arguments[0];
        $locale = (isset($arguments[1])) ? $arguments[1] : $this->getLocale();
        $scope  = (isset($arguments[2])) ? $arguments[2] : $this->getScope();
        $value  = $this->getValue($attributeCode, $locale, $scope);
        if ($value === null) {
            $value = $this->createValue($attributeCode, $locale, $scope);
            $this->addValue($value);
        }
        $value->$method($data);

        return $this;
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
        $methodName = "get{$attCode}";
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        } else {
            return $this->getValue($attCode);
        }
    }
}
