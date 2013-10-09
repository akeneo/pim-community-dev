<?php

namespace Context\Page\Export;

use Context\Page\Job\Index as JobIndex;

/**
 * Export index page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends JobIndex
{
    /**
     * @var string $path
     */
    protected $path = '/spread/export/_grid_';

    /**
     * @param string $exportLink
     */
    public function clickExportCreationLink($exportLink)
    {
        $this->clickLink('Create export profile');
        $this->clickLink($exportLink);
    }
}
