<?php

namespace Context\Page\Attribute;

/**
 * @author    Gildas Quéméner <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Creation
{
    protected $path = '/enrich/product-attribute/edit/{id}';

    public function setPosition($position)
    {
        $this->fillField('Position', $position);

        return $this;
    }
}
