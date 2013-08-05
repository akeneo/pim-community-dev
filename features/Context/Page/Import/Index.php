<?php

namespace Context\Page\Import;

use Context\Page\Export\Index as ExportIndex;

/**
 * Import index page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class Index extends ExportIndex
{
    /**
     * @var string $path
     */
    protected $path = '/ie/import/';
}
