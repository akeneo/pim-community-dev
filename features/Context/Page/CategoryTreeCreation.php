<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Category tree creation page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeCreation extends Page
{
    protected $path = '/enrich/category-tree/create';

    /**
     * Save the categoy tree
     */
    public function save()
    {
        $this->pressButton('Save');
    }
}
