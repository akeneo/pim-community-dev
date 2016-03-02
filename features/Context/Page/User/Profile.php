<?php

namespace Context\Page\User;

use Context\Page\Base\Base;

/**
 * "My Account" page
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Profile extends Base
{
    /** @var string */
    protected $path = '/user/profile/view';

    /**
     * Retrieve user API key
     *
     * @throws \Exception
     *
     * @return null|string
     */
    public function getApiKey()
    {
        $generateButton = $this->find('css', '#btn-apigen');
        $apiKey         = null;

        if ($generateButton) {
            $apiKey = $generateButton->getParent()->find('css', 'label')->getHtml();
            return $apiKey;
        }

        throw new \Exception('Could not find the Generate API key button');
    }
}
