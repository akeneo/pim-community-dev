<?php
namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

/**
 * Product value interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductValueInterface
{
    /**
     * Set text data
     *
     * @param string $text
     *
     * @return ProductValueInterface
     */
    public function setText($text);

    /**
     * Set datetime data
     *
     * @param \Datetime $datetime
     *
     * @return ProductValueInterface
     */
    public function setDatetime($datetime);

    /**
     * Set integer data
     *
     * @param integer $integer
     *
     * @return ProductValueInterface
     */
    public function setInteger($integer);

    /**
     * Set id
     *
     * @param int|string $id
     *
     * @return ProductValueInterface
     */
    public function setId($id);

    /**
     * Get related option, used for simple select to set single option
     *
     * @return AttributeOption
     */
    public function getOption();

    /**
     * Get media
     *
     * @return ProductMediaInterface
     */
    public function getMedia();

    /**
     * Get decimal data
     *
     * @return double
     */
    public function getDecimal();

    /**
     * Set decimal data
     *
     * @param double $decimal
     *
     * @return ProductValueInterface
     */
    public function setDecimal($decimal);

    /**
     * Set attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return ProductValueInterface
     *
     * @throws \LogicException
     */
    public function setAttribute(AttributeInterface $attribute = null);

    /**
     * Add option, used for multi select to add many options
     *
     * @param AttributeOption $option
     *
     * @return ProductValueInterface
     */
    public function addOption(AttributeOption $option);

    /**
     * Get boolean data
     *
     * @return boolean
     */
    public function getBoolean();

    /**
     * Set options, used for multi select to set many options
     *
     * @param ArrayCollection $options
     *
     * @return ProductValueInterface
     */
    public function setOptions($options);

    /**
     * Set prices, used for multi select to set many prices
     *
     * @param ArrayCollection $prices
     *
     * @return ProductValue
     */
    public function setPrices($prices);

    /**
     * Get id
     *
     * @return int|string
     */
    public function getId();

    /**
     * Set varchar data
     *
     * @param string $varchar
     *
     * @return ProductValueInterface
     */
    public function setVarchar($varchar);

    /**
     * Set boolean data
     *
     * @param boolean $boolean
     *
     * @return ProductValueInterface
     */
    public function setBoolean($boolean);

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Get metric
     *
     * @return MetricInterface
     */
    public function getMetric();

    /**
     * Get date data
     *
     * @return \Datetime
     */
    public function getDate();

    /**
     * Get attribute
     *
     * @return AttributeInterface
     */
    public function getAttribute();

    /**
     * Get entity
     *
     * @return ProductValueInterface $entity
     */
    public function getEntity();

    /**
     * Set media
     *
     * @param ProductMediaInterface $media
     *
     * @return ProductValue
     */
    public function setMedia(ProductMediaInterface $media);

    /**
     * Get prices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrices();

    /**
     * Get options, used for multi select to retrieve many options
     *
     * @return ArrayCollection
     */
    public function getOptions();

    /**
     * Get used locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Set metric
     *
     * @param MetricInterface $metric
     *
     * @return ProductValue
     */
    public function setMetric(MetricInterface $metric);

    /**
     * Add price (removing the older one)
     *
     * @param ProductPriceInterface $price
     *
     * @return ProductValue
     */
    public function addPrice(ProductPriceInterface $price);

    /**
     * Get varchar data
     *
     * @return string
     */
    public function getVarchar();

    /**
     * Remove price
     *
     * @param ProductPriceInterface $price
     *
     * @return ProductValue
     */
    public function removePrice(ProductPriceInterface $price);

    /**
     * Check if the value contains data
     *
     * @return bool
     */
    public function hasData();

    /**
     * Set used scope
     *
     * @param string $scope
     */
    public function setScope($scope);

    /**
     * Remove an option
     *
     * @param AttributeOption $option
     *
     * @return ProductValue
     */
    public function removeOption(AttributeOption $option);

    /**
     * Get text data
     *
     * @return string
     */
    public function getText();

    /**
     * Set data
     *
     * @param mixed $data
     *
     * @return ProductValueInterface
     */
    public function setData($data);

    /**
     * Set option, used for simple select to set single option
     *
     * @param AttributeOption $option
     *
     * @return ProductValueInterface
     */
    public function setOption(AttributeOption $option = null);

    /**
     * Get the price matching the given currency
     *
     * @param string $currency
     *
     * @return null|ProductPriceInterface
     */
    public function getPrice($currency);

    /**
     * Set date data
     *
     * @param \Datetime $date
     *
     * @return ProductValueInterface
     */
    public function setDate($date);

    /**
     * Add data
     *
     * @param mixed $data
     *
     * @return ProductValueInterface
     */
    public function addData($data);

    /**
     * Set used locale
     *
     * @param string $locale
     */
    public function setLocale($locale);

    /**
     * @return boolean
     */
    public function isRemovable();

    /**
     * Get used scope
     *
     * @return string $scope
     */
    public function getScope();

    /**
     * Get datetime data
     *
     * @return \Datetime
     */
    public function getDatetime();

    /**
     * Set entity
     *
     * @param ProductInterface $entity
     *
     * @return ProductValueInterface
     */
    public function setEntity(ProductInterface $entity = null);

    /**
     * Get integer data
     *
     * @return integer
     */
    public function getInteger();

    /**
     * @return string
     */
    public function __toString();
}
