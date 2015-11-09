<?php

namespace Context\Page\JobTracker;

use Context\Page\Base\Grid;

/**
 * Job tracker index page
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    /** @var string */
    protected $path = '#/job/';
}
