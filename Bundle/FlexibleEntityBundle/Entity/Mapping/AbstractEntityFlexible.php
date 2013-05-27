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
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="AbstractEntityFlexibleValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
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
     *
     * @return FlexibleValueInterface
     */
    public function getValue($attributeCode)
    {
        $locale = $this->getLocale();
        $scope  = $this->getScope();
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
