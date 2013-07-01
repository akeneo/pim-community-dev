<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue;

/**
 * Base Doctrine ORM entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
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
     * @return \ArrayAccess
     */
    public function getValues()
    {
        return $this->values;
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
        $locale = ($localeCode) ? $localeCode : $this->getLocale();
        $scope  = ($scopeCode) ? $scopeCode : $this->getScope();
        $values = $this->getValues();

        if (empty($values)) {
            return false;
        }

        $values = $values->filter(
            function ($value) use ($attributeCode, $locale, $scope) {
                // related value to asked attribute
                if ($value->getAttribute()->getCode() == $attributeCode) {
                    // return relevant translated value if translatable
                    if ($value->getAttribute()->getTranslatable() and $value->getLocale() == $locale) {
                        // check also scope if scopable
                        if ($value->getAttribute()->getScopable() and $value->getScope() == $scope) {
                            return true;
                        } elseif (!$value->getAttribute()->getScopable()) {
                            return true;
                        }
                    } elseif (!$value->getAttribute()->getTranslatable()) {
                        // return the value if not translatable
                        return true;
                    }
                }

                return false;
            }
        );
        $value = (count($values) == 1) ? $values->first() : false;

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
        //return true;

        $values = $this->getValues();

        if (empty($values)) {
            return false;
        }

        $values = $values->filter(
            function ($value) use ($name) {
                // related value to asked attribute
                if ($value->getAttribute()->getCode() == $name) {
                    return true;
                }
            }
        );
        return (count($values) >= 1);
    }

    /**
     * TODO : to move !
     *
     * @param string $code
     *
     * @return string
     */
    protected function sanitizeAttributeCode($code)
    {
        return strtolower(implode('_', preg_split('/(?=[A-Z])/', $code, -1, PREG_SPLIT_NO_EMPTY)));
    }

    /**
     * Add support of magic method getAttributeCode, setAttributeCode, addAttributeCode
     *
     * @param string $method
     * @param string $arguments
     *
     * @throws \Exception
     *
     * @return Ambigous <mixed, multitype:>
     */
    public function __call($method, $arguments)
    {
        preg_match('/get(.*)/', $method, $matches);

        if (count($matches) > 1) {
            $attributeCode = $this->sanitizeAttributeCode($matches[1]);

            return $this->getValue($attributeCode);
        }

        preg_match('/set(.*)/', $method, $matches);
        if (count($matches) > 1) {

            $attributeCode = $this->sanitizeAttributeCode($matches[1]);
            $data          = $arguments[0];
            $locale        = (isset($arguments[1])) ? $arguments[1] : $this->getLocale();
            $scope         = (isset($arguments[2])) ? $arguments[2] : $this->getScope();
            $value         = $this->getValue($attributeCode, $locale, $scope);

            if (!$value) {
                if (!isset($this->allAttributes[$attributeCode])) {
                    throw new \Exception(sprintf('Could not find attribute "%s".', $attributeCode));
                }

                $attribute = $this->allAttributes[$attributeCode];
                $value = new $this->valueClass();
                $value->setAttribute($attribute);

                if ($attribute->getTranslatable()) {
                    $value->setLocale($locale);
                }

                if ($attribute->getScopable()) {
                    $value->setScope($scope);
                }

                $this->addValue($value);
            }

            $value->setData($data);

            return $this;
        }

        preg_match('/add(.*)/', $method, $matches);
        if (count($matches) > 1) {

            $attributeCode = $this->sanitizeAttributeCode($matches[1]);
            $data          = $arguments[0];
            $locale        = (isset($arguments[1])) ? $arguments[1] : $this->getLocale();
            $scope         = (isset($arguments[2])) ? $arguments[2] : $this->getScope();
            $value         = $this->getValue($attributeCode, $locale, $scope);

            if (!$value) {
                if (!isset($this->allAttributes[$attributeCode])) {
                    throw new \Exception(sprintf('Could not find attribute "%s".', $attributeCode));
                }

                $attribute = $this->allAttributes[$attributeCode];
                $value = new $this->valueClass();
                $value->setAttribute($attribute);

                if ($attribute->getTranslatable()) {
                    $value->setLocale($locale);
                }

                if ($attribute->getScopable()) {
                    $value->setScope($scope);
                }

                $this->addValue($value);
            }

            $value->addData($data);

            return $this;
        }
    }

    /**
     * TODO merge with __call ! ensure that existing method can be called
     *
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
