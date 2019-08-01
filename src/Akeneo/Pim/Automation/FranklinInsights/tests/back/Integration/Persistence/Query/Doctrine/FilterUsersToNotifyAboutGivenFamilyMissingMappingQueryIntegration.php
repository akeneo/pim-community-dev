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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\FilterUsersToNotifyAboutGivenFamilyMissingMappingQuery;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Types\Type;

/**
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class FilterUsersToNotifyAboutGivenFamilyMissingMappingQueryIntegration extends TestCase
{
    public function test_that_it_filters_users_that_have_not_been_already_notified_today_or_did_not_view_it()
    {
        $this->initialize();
        $filter = $this->getFilterUsersToNotifyQuery();
        $filteredUsers = $filter->filter(
            new FamilyCode('socks'),
            [
                $this->getUserId('admin'),
                $this->getUserId('julia'),
                $this->getUserId('mary'),
                $this->getUserId('kevin')
            ]
        );

        $this->assertEquals([$this->getUserId('mary')], $filteredUsers);
    }

    private function initialize()
    {
        $admin = $this->getUser('admin');
        $julia = $this->getUser('julia');
        $mary = $this->getUser('mary');
        $kevin = $this->getUser('kevin');

        $notificationNow = $this->insertNotification('now');
        $this->insertUserNotification($notificationNow, $admin, true);
        $this->insertUserNotification($notificationNow, $julia, false);


        $notificationBefore = $this->insertNotification('2 days ago');
        $this->insertUserNotification($notificationBefore, $mary, true);
        $this->insertUserNotification($notificationBefore, $kevin, false);
        $this->insertUserNotification($notificationBefore, $admin, true);
    }

    private function insertUserNotification(Notification $notification, UserInterface $user, bool $viewed)
    {
        $query = <<<SQL
INSERT INTO pim_notification_user_notification (notification, user, viewed)
VALUES (:notification, :user, :viewed)
SQL;

        $queryParameters = [
            'notification' => $notification->getId(),
            'user' => $user->getId(),
            'viewed' => $viewed,
        ];
        $types = [
            'notification' => Type::INTEGER,
            'user' => Type::INTEGER,
            'viewed' => Type::BOOLEAN,
        ];

        $this->get('doctrine.orm.entity_manager')->getConnection()->executeUpdate($query, $queryParameters, $types);
    }

    private function insertNotification(string $datetime): ?Notification
    {
        $key = microtime();
        $query = <<<SQL
INSERT INTO pim_notification_notification (route, routeParams, message, messageParams, created, type, context)
VALUES (:route, :routeParams, :message, :messageParams, :created, :type, :context)
SQL;

        $queryParameters = [
            'route' => $key,
            'routeParams' => serialize(['familyCode' => 'socks']),
            'message' => 'akeneo_franklin_insights.entity.attributes_mapping.notification.new_attributes_to_map',
            'messageParams' => serialize(['familyLabel' => 'Chaussettes']),
            'created' => new \DateTime($datetime),
            'type' => 'add',
            'context' => serialize(['actionType' => 'franklin_insights', 'familyCode' => 'socks']),
        ];
        $types = [
            'route' => Type::STRING,
            'routeParams' => Type::STRING,
            'message' => Type::STRING,
            'messageParams' => Type::STRING,
            'created' => Type::DATETIME,
            'type' => Type::STRING,
            'context' => Type::STRING,
        ];

        $this->get('doctrine.orm.entity_manager')->getConnection()->executeUpdate($query, $queryParameters, $types);
        $notificationRepository = $this->get('doctrine.orm.entity_manager')->getRepository(Notification::class);

        return $notificationRepository->findOneBy(['route' => $key]);
    }

    /**
     * @param string $username
     *
     * @return UserInterface
     */
    private function getUser(string $username): UserInterface
    {
        return $this->get('pim_user.provider.user')->loadUserByUsername($username);
    }

    /**
     * @param string $username
     *
     * @return int
     */
    private function getUserId(string $username): int
    {
        return $this->get('pim_user.provider.user')->loadUserByUsername($username)->getId();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getFilterUsersToNotifyQuery(): FilterUsersToNotifyAboutGivenFamilyMissingMappingQuery
    {
        return $this->get('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.filter_users_to_notify_about_given_family_missing_mapping');
    }
}
