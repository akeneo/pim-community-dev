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

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\UserNotification\NotifyUserAboutMissingMapping;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class NotifyUserAboutMissingMappingSpec extends ObjectBehavior
{
    public function let(SimpleFactoryInterface $notificationFactory, NotifierInterface $notifier): void
    {
        $this->beConstructedWith($notificationFactory, $notifier);
    }

    public function it_is_a_user_notifier(): void
    {
        $this->shouldHaveType(NotifyUserAboutMissingMapping::class);
    }

    public function it_notify_a_user_about_missing_mapping_for_a_family($notificationFactory, $notifier): void
    {
        $family = new Family();
        $family->setCode('family_code');
        $family->setLocale('en_US')->setLabel('Family label');

        $user = new User();
        $user->setUsername('username');
        $user->setUiLocale((new Locale())->setCode('en_US'));

        $notification = new Notification();
        $notification
            ->setType('success')
            ->setMessage('akeneo_franklin_insights.entity.attributes_mapping.notification.new_attributes_to_map')
            ->setMessageParams(['familyLabel' => 'Family Label'])
            ->setRoute('akeneo_franklin_insights_attributes_mapping_edit')
            ->setRouteParams(['familyCode' => 'family_code'])
            ->setContext(['actionType' => 'franklin_insights']);

        $notificationFactory->create()->willReturn($notification);

        $notifier->notify($notification, ['username'])->shouldBeCalled();

        $this->forFamily($user, $family);
    }
}
