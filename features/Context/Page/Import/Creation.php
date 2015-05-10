<?php

namespace Context\Page\Import;

use Context\Page\Job\Creation as JobCreation;

/**
 * Import creation page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends JobCreation
{
    /**
     * @var string
     */
    protected $path = '#/collect/import/create';
}
