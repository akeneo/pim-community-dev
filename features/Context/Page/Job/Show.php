<?php

namespace Context\Page\Job;

use Context\Page\Base\Index as BaseIndex;

/**
 * Job show page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Show extends BaseIndex
{
    /**
     * @param string $property
     *
     * @return string
     */
    public function getPropertyErrorMessage($property)
    {
        $error = $this->find('css', sprintf('tr:contains("%s") span.label-important', ucfirst($property)));

        if (!$error) {
            throw new \InvalidArgumentException(sprintf('Could not find the %s property', $property));
        }

        return $error->getText();
    }
}
