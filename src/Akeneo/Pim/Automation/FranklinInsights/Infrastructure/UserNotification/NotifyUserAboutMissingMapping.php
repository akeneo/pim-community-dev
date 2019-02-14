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

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * Notifies a user there is new pending attributes in the attribute mapping of a given family.
 * The notification message will be displayed in the UI locale of the user.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class NotifyUserAboutMissingMapping
{
    /** @var SimpleFactoryInterface */
    private $notificationFactory;

    /** @var NotifierInterface */
    private $notifier;

    /**
     * @param SimpleFactoryInterface $notificationFactory
     * @param NotifierInterface $notifier
     */
    public function __construct(SimpleFactoryInterface $notificationFactory, NotifierInterface $notifier)
    {
        $this->notificationFactory = $notificationFactory;
        $this->notifier = $notifier;
    }

    /**
     * @param UserInterface $user
     * @param FamilyInterface $family
     */
    public function forFamily(UserInterface $user, FamilyInterface $family): void
    {
        $family->setLocale($user->getUiLocale()->getCode());

        $notification = $this->notificationFactory->create();
        $notification
            ->setType('success')
            ->setMessage('akeneo_franklin_insights.entity.attributes_mapping.notification.new_attributes_to_map')
            ->setMessageParams(['%familyLabel%' => $family->getLabel()])
            ->setRoute('akeneo_franklin_insights_attributes_mapping_edit')
            ->setRouteParams(['familyCode' => $family->getCode()])
            ->setContext(['actionType' => 'robot']);

        $this->notifier->notify($notification, [$user->getUsername()]);
    }
}
