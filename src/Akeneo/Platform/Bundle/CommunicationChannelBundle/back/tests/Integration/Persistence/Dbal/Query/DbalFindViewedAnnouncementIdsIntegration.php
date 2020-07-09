<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Test\Integration\Persistence\Dbal\Repository;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Write\ViewedAnnouncement;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\Dbal\Query\DbalFindViewedAnnouncementIds;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\Dbal\Repository\DbalViewedAnnouncementRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

class DbalFindViewedAnnouncementIdsIntegration extends TestCase
{
    /** @var DbalConnection */
    private $dbalConnection;

    /** @var DbalViewedAnnouncementRepository */
    private $repository;

    /** @var DbalFindViewedAnnouncementIds */
    private $findViewedAnnouncementIdsQuery;

    /** @var array */
    private $userFromDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->repository = $this->get('akeneo_communication_channel.repository.dbal.viewed_announcement');
        $this->findViewedAnnouncementIdsQuery = $this->get('akeneo_communication_channel.query.dbal.find_viewed_announcement_ids');
        $this->userFromDb = $this->selectUserFromDb();
    }

    public function test_it_can_find_the_viewed_announcements_by_user_id()
    {
        $expectedViewedAnnouncements = $this->createViewedAnnouncements(['announcement_id_1', 'announcement_id_2']);

        $this->repository->create($expectedViewedAnnouncements);

        $viewedAnnouncementIds = $this->findViewedAnnouncementIdsQuery->ByUserId((int) $this->userFromDb['id']);

        Assert::assertEquals(['announcement_id_1', 'announcement_id_2'], $viewedAnnouncementIds);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createViewedAnnouncements(array $viewAnnouncementIds)
    {
        return array_map(function ($viewAnnouncementId) {
            return ViewedAnnouncement::create(
                $viewAnnouncementId,
                (int) $this->userFromDb['id']
            );
        }, $viewAnnouncementIds);
    }

    private function selectUserFromDb()
    {
        $query = <<<SQL
        SELECT id
        FROM oro_user
        WHERE username = 'admin'
SQL;
        $statement = $this->dbalConnection->executeQuery($query);

        return $statement->fetch();
    }
}
