<?php

namespace Context\Page\Published;

use Context\Page\Product\Index as BaseIndex;

/**
 * Published Product index page
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Index extends BaseIndex
{
    /**
     * @var string
     */
    protected $path = '/workflow/published-product/';
}
