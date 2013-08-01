<?php

namespace Context\Page\Export;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Pim\Bundle\BatchBundle\Entity\Job;

/**
 * Export launch page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Launch extends Page
{
    protected $path = '/ie/export/{id}/launch';
}
