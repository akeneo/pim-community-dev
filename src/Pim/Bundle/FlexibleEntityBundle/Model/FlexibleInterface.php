<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model;

/**
 * Flexible entity interface, allow to define a flexible entity without extends abstract class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FlexibleInterface
{
    /**
     * Add value
     *
     * @param FlexibleValueInterface $value
     *
     * @return FlexibleInterface
     */
    public function addValue(FlexibleValueInterface $value);

    /**
     * Remove value
     *
     * @param FlexibleValueInterface $value
     */
    public function removeValue(FlexibleValueInterface $value);

    /**
     * Get values
     *
     * @return \ArrayAccess
     */
    public function getValues();

    /**
     * Get value related to attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return FlexibleValueInterface
     */
    public function getValue(AbstractAttribute $attribute);
}
