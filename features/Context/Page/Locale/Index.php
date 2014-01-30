<?php

namespace Context\Page\Locale;

use Context\Page\Base\Grid;

/**
 * Behat context page for locale index
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index
{
    use \Context\Page\Base\WithGrid;

    /**
     * @var string $path
     */
    protected $path = '/configuration/locale/';

    public function __construct()
    {
        $this->grid = $this->getElement('Grid');
    }
}
