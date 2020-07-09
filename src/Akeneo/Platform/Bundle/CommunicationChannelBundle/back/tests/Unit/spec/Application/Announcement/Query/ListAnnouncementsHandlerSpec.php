<?php

declare(strict_types=1);

namespace spec\Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementsHandler;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementsQuery;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Query\InMemoryFindViewedAnnouncementIds;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Repository\InMemoryViewedAnnouncementRepository;
use DateTimeImmutable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ListAnnouncementsHandlerSpec extends ObjectBehavior
{
    /** @var InMemoryViewedAnnouncementRepository */
    private $viewedAnnouncementsRepository;

    public function let(FindAnnouncementItemsInterface $findAnnouncementItems): void
    {
        $this->viewedAnnouncementsRepository = new InMemoryViewedAnnouncementRepository();
        $findViewedAnnouncementIds = new InMemoryFindViewedAnnouncementIds($this->viewedAnnouncementsRepository);

        $this->beConstructedWith($findAnnouncementItems, $findViewedAnnouncementIds);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ListAnnouncementsHandler::class);
    }

    public function it_handles_the_list_paginated_announcements_query($findAnnouncementItems): void
    {
        $announcements = $this->getAnnouncements();
        $announcementItems = $this->createAnnouncementItems($announcements);
        $findAnnouncementItems->byPimVersion(
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('int')
        )->willReturn([$announcementItems[1]]);

        $query = new ListAnnouncementsQuery('EE', '4.0', 1, 'f68a21bb-ec9a-4009-9b0b-2639c6798e5f', 1);
        $this->execute($query)->shouldReturn([$announcementItems[1]]);
    }

    public function it_can_notifiy_new_announcements_when_user_has_not_seen_it($findAnnouncementItems)
    {
        $announcements = $this->getAnnouncements();
        $announcementsItems = $this->createAnnouncementItems($announcements);
        $findAnnouncementItems->byPimVersion(
            Argument::type('string'),
            Argument::type('string'),
            null,
            Argument::type('int')
        )->willReturn($announcementsItems);
        $this->viewedAnnouncementsRepository->dataRows[] = ['announcement_id' => 'update-easily_monitor_errors_on_your_connections-2020-06-04', 'user_id' => 1];

        $query = new ListAnnouncementsQuery('EE', '4.0', 1, null, 2);
        $expectedAnnouncements = $this->getAnnouncements();
        array_unshift($expectedAnnouncements[1]['tags'], 'new');
        $expectedAnnouncementItems = $this->createAnnouncementItems($expectedAnnouncements);
        $this->execute($query)->shouldBeLike($expectedAnnouncementItems);
    }

    /**
     * @return AnnouncementItems[]
     */
    private function createAnnouncementItems(array $announcements): array
    {
        return array_map(function ($announcement) {
            return new AnnouncementItem(
                $announcement['id'],
                $announcement['title'],
                $announcement['description'],
                $announcement['img'] ?? null,
                $announcement['altImg'] ?? null,
                $announcement['link'],
                new \DateTimeImmutable($announcement['startDate']),
                $announcement['notificationDuration'],
                $announcement['tags'],
            );
        }, $announcements);
    }

    private function getAnnouncements(): array
    {
        $currentStartDate = new DateTimeImmutable();
        return [
            [
                'id' => 'update-easily_monitor_errors_on_your_connections-2020-06-04',
                'title' => 'Easily monitor errors on your connections',
                'description' => 'For each of your connections, a new `Monitoring` page now lists the last integration errors that may have occurred.',
                'img' => '/bundles/akeneocommunicationchannel/images/announcements/new-connection-monitoring-page.png',
                'altImg' => 'Connection monitoring page',
                'link' => 'https://help.akeneo.com/pim/serenity/updates/2020-05.html#easily-monitor-errors-on-your-connections',
                'startDate' => $currentStartDate->format('Y/m/d'),
                'notificationDuration' => 14,
                'tags' => [
                    'updates'
                ]
            ],
            [
                'id' => 'update-new_metrics_on_the_connection_dashboard-2020-06-04',
                'title' => 'New metrics on the Connection dashboard',
                'description' => 'The Connection dashboard now displays additional information to ease error monitoring and allow you to see at a glance how your source connections are performing.',
                'link' => 'https://help.akeneo.com/pim/serenity/updates/2020-05.html#new-metrics-on-the-connection-dashboard',
                'startDate' => $currentStartDate->format('Y/m/d'),
                'notificationDuration' => 7,
                'tags' => [
                    'updates'
                ]
            ],
        ];
    }
}
