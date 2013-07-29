<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Import index page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ImportIndex extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/ie/import/';

    /**
     *
     * @param string $importLink
     */
    public function clickCreationLink($importLink)
    {
        $this->openCreationDropdown();
        $this->clickLink($importLink);
    }

    public function getUrl()
    {
        return $this->getPath();
    }

    protected function openCreationDropdown()
    {
        $this->clickLink('New import');
    }
}
