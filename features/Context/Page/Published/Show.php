<?php

namespace Context\Page\Published;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
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
    protected $path = '/workflow/published-product/{id}';
}
