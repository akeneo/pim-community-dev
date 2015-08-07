<?php

namespace Pim\Bundle\CatalogBundle\Model;

/**
 * Interface defining methods used in ChoicesBuilder to build choices lists
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChosableInterface
{
    /**
     * Return the value that will be used as key in the choices list
     *
     * @return string
     */
    public function getChoiceValue();

    /**
     * Return the value that will be used as label in the choices list
     *
     * @return string
     */
    public function getChoiceLabel();
}
