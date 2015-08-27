<?php

namespace Context\Page\Asset\Category;

use Context\Page\Category\CategoryView;

/**
 * Asset category tree edit page
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class Edit extends CategoryView
{
    /** @var string */
    protected $path = '/enrich/asset-category-tree/{id}/edit';
}
