<?php

namespace Pim\Component\Catalog\Model;

/**
 * Product unique data consists of data that is unique among all the products for a given attribute.
 * Only pim_catalog_identifier, pim_catalog_number, pim_catalog_text and pim_catalog_date attribute types can be
 * defined as unique.
 *
 * For instance, if the attribute "release date" is defined as unique.
 * Then, the data "05/24/1980" can be used only once among all the products.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductUniqueDataInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * @return AttributeInterface
     */
    public function getAttribute();

    /**
     * @return string
     */
    public function getRawData();

    /**
     * @param ValueInterface $value
     */
    public function setProductValue(ValueInterface $value);

    /**
     * @param ProductUniqueDataInterface $uniqueValue
     *
     * @return bool
     */
    public function isEqual(ProductUniqueDataInterface $uniqueValue);
}
