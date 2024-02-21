<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Test\Integration\Delivery\InternalApi\Announcement;

use Akeneo\Platform\CommunicationChannel\Test\Integration\WebTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class AddViewedAnnouncementsActionIntegration extends WebTestCase
{
    /** @var UserInterface */
    private $user;

    public function setUp(): void
    {
        parent::setup();

        $this->user = $this->authenticateAsAdmin();
    }

    public function test_it_can_add_viewed_announcements()
    {
        $viewedAnnouncementIds = ['update_1-easily-monitor-errors-on-your-connections_2020-06-04', 'update_2-new-metric-on-the-connection-dashboard_2020-06-04'];
        $this->client->request(
            'POST',
            '/rest/viewed_announcements/add',
            [
                'viewed_announcement_ids' => $viewedAnnouncementIds
            ]
        );

        Assert::assertSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        $this->assertViewedAnnouncement($viewedAnnouncementIds);
    }

    public function test_it_throws_an_exception_when_it_does_not_have_a_view_announcement_ids()
    {
        $viewedAnnouncementIds = ['update_1-easily-monitor-errors-on-your-connections_2020-06-04', 'update_2-new-metric-on-the-connection-dashboard_2020-06-04'];
        $this->client->request(
            'POST',
            '/rest/viewed_announcements/add',
            []
        );

        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertViewedAnnouncement(array $viewedAnnouncementIds): void
    {
        $viewedAnnouncementRepository = $this->get('akeneo_communication_channel.repository.in_memory.viewed_announcement');
        $viewedAnnouncements = [];
        foreach ($viewedAnnouncementIds as $announcementId) {
            $viewedAnnouncements[] = [
                'announcement_id' => $announcementId,
                'user_id' => $this->user->getId()
            ];
        }

        Assert::assertSame($viewedAnnouncements, $viewedAnnouncementRepository->dataRows);
    }
}
