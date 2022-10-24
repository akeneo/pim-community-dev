<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Platform\Acceptance\NotificationBundle\FakeService;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Webmozart\Assert\Assert;

final class FakeNotifier implements NotifierInterface
{

    private $notificationSent = [];

    public function notify(NotificationInterface $notification, array $users)
    {
        foreach ($users as $user) {
            $this->notificationSent[$user][] = $notification->getMessage();
        }
    }

    public function assertNotificationHaveBeenSent(string $userName, string $message): void
    {
        Assert::notEmpty($this->notificationSent, 'No notification have been sent');
        Assert::keyExists($this->notificationSent, $userName, 'No notification have been sent to "%s"');

        $notificationSentSubject = $this->notificationSent[$userName];
        Assert::inArray(
            $message,
            $notificationSentSubject,
            sprintf(
                'No notification has been sent to "%s" with message "%s". Got "%s"',
                $userName,
                $message,
                implode(', ', $notificationSentSubject)
            ),
        );
    }

    public function assertNotificationHasNotBeenSent(): void
    {
        Assert::isEmpty(
            $this->notificationSent,
            sprintf('A notification have been sent to %s', implode(',', array_keys($this->notificationSent)))
        );
    }
}
