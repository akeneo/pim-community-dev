<?php

namespace Context\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * The export detail page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportDetail extends Page
{
    protected $path = '/ie/export/{id}';

    public function getUrl($job)
    {
        return str_replace('{id}', $job->getId(), $this->getPath());
    }
}

