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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\UserNotification;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\UserNotification\NotifyUserAboutInvalidToken;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class NotifyUserAboutInvalidTokenSpec extends ObjectBehavior
{
    public function let(
        SimpleFactoryInterface $notificationFactory,
        NotifierInterface $notifier,
        UserContext $userContext
    ): void {
        $this->beConstructedWith($notificationFactory, $notifier, $userContext);
    }

    public function it_is_a_user_notifier(): void
    {
        $this->shouldHaveType(NotifyUserAboutInvalidToken::class);
    }

    public function it_notifies_a_user_about_token_invalidity(
        $notificationFactory,
        $notifier,
        $userContext,
        UserInterface $user
    ): void {
        $notification = new Notification();
        $notification
            ->setType('error')
            ->setMessage('akeneo_franklin_insights.notification.invalid_token')
            ->setContext(['actionType' => 'franklin_insights']);

        $notificationFactory->create()->willReturn($notification);

        $user->getUsername()->willReturn('username');
        $userContext->getUser()->willReturn($user);

        $notifier->notify($notification, ['username'])->shouldBeCalled();

        $this->notify();
    }
}
