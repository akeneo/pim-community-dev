<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Test\Integration\Persistence\Dbal\Repository;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Write\ViewedAnnouncement;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\Dbal\Repository\DbalViewedAnnouncementRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

class DbalViewedAnnouncementRepositoryIntegration extends TestCase
{
    /** @var DbalConnection */
    private $dbalConnection;

    /** @var DbalViewedAnnouncementRepository */
    private $repository;

    /** @var array */
    private $userFromDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->repository = $this->get('akeneo_communication_channel.repository.dbal.viewed_announcement');
        $this->userFromDb = $this->selectUserFromDb();
    }

    public function test_it_creates_viewed_announcements_by_the_user()
    {
        $viewedAnnouncements = $this->createViewedAnnouncements(['announcement_id_1', 'announcement_id_2']);

        $this->repository->create($viewedAnnouncements);

        $viewedAnnouncementsFromDb = $this->selectViewedAnnouncementByUserFromDb((int) $this->userFromDb['id']);

        Assert::assertSame('announcement_id_1', $viewedAnnouncementsFromDb[0]['announcement_id']);
        Assert::assertSame($this->userFromDb['id'], $viewedAnnouncementsFromDb[0]['user_id']);
        Assert::assertSame('announcement_id_2', $viewedAnnouncementsFromDb[1]['announcement_id']);
        Assert::assertSame($this->userFromDb['id'], $viewedAnnouncementsFromDb[1]['user_id']);
    }

    public function test_it_updates_when_the_viewed_announcement_already_exists()
    {
        $viewedAnnouncements = $this->createViewedAnnouncements(['announcement_id_1', 'announcement_id_2']);

        $this->repository->create($viewedAnnouncements);

        $duplicateViewedAnnouncements = $this->createViewedAnnouncements(['announcement_id_1']);

        $this->repository->create($duplicateViewedAnnouncements);

        $viewedAnnouncementFromDb = $this->selectViewedAnnouncementByUserFromDb((int) $this->userFromDb['id']);

        Assert::assertSame('announcement_id_1', $viewedAnnouncementFromDb[0]['announcement_id']);
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

        return $statement->fetchAssociative();
    }

    private function selectViewedAnnouncementByUserFromDb(int $userId)
    {
        $query = <<<SQL
    SELECT user_id, announcement_id
    FROM akeneo_communication_channel_viewed_announcements
    WHERE user_id = :userId
SQL;
        $statement = $this->dbalConnection->executeQuery($query, ['userId' => $userId]);

        return $statement->fetchAllAssociative();
    }
}
