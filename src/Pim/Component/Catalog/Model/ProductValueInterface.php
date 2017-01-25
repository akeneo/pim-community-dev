<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;

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
     * Get data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Get related option, used for simple select to set single option
     *
     * @return AttributeOptionInterface
     */
    public function getOption();

    /**
     * Get media
     *
     * @return FileInfoInterface
     */
    public function getMedia();

    /**
     * Get decimal data
     *
     * @return float
     */
    public function getDecimal();

    /**
     * Get boolean data
     *
     * @return bool
     */
    public function getBoolean();

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
     * Get product
     *
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * Get prices
     *
     * @return PriceCollectionInterface
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
     * Get varchar data
     *
     * @return string
     */
    public function getVarchar();

    /**
     * Check if the value contains data
     *
     * @return bool
     */
    public function hasData();

    /**
     * Get text data
     *
     * @return string
     */
    public function getText();

    /**
     * Get the price matching the given currency
     *
     * @param string $currency
     *
     * @return null|ProductPriceInterface
     */
    public function getPrice($currency);

    /**
     * @return bool
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
     * Get integer data
     *
     * @return int
     */
    public function getInteger();

    /**
     * Checks that the product value is equal to another.
     *
     * @param ProductValueInterface $productValue
     *
     * @return bool
     */
    public function isEqual(ProductValueInterface $productValue);

    /**
     * @return string
     */
    public function __toString();
}
