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
class Index extends Grid
{
    protected $path = '/configuration/locale/';

    public function findActivatedLocale($locale)
    {
        return $this->getGridRow($locale)
                    ->find('css', 'input[type=checkbox][checked][disabled]');
    }

    public function findDeactivatedLocale($locale)
    {
        return $this->getGridRow($locale)
                    ->find('css', 'input[type=checkbox][unchecked][disabled]');
    }

    public function clickNewLocaleLink()
    {
        $this->clickLink('New locale');
    }

    public function getUrl()
    {
        return $this->getPath();
    }
}
