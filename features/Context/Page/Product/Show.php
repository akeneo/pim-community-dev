<?php

namespace Context\Page\Product;

use Context\Page\Product\Edit;

/**
 * Show product page
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Show extends Edit
{
    /**
     * @var string
     */
    protected $path = '/enrich/product/{id}';
}
