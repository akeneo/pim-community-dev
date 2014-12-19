<?php

namespace Pim\Bundle\CatalogBundle\Model;

/**
 * Product template model, aims to store common product values for different products
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
     */
    public function setValuesData($valuesData);

    /**
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValueInterface[]
     */
    public function getValues();

    /**
     * @param \Pim\Bundle\CatalogBundle\Model\ProductValueInterface[] $values
     */
    public function setValues($values);
}
