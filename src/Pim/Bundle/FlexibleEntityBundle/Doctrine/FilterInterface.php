<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Filter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterInterface
{
    /**
     * Add an attribute to filter
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string|array      $operator  the used operator
     * @param string|array      $value     the value(s) to filter
     *
     * @return FilterInterface
     */
    public function add(AbstractAttribute $attribute, $operator, $value);
}
