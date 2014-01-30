<?php

namespace Context\Page\Currency;

use Context\Page\Base\Index as BaseIndex;

/**
 * Currency index page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends BaseIndex
{
    use \Context\Page\Base\WithGrid;

    /**
     * @var string $path
     */
    protected $path = '/configuration/currency/';

    public function __construct()
    {
        $this->grid = $this->getElement('Grid');
    }
}
