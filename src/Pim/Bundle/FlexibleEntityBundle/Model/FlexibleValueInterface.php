<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model;

/**
 * Flexible value interface, allow to define a flexible value without extends abstract class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FlexibleValueInterface
{
    /**
     * Get attribute
     *
     * @return AbstractAttribute
     */
    public function getAttribute();

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Get wether or not value has data
     *
     * @return Boolean
     */
    public function hasData();

    /**
     * Set value data
     *
     * @param mixed $data
     *
     * @return FlexibleValueInterface
     */
    public function setData($data);
}
