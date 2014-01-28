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
        $value->setEntity($this);

        return $this;
    }

    /**
     * Remove value
     *
     * @param FlexibleValueInterface $value
     */
    public function removeValue(FlexibleValueInterface $value)
    {
        $this->values->removeElement($value);
    }

    /**
     * Get values
     *
     * @return ArrayCollection
     */
    public function getValues()
    {
        $values = new ArrayCollection();

        foreach ($this->values as $value) {
            $attribute = $value->getAttribute();
            $key = $this->getValueKey($value);
                
            $values[$key] = $value;
        }

        return $values;
    }

    /**
     * Get value related to attribute code
     *
     * @param AbstractEntityAttribute $attribute
     * @param string                  $localeCode
     * @param string                  $scopeCode
     *
     * @return FlexibleValueInterface
     */
    public function getValue(AbstractAttribute $attribute, $localeCode = null, $scopeCode = null)
    {
        $localeCode = ($localeCode) ? $localeCode : $this->getLocale();
        $scopeCode  = ($scopeCode) ? $scopeCode : $this->getScope();

        $valueKey = $this->buildValueKey($attribute, $localeCode, $scopeCode);

        $values = $this->getValues();

        return $values[$valueKey];
    }

    /**
     * Get a key identifing uniquely the value
     *
     * @param AbstractFlexibleValue $value
     *
     * @return string
     */
    protected function getValueKey(AbstractFlexibleValue $value)
    {
        $attribute = $value->getAttribute();

        $localeCode = null;
        $scopeCode = null;

        if ($attribute->isTranslatable()) {
            $locale = $value->getLocale();
        }
        if ($attribute->isScopable()) {
            $scope = $value->getScope();
        }

        return $this->buidlValueKey($attribute, $localeCode, $scopeCode);
    }

    /**
     * Get a key identifier for a value from attributeCode, localeCode and scopeCode
     *
     * @param AbstractEntityAttribute $attribute
     * @param string                  $localeCode
     * @param string                  $scopeCode
     *
     * @return string
     */
    protected function buildValueKey(AbstractEntityAttribute $attribute, $localeCode = null, $scopeCode = null)
    {
        $key = $attribute->getCode();

        if ($attribute->isTranslatable() && $localeCode != null) {
            $key .= '_' . $localeCode;
        }
        if ($attribute->isScopable() && $scopeCode != null) {
            $key .= '_' . $scopeCode;
        }

        return $key;
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
        return 0 !== $this
            ->getValues()
            ->filter(
                function ($value) use ($attribute) {
                    return $value->getAttribute() === $attribute;
                }
            )
            ->count();
    }

    /**
     * Create a new value
     *
     * @param AbstractAttribute $attributeCode
     * @param string            $locale
     * @param string            $scope
     *
     * @throws \Exception
     *
     * @return AbstractFlexibleValue
     */
    public function createValue(AbstractAttribute $attribute, $locale = null, $scope = null)
    {
        $value = new $this->valueClass();
        $value->setAttribute($attribute);
        if ($attribute->isTranslatable()) {
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

            if (!isset($this->allAttributes[$attributeCode])) {
                throw new \Exception(sprintf('Could not find attribute "%s".', $attributeCode));
            }
            $attribute = $this->allAttributes[$attributeCode];

            return $this->getValue($attribute);
        }

        $attributeCode = null;
        if (preg_match('/set(.*)/', $method, $matches)) {
            $attributeCode = Inflector::tableize($matches[1]);
            $method        = 'setData';
        } elseif (preg_match('/add(.*)/', $method, $matches)) {
            $attributeCode = Inflector::tableize($matches[1]);
            $method        = 'addData';
        }

        if (!isset($this->allAttributes[$attributeCode])) {
            throw new \Exception(sprintf('Could not find attribute "%s".', $attributeCode));
        }

        return $this->updateValue($attribute, $method, $arguments);
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
    protected function updateValue(AbstractAttribute $attribute, $method, $arguments)
    {
        $data   = $arguments[0];
        $locale = (isset($arguments[1])) ? $arguments[1] : $this->getLocale();
        $scope  = (isset($arguments[2])) ? $arguments[2] : $this->getScope();
        $value  = $this->getValue($attribute, $locale, $scope);
        if ($value === null) {
            $value = $this->createValue($attribute, $locale, $scope);
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
            // dynamic call to get value data
            return $this->getValue($attCode);
        }
    }
}
