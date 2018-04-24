<?php

namespace Context\Page\Import;

use Context\Page\Job\Show as JobShow;

/**
 * Import show page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Show extends JobShow
{
    /**
     * @var string
     */
    protected $path = '#/collect/import/{code}';

    /**
     * Click the job execution link
     */
    public function execute()
    {
        $launchButton = $this->spin(function () {
            return $this->find('css', sprintf('button:contains("%s")', 'Import now'));
        }, 'Cannot find the launch button');

        $launchButton->click();
    }
}
