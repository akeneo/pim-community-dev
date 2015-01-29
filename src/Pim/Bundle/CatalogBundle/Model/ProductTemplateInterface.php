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
    /** @return int */
    public function getId();

    /**
     * This property is not mapped to a column in the database and exists just to
     * be able to bind the normalized values to the form
     *
     * @return ArrayCollection of ProductValueInterface
     */
    public function getValues();

    /**
     * @param ArrayCollection $values Collection of ProductValueInterface
     *
     * @return ProductTemplateInterface
     *
     * TODO (JJ) typehint array
     */
    public function setValues($values);

    /**
     * @return array
     */
    public function getValuesData();

    /**
     * @param array $valuesData
     *
     * @return ProductTemplateInterface
     */
    public function setValuesData(array $valuesData);

    /**
     * @param ProductValueInterface $value
     *
     * @return boolean
     */
    public function hasValue(ProductValueInterface $value);

    /**
     * @param AttributeInterface $attribute
     *
     * @return boolean
     */
    public function hasValueForAttribute(AttributeInterface $attribute);

    /**
     * @return array
     */
    public function getAttributes();
}
