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
     * @return null|string
     */
    public function getApiKey()
    {
        $generateButton = $this->spin(function () {
            return $this->find('css', '#btn-apigen');
        }, 'Could not find Generate API Key button.');

        $generateElement = $this->spin(function () use ($generateButton) {
            return $generateButton->getParent()->find('css', 'label');
        }, 'Could not find API Key.');

        return $generateElement->getHtml();
    }
}
