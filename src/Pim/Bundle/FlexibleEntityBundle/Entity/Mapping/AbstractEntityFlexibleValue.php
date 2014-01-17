<?php

namespace Pim\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Base Doctrine ORM entity attribute value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractEntityFlexibleValue extends AbstractFlexibleValue
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
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\FlexibleEntityBundle\Entity\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute;

    /**
     * @var Entity $entity
     *
     * This field must by overrided in concret value class
     *
     * @ORM\ManyToOne(targetEntity="AbstractEntityFlexible", inversedBy="values")
     */
    protected $entity;

    /**
     * Locale code
     * @var string $locale
     *
     * @ORM\Column(name="locale_code", type="string", length=20, nullable=true)
     */
    protected $locale;

    /**
     * Scope code
     * @var string $scope
     *
     * @ORM\Column(name="scope_code", type="string", length=20, nullable=true)
     */
    protected $scope;

    /**
     * Store varchar value
     * @var string $varchar
     *
     * @ORM\Column(name="value_string", type="string", length=255, nullable=true)
     */
    protected $varchar;

    /**
     * Store int value
     * @var integer $integer
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     */
    protected $integer;

    /**
     * Store decimal value
     * @var double $decimal
     *
     * @ORM\Column(name="value_decimal", type="decimal", precision=14, scale=4, nullable=true)
     */
    protected $decimal;

    /**
     * Store boolean value
     * @var boolean $boolean
     *
     * @ORM\Column(name="value_boolean", type="boolean", nullable=true)
     */
    protected $boolean;

    /**
     * Store text value
     * @var string $text
     *
     * @ORM\Column(name="value_text", type="text", nullable=true)
     */
    protected $text;

    /**
     * Store date value
     * @var date $date
     *
     * @ORM\Column(name="value_date", type="date", nullable=true)
     */
    protected $date;

    /**
     * Store datetime value
     * @var date $datetime
     *
     * @ORM\Column(name="value_datetime", type="datetime", nullable=true)
     */
    protected $datetime;

    /**
     * Store many options values
     *
     * This field must by overrided in concret value class
     *
     * @var options ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption")
     * @ORM\JoinTable(name="pim_flexibleentity_values_options",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id")}
     * )
     */
    protected $options;

    /**
     * Store simple option value
     *
     * @var Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption $option
     *
     * @ORM\ManyToOne(targetEntity="Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption", cascade="persist")
     * @ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="SET NULL")
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
     * Get entity
     *
     * @return AbstractFlexible $entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set entity
     *
     * @param AbstractFlexible $entity
     *
     * @return EntityAttributeValue
     */
    public function setEntity(AbstractFlexible $entity = null)
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
     * @return AbstractEntityFlexibleValue
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
     * @return array
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
     * @return AbstractEntityFlexibleValue
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
     * @return AbstractEntityFlexible
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
     * @param AbstractEntityFlexibleValue $value
     *
     * @return AbstractEntityFlexibleValue
     */
    public function setCollections(AbstractEntityFlexibleValue $value = null)
    {
        $this->collection = $value->getCollections();

        return $this;
    }

    /**
     * Set collection attribute values
     *
     * @param Collection[] $collection
     *
     * @return AbstractEntityFlexibleValue
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Check if value is related to attribute and match locale and scope if it's localizable, scopable
     *
     * @param string $attribute the attribute code
     * @param string $locale    the locale
     * @param string $scope     th scope
     *
     * @return boolean
     */
    public function isMatching($attribute, $locale, $scope)
    {
        $isLocalizable = (int) $this->getAttribute()->isTranslatable();
        $isScopable    = (int) $this->getAttribute()->isScopable();
        $isLocalized   = (int) ($this->getLocale() == $locale);
        $isScoped      = (int) ($this->getScope() == $scope);

        if ($this->getAttribute()->getCode() === $attribute) {
            $matchedMatrix = array('0000', '0100', '0001', '0101', '1111', '1100', '1101', '0011', '0111');
            $status = (string) $isLocalizable.$isLocalized.$isScopable.$isScoped;
            if (in_array($status, $matchedMatrix)) {
                return true;
            }
        }

        return false;
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
