<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model;

use Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleConfigurationException;

use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\LocalizableInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;

/**
 * Abstract entity value, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFlexibleValue implements FlexibleValueInterface, LocalizableInterface, ScopableInterface
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var AbstractAttribute $attribute
     */
    protected $attribute;

    /**
     * @var mixed $data
     */
    protected $data;

    /**
     * @var string $locale
     */
    protected $locale;

    /**
     * @var string $scope
     */
    protected $scope;

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
     * @return AbstractFlexibleValue
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return AbstractFlexibleValue
     */
    public function setData($data)
    {
        $this->data = $data;

         return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

     /**
      * Has data
      * @return boolean
      */
    public function hasData()
    {
        return !is_null($this->getData());
    }

    /**
     * Set attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return AbstractFlexibleValue
     */
    public function setAttribute(AbstractAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return AbstractAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
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
     * @param string $locale
     */
    public function setLocale($locale)
    {
        if ($locale and $this->getAttribute() and $this->getAttribute()->isLocalizable() === false) {
            $attributeCode = $this->getAttribute()->getCode();
            throw new FlexibleConfigurationException(
                "This value '".$this->getId()."' can't be localized, see attribute '".$attributeCode."' configuration"
            );
        }

        $this->locale = $locale;
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
     * @param string $scope
     */
    public function setScope($scope)
    {
        if ($scope and $this->getAttribute() and $this->getAttribute()->isScopable() === false) {
            $attributeCode = $this->getAttribute()->getCode();
            throw new FlexibleConfigurationException(
                "This value '".$this->getId()."' can't be scopped, see attribute '".$attributeCode."' configuration"
            );
        }

        $this->scope = $scope;
    }
}
