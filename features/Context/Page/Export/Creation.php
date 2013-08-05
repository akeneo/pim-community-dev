<?php

namespace Context\Page\Export;

use Context\Page\Job\JobCreation;

/**
 * Export creation page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends JobCreation
{
    /**
     * @var string $path
     */
    protected $path = '/ie/export/create';
}
