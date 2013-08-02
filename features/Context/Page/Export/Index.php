<?php

namespace Context\Page\Export;

use Context\Page\Base\Grid;

/**
 * Export index page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    protected $path = '/ie/export/';

    /**
     * @param string $exportLink
     */
    public function clickExportCreationLink($exportLink)
    {
        $this->clickLink('New export');
        $this->clickLink($exportLink);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->getPath();
    }
}
