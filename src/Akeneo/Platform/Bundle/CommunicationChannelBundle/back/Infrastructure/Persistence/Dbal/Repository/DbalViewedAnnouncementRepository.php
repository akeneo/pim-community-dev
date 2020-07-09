<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Repository\ViewedAnnouncementRepositoryInterface;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author    Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalViewedAnnouncementRepository implements ViewedAnnouncementRepositoryInterface
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
    public function create(array $viewedAnnouncements): void
    {
        $values = $parameters = [];
        foreach ($viewedAnnouncements as $index => $viewedAnnouncement) {
            $values[] = <<<SQL
                (:announcement_id_$index, :user_id_$index)
            SQL;
            $parameters['announcement_id_' . $index] = $viewedAnnouncement->announcementId();
            $parameters['user_id_' . $index] = $viewedAnnouncement->userId();
        }

        $valuesQuery = implode(',', $values);
        $insertQuery = <<<SQL
            INSERT INTO akeneo_communication_channel_viewed_announcements
                (announcement_id, user_id)
            VALUES $valuesQuery
            ON DUPLICATE KEY UPDATE announcement_id=announcement_id;
        SQL;


        $this->dbalConnection->executeQuery(
            $insertQuery,
            $parameters
        );
    }
}
