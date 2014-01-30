<?php

namespace Context\Page\Attribute;

use Context\Page\Base\Index as BaseIndex;

/**
 * Attribute index page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends BaseIndex
{
    use \Context\Page\Base\WithGrid;

    /**
     * @var string
     */
    protected $path = '/configuration/attribute/';

    public function __construct()
    {
        $this->grid = $this->getElement('Grid');
    }
}
