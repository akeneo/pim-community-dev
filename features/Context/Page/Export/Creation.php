<?php

namespace Context\Page\Export;

use Context\Page\Job\Creation as JobCreation;

/**
 * Export creation page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends JobCreation
{
    /**
     * @var string
     */
    protected $path = '#/spread/export/create';
}
