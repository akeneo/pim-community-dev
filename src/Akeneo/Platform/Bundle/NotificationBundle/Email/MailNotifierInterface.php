<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\NotificationBundle\Email;

use Akeneo\UserManagement\Component\Model\UserInterface;

interface MailNotifierInterface
{
    /**
     * @param UserInterface[] $users
     * For legacy constraint, we did not type the parameters.
     */
    public function notify(array $users, $subject, $txtBody, $htmlBody = null, array $options = []);

    public function notifyByEmail(
        string $recipient,
        string $subject,
        string $txtBody,
        $htmlBody = null,
        array $options = []
    );
}
