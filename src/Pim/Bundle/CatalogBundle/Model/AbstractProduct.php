<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ScopableInterface;
use Pim\Bundle\CatalogBundle\Model\LocalizableInterface;
use Pim\Bundle\CatalogBundle\Model\TimestampableInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Abstract product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractProduct implements ProductInterface, LocalizableInterface, ScopableInterface, TimestampableInterface
{
    /** @var mixed $id */
    protected $id;

    /** @var datetime $created */
    protected $created;

    /** @var datetime $updated */
    protected $updated;

    /**
     * Not persisted but allow to force locale for values
     * @var string $locale
     */
    protected $locale;

    /**
     * Not persisted but allow to force scope for values
     * @var string $scope
     */
    protected $scope;

    /** @var ArrayCollection */
    protected $values;

    /** @var array */
    protected $indexedValues;

    /** @var boolean */
    protected $indexedValuesOutdated = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return AbstractProduct
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get created datetime
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created datetime
     *
     * @param datetime $created
     *
     * @return TimestampableInterface
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get updated datetime
     *
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated datetime
     *
     * @param datetime $updated
     *
     * @return TimestampableInterface
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get used locale
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set used locale
     *
     * @param string $locale
     *
     * @return LocalizableInterface
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get used scope
     * @return string $scope
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set used scope
     *
     * @param string $scope
     *
     * @return ScopableInterface
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }


    /**
     * Add value, override to deal with relation owner side
     *
     * @param ProductValueInterface $value
     *
     * @return AbstractProduct
     */
    public function addValue(ProductValueInterface $value)
    {
        $this->values[] = $value;
        $this->indexedValues[$value->getAttribute()->getCode()][] = $value;
        $value->setEntity($this);

        return $this;
    }

    /**
     * Remove value
     *
     * @param ProductValueInterface $value
     *
     * @return AbstractProduct
     */
    public function removeValue(ProductValueInterface $value)
    {
        $this->removeIndexedValue($value);
        $this->values->removeElement($value);

        return $this;
    }

    /**
     * Remove a value from the indexedValues array
     *
     * @param ProductValueInterface $value
     *
     * @return AbstractProduct
     */
    protected function removeIndexedValue(ProductValueInterface $value)
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
     * @return AbstractProduct
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
     * @return AbstractProduct
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
     * @return ProductValueInterface
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
