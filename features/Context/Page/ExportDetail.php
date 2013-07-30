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

    public function getPropertyErrorMessage($property)
    {
        $error = $this->find('css', sprintf('li:contains("%s") span.label-important', ucfirst($property)));

        if (!$error) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find the %s property', $property
            ));
        }

        return $error->getText();
    }

    public function execute()
    {
        $this->clickLink('Execute');
    }
}

