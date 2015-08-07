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
     * @return string
     */
    public function getChoiceValue();

    /**
     * @return string
     */
    public function getChoiceLabel();
}
