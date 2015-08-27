<?php

namespace Context\Page\Asset\Category\Node;

use Context\Page\Asset\Category\Tree\Creation as TreeCreation;

/**
 * Asset category node creation page
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class Creation extends TreeCreation
{
    /** @var string */
    protected $path = '/enrich/asset-category-tree/create/{id}';
}
