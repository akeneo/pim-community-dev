<?php

namespace Pim\Bundle\CatalogBundle\Model;

/**
 * Abstract price backend type entity
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductPriceInterface
{
    /**
     * Get value
     *
     * @return ProductValueInterface
     */
    public function getValue();

    /**
     * To string
     *
     * @return string
     */
    public function __toString();

    /**
     * Get id
     *
     * @return int|string
     */
    public function getId();

    /**
     * Set id
     *
     * @param int|string $id
     *
     * @return ProductPriceInterface
     */
    public function setId($id);

    /**
     * Get data
     *
     * @return float
     */
    public function getData();

    /**
     * Set value
     *
     * @param ProductValueInterface $value
     *
     * @return ProductPriceInterface
     */
    public function setValue(ProductValueInterface $value);

    /**
     * Set used currency
     *
     * @param string $currency
     *
     * @return ProductPriceInterface
     */
    public function setCurrency($currency);

    /**
     * Set data
     *
     * @param float $data
     *
     * @return ProductPriceInterface
     */
    public function setData($data);

    /**
     * Get used currency
     *
     * @return string $currency
     */
    public function getCurrency();
}
