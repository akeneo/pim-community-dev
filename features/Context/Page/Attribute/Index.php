<?php

namespace Context\Page\Attribute;

use Context\Page\Base\Grid;

/**
 * Attribute index page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    /**
     * @var string
     */
    protected $path = '/configuration/attribute/';

    /**
     * @param string $attribute
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    public function findAttribute($attribute)
    {
        return $this->getRow($attribute);
    }
}
