<?php

namespace Context\Page\Attribute;

/**
 * Attribute edit page
 *
 * @author    Gildas Quéméner <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Creation
{
    protected $path = '/enrich/product-attribute/edit/{id}';

    /**
     * Set the attribute position
     * @param integer $position
     *
     * @return Edit
     */
    public function setPosition($position)
    {
        $this->fillField('Position', $position);

        return $this;
    }
}
