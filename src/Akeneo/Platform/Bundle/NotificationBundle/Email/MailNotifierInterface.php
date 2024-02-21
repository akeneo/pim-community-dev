<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\NotificationBundle\Email;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MailNotifierInterface
{
    /**
     * @param string[] $recipients
     */
    public function notify(
        array $recipients,
        string $subject,
        string $txtBody,
        string $htmlBody,
        array $options = []
    ): void;
}
