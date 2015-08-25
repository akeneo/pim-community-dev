<?php

namespace Pim\Bundle\NotificationBundle\Email;

/**
 * Interface for the notifiers
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface NotifierInterface
{
    /**
     * Notify the user
     *
     * @param array  $users
     * @param string $subject
     * @param string $txtBody
     * @param null   $htmlBody
     * @param array  $options
     */
    public function notify(array $users, $subject, $txtBody, $htmlBody = null, array $options = []);
}
