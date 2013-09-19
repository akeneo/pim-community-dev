<?php

namespace Context\Page\Import;

use Context\Page\Job\Index as JobIndex;

/**
 * Import index page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends JobIndex
{
    /**
     * @var string $path
     */
    protected $path = '/collect/import/';

    /**
     * @param string $importLink
     */
    public function clickImportCreationLink($importLink)
    {
        $this->clickLink('New import');
        $this->clickLink($importLink);
    }
}
