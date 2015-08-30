<?php
namespace Pim\Bundle\NotificationBundle\Update;

/**
 * Generates the url to call to fetch the last available versions from the update server
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpdateUrlGeneratorInterface
{
    /**
     * @return string
     */
    public function generateAvailablePatchsUrl();
}
