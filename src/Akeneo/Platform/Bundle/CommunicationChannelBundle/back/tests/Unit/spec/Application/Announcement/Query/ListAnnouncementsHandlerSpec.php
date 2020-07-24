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
        $announcementItems = $this->getAnnouncements(new \DateTimeImmutable('2020-01-01'), new \DateTimeImmutable('2020-01-02'));
        $findAnnouncementItems->byPimVersion(
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('string')
        )->willReturn([$announcementItems[1]]);

        $query = new ListAnnouncementsQuery('EE', '4.0', 1, 'f68a21bb-ec9a-4009-9b0b-2639c6798e5f');
        $this->execute($query)->shouldReturn([$announcementItems[1]]);
    }

    public function it_notifies_new_announcements_when_user_has_not_seen_it($findAnnouncementItems)
    {
        $startDate = new \DateTimeImmutable();
        $endDate = new \DateTimeImmutable('tomorrow');
        $announcementItems = $this->getAnnouncements($startDate, $endDate);
        $findAnnouncementItems->byPimVersion(
            Argument::type('string'),
            Argument::type('string'),
            null
        )->willReturn($announcementItems);
        $this->viewedAnnouncementsRepository->dataRows[] = ['announcement_id' => 'update-easily_monitor_errors_on_your_connections-2020-06-04', 'user_id' => 1];

        $query = new ListAnnouncementsQuery('EE', '4.0', 1, null);

        $this->execute($query)->shouldBeLike([
            new AnnouncementItem(
                'update-easily_monitor_errors_on_your_connections-2020-06-04',
                'Easily monitor errors on your connections',
                'For each of your connections, a new `Monitoring` page now lists the last integration errors that may have occurred.',
                '/bundles/akeneocommunicationchannel/images/announcements/new-connection-monitoring-page.png',
                'Connection monitoring page',
                'https://help.akeneo.com/pim/serenity/updates/2020-05.html#easily-monitor-errors-on-your-connections',
                $startDate,
                $endDate,
                ['updates']
            ),
            new AnnouncementItem(
                'update-new_metrics_on_the_connection_dashboard-2020-06-04',
                'New metrics on the Connection dashboard',
                'The Connection dashboard now displays additional information to ease error monitoring and allow you to see at a glance how your source connections are performing.',
                null,
                null,
                'https://help.akeneo.com/pim/serenity/updates/2020-05.html#new-metrics-on-the-connection-dashboard',
                $startDate,
                $endDate,
                ['updates', 'new']
            )
        ]);
    }

    private function getAnnouncements(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return [
            new AnnouncementItem(
                'update-easily_monitor_errors_on_your_connections-2020-06-04',
                'Easily monitor errors on your connections',
                'For each of your connections, a new `Monitoring` page now lists the last integration errors that may have occurred.',
                '/bundles/akeneocommunicationchannel/images/announcements/new-connection-monitoring-page.png',
                'Connection monitoring page',
                'https://help.akeneo.com/pim/serenity/updates/2020-05.html#easily-monitor-errors-on-your-connections',
                $startDate,
                $endDate,
                ['updates']
            ),
            new AnnouncementItem(
                'update-new_metrics_on_the_connection_dashboard-2020-06-04',
                'New metrics on the Connection dashboard',
                'The Connection dashboard now displays additional information to ease error monitoring and allow you to see at a glance how your source connections are performing.',
                null,
                null,
                'https://help.akeneo.com/pim/serenity/updates/2020-05.html#new-metrics-on-the-connection-dashboard',
                $startDate,
                $endDate,
                ['updates']
            )
        ];
    }
}
