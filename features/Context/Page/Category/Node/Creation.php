<?php

namespace Context\Page\Category\Node;

use Context\Page\Category\Tree\Creation as TreeCreation;

/**
 * Product category node creation page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends TreeCreation
{
    /**
     * @var string
     */
    protected $path = '#/enrich/product-category-tree/create/{id}';
}
