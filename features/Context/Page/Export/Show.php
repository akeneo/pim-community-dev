<?php

namespace Context\Page\Export;

use Context\Page\Base\Index as BaseIndex;

/**
 * Export show page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Show extends BaseIndex
{
    protected $path = '/ie/export/{id}';

    /**
     * @param Job $job
     *
     * @return string
     */
    public function getUrl($job)
    {
        return str_replace('{id}', $job->getId(), $this->getPath());
    }

    /**
     * @param string $property
     *
     * @return string
     */
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

    /**
     * Click the job execution link
     */
    public function execute()
    {
        $this->clickLink('Export now');
    }
}
