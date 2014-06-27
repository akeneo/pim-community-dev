<?php

namespace Context\Page\Product;

use Context\Page\Base\Form;

/**
 * Show product page
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Show extends Form
{
    /**
     * @var string $path
     */
    protected $path = '/enrich/product/{id}/show';
}
