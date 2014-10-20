<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Filter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeFilterInterface extends FilterInterface
{
    /**
     * Add an attribute to filter
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string            $operator  the used operator
     * @param string|array      $value     the value(s) to filter
     * @param array             $context   the filter context, used for locale and scope
     *
     * @return AttributeFilterInterface
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value, $context = []);

    /**
     * This filter supports the attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return boolean
     */
    public function supportsAttribute(AbstractAttribute $attribute);
}
