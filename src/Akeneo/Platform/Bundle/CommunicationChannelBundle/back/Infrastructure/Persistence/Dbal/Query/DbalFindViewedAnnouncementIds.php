<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindViewedAnnouncementIdsInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\FetchMode;

/**
 * @author    Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalFindViewedAnnouncementIds implements FindViewedAnnouncementIdsInterface
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function byUserId(int $userId): array
    {
        $query = <<<SQL
            SELECT announcement_id
            FROM akeneo_communication_channel_viewed_announcements
            WHERE user_id = :user_id
        SQL;

        $statement = $this->dbalConnection->executeQuery(
            $query,
            [
                'user_id' => $userId
            ]
        );
        $results = $statement->fetchFirstColumn();
        $statement->closeCursor();

        if (!$results) {
            return [];
        }

        return $results;
    }
}
