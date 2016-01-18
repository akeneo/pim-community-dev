<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;

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
    /** @var int|string */
    protected $id;

    /** @var \Pim\Bundle\CatalogBundle\Model\AttributeInterface */
    protected $attribute;

    /** @var mixed */
    protected $data;

    /** @var ProductInterface */
    protected $entity;

    /**
     * LocaleInterface code
     *
     * @var string
     */
    protected $locale;

    /**
     * Scope code
     *
     * @var string
     */
    protected $scope;

    /**
     * Store varchar value
     *
     * @var string
     */
    protected $varchar;

    /**
     * Store int value
     *
     * @var int
     */
    protected $integer;

    /**
     * Store decimal value
     *
     * @var float
     */
    protected $decimal;

    /**
     * Store boolean value
     *
     * @var bool
     */
    protected $boolean;

    /**
     * Store text value
     *
     * @var string
     */
    protected $text;

    /**
     * Store date value
     *
     * @var date
     */
    protected $date;

    /**
     * Store datetime value
     *
     * @var \Datetime
     */
    protected $datetime;

    /**
     * Store many options values
     *
     * This field must by overrided in concret value class
     *
     * @var ArrayCollection
     */
    protected $options;

    /** @var array */
    protected $optionIds;

    /**
     * Store simple option value
     *
     * @var AttributeOptionInterface
     */
    protected $option;

    /**
     * Store upload values
     *
     * @var FileInfoInterface
     */
    protected $media;

    /**
     * Store metric value
     *
     * @var MetricInterface
     */
    protected $metric;

    /**
     * Store prices value
     *
     * @var ArrayCollection
     */
    protected $prices;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
        $this->prices  = new ArrayCollection();
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
     * {@inheritdoc}
     */
    public function setAttribute(AttributeInterface $attribute = null)
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
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        if ($locale && $this->getAttribute() && $this->getAttribute()->isLocalizable() === false) {
            $attributeCode = $this->getAttribute()->getCode();
            throw new \LogicException(
                "This value '".$this->getId()."' can't be localized, see attribute '".$attributeCode."' configuration"
            );
        }

        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($scope)
    {
        if ($scope && $this->getAttribute() && $this->getAttribute()->isScopable() === false) {
            $attributeCode = $this->getAttribute()->getCode();
            throw new \LogicException(
                "This value '".$this->getId()."' can't be scoped, see attribute '".$attributeCode."' configuration"
            );
        }

        $this->scope = $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function setEntity(ProductInterface $entity = null)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProduct(ProductInterface $product = null)
    {
        $this->entity = $product;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data)
    {
        $setter = $this->attribute->getBackendType();
        if ($this->attribute->isBackendTypeReferenceData()) {
            $setter = $this->attribute->getReferenceDataName();
        }

        $setter = 'set'.ucfirst($setter);

        return $this->$setter($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $getter = $this->attribute->getBackendType();
        if ($this->attribute->isBackendTypeReferenceData()) {
            $getter = $this->attribute->getReferenceDataName();
        }

        $getter = 'get'.ucfirst($getter);

        return $this->$getter();
    }

    /**
     * {@inheritdoc}
     */
    public function addData($data)
    {
        $backendType = $this->attribute->getBackendType();
        if ($this->attribute->isBackendTypeReferenceData()) {
            $backendType = $this->attribute->getReferenceDataName();
        }

        if (substr($backendType, -1, 1) === 's') {
            $backendType = substr($backendType, 0, strlen($backendType) - 1);
        }
        $name = 'add'.ucfirst($backendType);

        return $this->$name($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getVarchar()
    {
        return $this->varchar;
    }

    /**
     * {@inheritdoc}
     */
    public function setVarchar($varchar)
    {
        $this->varchar = $varchar;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInteger()
    {
        return $this->integer;
    }

    /**
     * {@inheritdoc}
     */
    public function setInteger($integer)
    {
        $this->integer = $integer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDecimal()
    {
        return $this->decimal;
    }

    /**
     * {@inheritdoc}
     */
    public function setDecimal($decimal)
    {
        $this->decimal = $decimal;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBoolean()
    {
        return $this->boolean;
    }

    /**
     * {@inheritdoc}
     */
    public function setBoolean($boolean)
    {
        $this->boolean = $boolean;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * {@inheritdoc}
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * {@inheritdoc}
     */
    public function setDate($date)
    {
        if ($this->date != $date) {
            $this->date = $date;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * {@inheritdoc}
     */
    public function setDatetime($datetime)
    {
        if ($this->datetime != $datetime) {
            $this->datetime = $datetime;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOption(AttributeOptionInterface $option = null)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addOption(AttributeOptionInterface $option)
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeOption(AttributeOptionInterface $option)
    {
        $this->options->removeElement($option);

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

        if ($data instanceof Collection) {
            $items = [];
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

    /**
     * {@inheritdoc}
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * {@inheritdoc}
     */
    public function setMedia(FileInfoInterface $media = null)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetric()
    {
        if (is_object($this->metric)) {
            $this->metric->setValue($this);
        }

        return $this->metric;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetric(MetricInterface $metric)
    {
        $metric->setValue($this);
        $this->metric = $metric;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrices()
    {
        $prices = [];
        foreach ($this->prices as $price) {
            $prices[$price->getCurrency()] = $price;
        }

        ksort($prices);

        return new ArrayCollection($prices);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice($currency)
    {
        foreach ($this->prices as $price) {
            if ($price->getCurrency() === $currency) {
                return $price;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrices($prices)
    {
        foreach ($prices as $price) {
            $this->addPrice($price);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPrice(ProductPriceInterface $price)
    {
        if (null !== $actualPrice = $this->getPrice($price->getCurrency())) {
            $this->removePrice($actualPrice);
        }

        $this->prices->add($price);
        $price->setValue($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removePrice(ProductPriceInterface $price)
    {
        $this->prices->removeElement($price);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRemovable()
    {
        if (null === $this->entity) {
            return true;
        }

        return $this->entity->isAttributeRemovable($this->attribute);
    }
}
