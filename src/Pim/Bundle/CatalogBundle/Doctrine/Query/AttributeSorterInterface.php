<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Sorter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeSorterInterface extends SorterInterface
{
    /**
     * Sort by attribute value
     *
     * @param AbstractAttribute $attribute the attribute to sort on
     * @param string            $direction the direction to use
     *
     * @return AttributeSorterInterface
     */
    public function addAttributeSorter(AbstractAttribute $attribute, $direction);

    /**
     * This filter supports the attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return boolean
     */
    public function supportsAttribute(AbstractAttribute $attribute);
}
