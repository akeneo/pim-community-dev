<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\UserNotification;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Notifier\InvalidTokenNotifierInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class NotifyUserAboutInvalidToken implements InvalidTokenNotifierInterface
{
    private $notificationFactory;

    private $notifier;

    private $userContext;

    public function __construct(
        SimpleFactoryInterface $notificationFactory,
        NotifierInterface $notifier,
        UserContext $userContext
    ) {
        $this->notificationFactory = $notificationFactory;
        $this->notifier = $notifier;
        $this->userContext = $userContext;
    }

    public function notify(): void
    {
        $notification = $this->notificationFactory->create();
        $notification
            ->setType('success')
            ->setMessage('akeneo_franklin_insights.notification.invalid_token')
            ->setContext(['actionType' => 'franklin_insights']);

        $this->notifier->notify($notification, [$this->userContext->getUser()->getUsername()]);
    }
}
