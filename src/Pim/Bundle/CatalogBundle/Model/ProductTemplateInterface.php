<?php

namespace Pim\Bundle\CatalogBundle\Model;

/**
 * Product template model, aims to store common product values for different products in order to copy them to products
 * later, used by groups of type variant group, may be used linked to other objects or as standalone template
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductTemplateInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return array
     */
    public function getValuesData();

    /**
     * @param array $valuesData
     *
     * @return ProductTemplateInterface
     */
    public function setValuesData($valuesData);

    /**
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValueInterface[]
     *
     * TODO : not used
     */
    public function getValues();

    /**
     * @param \Pim\Bundle\CatalogBundle\Model\ProductValueInterface[] $values
     *
     * @return ProductTemplateInterface
     *
     * TODO : not used
     */
    public function setValues($values);

    /**
     * @param ProductValueInterface $value
     *
     * @return boolean
     */
    public function hasValue(ProductValueInterface $value);
}
