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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Doctrine\DBAL\Connection;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FilterUsersToNotifyAboutGivenFamilyMissingMappingQuery
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @params FamilyCode $familyCode
     * @params array $userIds
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    public function filter(FamilyCode $familyCode, array $userIds): array
    {
        $sql = <<<SQL
SELECT user_notif.user as user_id
FROM pim_notification_user_notification as user_notif
LEFT JOIN pim_notification_notification notification on user_notif.notification = notification.id
WHERE user_notif.user IN (:user_ids) AND
    notification.message = 'akeneo_franklin_insights.entity.attributes_mapping.notification.new_attributes_to_map' AND
    notification.context = :context AND
    (user_notif.viewed = 0 OR notification.created >= CURDATE() - INTERVAL 1 DAY);
SQL;
        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'user_ids' => $userIds,
                'context' => serialize(['actionType' => 'franklin_insights', 'familyCode' => (string) $familyCode])
            ],
            [
                'user_ids' => Connection::PARAM_INT_ARRAY,
                'context' => \PDO::PARAM_STR,
            ]
        );
        $doNotNotifyUserIds = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        return array_values(array_diff($userIds, $doNotNotifyUserIds));
    }
}
