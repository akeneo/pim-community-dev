<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOption;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Abstract product value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
abstract class AbstractProductValue implements ProductValueInterface
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var \Pim\Bundle\CatalogBundle\Model\AbstractAttribute $attribute
     */
    protected $attribute;

    /**
     * @var mixed $data
     */
    protected $data;

    /**
     * @var Entity $entity
     *
     * This field must by overrided in concret value class
     */
    protected $entity;

    /**
     * Locale code
     * @var string $locale
     */
    protected $locale;

    /**
     * Scope code
     * @var string $scope
     */
    protected $scope;

    /**
     * Store varchar value
     * @var string $varchar
     */
    protected $varchar;

    /**
     * Store integer value
     * @var integer $integer
     */
    protected $integer;

    /**
     * Store decimal value
     * @var double $decimal
     */
    protected $decimal;

    /**
     * Store boolean value
     * @var boolean $boolean
     */
    protected $boolean;

    /**
     * Store text value
     * @var string $text
     */
    protected $text;

    /**
     * Store date value
     * @var date $date
     */
    protected $date;

    /**
     * Store datetime value
     * @var date $datetime
     */
    protected $datetime;

    /**
     * Store many options values
     *
     * This field must by overrided in concret value class
     *
     * @var options ArrayCollection
     */
    protected $options;

    /**
     * Store simple option value
     *
     * @var Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption $option
     */
    protected $option;

    /**
     * To implement collection attribute storage, this field must be overridden in concret value class
     *
     * @var ArrayCollection
     */
    protected $collection;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->collection = new ArrayCollection();
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
     * @return AbstractProductValue
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
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
     * @return AbstractProductValue
     * @throws LogicException
     */
    public function setAttribute(AbstractAttribute $attribute = null)
    {
        if (is_object($this->attribute) && ($attribute != $this->attribute)) {
            throw new \LogicException(
                sprintf('An attribute (%s) has already been set for this value', $this->attribute->getCode())
            );
        }
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

    /**
     * Get entity
     *
     * @return AbstractProduct $entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set entity
     *
     * @param AbstractProduct $entity
     *
     * @return EntityAttributeValue
     */
    public function setEntity(AbstractProduct $entity = null)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Set data
     *
     * @param mixed $data
     *
     * @return EntityAttributeValue
     */
    public function setData($data)
    {
        $name = 'set'.ucfirst($this->attribute->getBackendType());

        return $this->$name($data);
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        $name = 'get'.ucfirst($this->attribute->getBackendType());

        return $this->$name();
    }

    /**
     * Add data
     *
     * @param mixed $data
     *
     * @return EntityAttributeValue
     */
    public function addData($data)
    {
        $backendType = $this->attribute->getBackendType();
        if (substr($backendType, -1, 1) === 's') {
            $backendType = substr($backendType, 0, strlen($backendType) - 1);
        }
        $name = 'add'.ucfirst($backendType);

        return $this->$name($data);
    }

    /**
     * Get varchar data
     *
     * @return string
     */
    public function getVarchar()
    {
        return $this->varchar;
    }

    /**
     * Set varchar data
     *
     * @param string $varchar
     *
     * @return EntityAttributeValue
     */
    public function setVarchar($varchar)
    {
        $this->varchar = $varchar;

        return $this;
    }

    /**
     * Get integer data
     *
     * @return integer
     */
    public function getInteger()
    {
        return $this->integer;
    }

    /**
     * Set integer data
     *
     * @param integer $integer
     *
     * @return EntityAttributeValue
     */
    public function setInteger($integer)
    {
        $this->integer = $integer;

        return $this;
    }

    /**
     * Get decimal data
     *
     * @return double
     */
    public function getDecimal()
    {
        return $this->decimal;
    }

    /**
     * Set decimal data
     *
     * @param double $decimal
     *
     * @return EntityAttributeValue
     */
    public function setDecimal($decimal)
    {
        $this->decimal = $decimal;

        return $this;
    }

    /**
     * Get boolean data
     *
     * @return boolean
     */
    public function getBoolean()
    {
        return $this->boolean;
    }

    /**
     * Set boolean data
     *
     * @param boolean $boolean
     *
     * @return EntityAttributeValue
     */
    public function setBoolean($boolean)
    {
        $this->boolean = $boolean;

        return $this;
    }

    /**
     * Get text data
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text data
     *
     * @param string $text
     *
     * @return EntityAttributeValue
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get date data
     *
     * @return date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date data
     *
     * @param date $date
     *
     * @return EntityAttributeValue
     */
    public function setDate($date)
    {
        if ($this->date != $date) {
            $this->date = $date;
        }

        return $this;
    }

    /**
     * Get datetime data
     *
     * @return datetime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set datetime data
     *
     * @param datetime $datetime
     *
     * @return EntityAttributeValue
     */
    public function setDatetime($datetime)
    {
        if ($this->datetime != $datetime) {
            $this->datetime = $datetime;
        }

        return $this;
    }

    /**
     * Set option, used for simple select to set single option
     *
     * @param AbstractEntityAttributeOption $option
     *
     * @return AbstractProductValue
     */
    public function setOption(AbstractEntityAttributeOption $option = null)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * Get related option, used for simple select to set single option
     *
     * @return AbstractEntityAttributeOption
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Get options, used for multi select to retrieve many options
     *
     * @return Arraycollection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set options, used for multi select to retrieve many options
     *
     * @param ArrayCollection $options
     *
     * @return AbstractProductValue
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Add option, used for multi select to add many options
     *
     * @param AbstractEntityAttributeOption $option
     *
     * @return AbstractProduct
     */
    public function addOption(AbstractEntityAttributeOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Get Collection attribute values
     *
     * @return Collection[]
     */
    public function getCollections()
    {
        return $this->collection;
    }

    /**
     * Get Collection attribute values
     *
     * @return Collection[]
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set collections data from value object
     *
     * @param AbstractProductValue $value
     *
     * @return AbstractProductValue
     */
    public function setCollections(AbstractProductValue $value = null)
    {
        $this->collection = $value->getCollections();

        return $this;
    }

    /**
     * Set collection attribute values
     *
     * @param Collection[] $collection
     *
     * @return AbstractProductValue
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $data = $this->getData();

        if ($data instanceof \DateTime) {
            $data = $data->format(\DateTime::ISO8601);
        }

        if ($data instanceof \Doctrine\Common\Collections\Collection) {
            $items = array();
            foreach ($data as $item) {
                $value = (string) $item;
                if (!empty($value)) {
                    $items[] = $value;
                }
            }

            return implode(', ', $items);
        } elseif (is_object($data)) {
            return (string) $data;
        }

        return (string) $data;
    }

}
