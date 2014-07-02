<?php

namespace Pim\Bundle\CatalogBundle\Model;

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
     * Get attribute
     *
     * @return AbstractAttribute
     */
    public function getAttribute();

    /**
     * Get entity
     *
     * @return ProductInterface
     */
    public function getEntity();

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Set value data
     *
     * @param mixed $data
     *
     * @return ProductValueInterface
     */
    public function setData($data);

    /**
     * @return string
     */
    public function __toString();
}
