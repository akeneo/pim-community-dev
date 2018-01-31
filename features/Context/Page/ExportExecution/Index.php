<?php

namespace Context\Page\ExportExecution;

use Context\Page\JobExecution\Index as JobIndex;

/**
 * Export execution index page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends JobIndex
{
    /**
     * @var string
     */
    protected $path = '#/spread/export_execution/';
}
