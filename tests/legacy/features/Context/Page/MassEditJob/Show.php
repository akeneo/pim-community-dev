<?php

namespace Context\Page\MassEditJob;

use Context\Page\Job\Show as JobShow;

/**
 * Mass edit job show page
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Show extends JobShow
{
    /** @var string $path */
    protected $path = '#/job/show/{id}';
}
