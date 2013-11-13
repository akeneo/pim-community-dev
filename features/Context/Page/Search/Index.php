<?php

namespace Context\Page\Search;

use Context\Page\Base\Base;

/**
 * Search page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Base
{
    /**
     * @var string
     */
    protected $path = '/search/';

    /**
     * {@inheritdoc}
     */
    public function fillField($locator, $value)
    {
        $searchField = $this->getElement('Container')->find('css', 'input#search');
        $searchField->setValue($value);
    }
}
