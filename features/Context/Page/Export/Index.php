<?php

namespace Context\Page\Export;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Export index page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Page
{
    protected $path = '/ie/export/';

    public function clickCreationLink($exportLink)
    {
        $this->openCreationDropdown();
        $this->clickLink($exportLink);
    }

    public function getUrl()
    {
        return $this->getPath();
    }

    private function openCreationDropdown()
    {
        $this->clickLink('New export');
    }
}
