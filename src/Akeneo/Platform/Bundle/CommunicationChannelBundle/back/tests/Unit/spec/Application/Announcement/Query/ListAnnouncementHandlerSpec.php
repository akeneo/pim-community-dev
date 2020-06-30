<?php

declare(strict_types=1);

namespace spec\Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementHandler;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementQuery;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;
use Akeneo\Platform\VersionProviderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ListAnnouncementsHandlerSpec extends ObjectBehavior
{
    public function let(
        VersionProviderInterface $versionProvider,
        FindAnnouncementItemsInterface $findAnnouncementItems
    ): void {
        $this->beConstructedWith($versionProvider, $findAnnouncementItems);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ListAnnouncementHandler::class);
    }

    public function it_handles_the_list_announcements_query_paginated($versionProvider, $findAnnouncementItems): void
    {
        $expectedAnnouncements = [
            [
                'id' => 'update-easily_monitor_errors_on_your_connections-2020-06-04',
                'title' => 'Easily monitor errors on your connections',
                'description' => 'For each of your connections, a new `Monitoring` page now lists the last integration errors that may have occurred.',
                'img' => '/bundles/akeneocommunicationchannel/images/announcements/new-connection-monitoring-page.png',
                'altImg' => 'Connection monitoring page',
                'link' => 'https://help.akeneo.com/pim/serenity/updates/2020-05.html#easily-monitor-errors-on-your-connections',
                'startDate' => '2020/06/04',
                'endDate' => '2020/12/31',
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
                'startDate' => '2020/06/04',
                'endDate' => '2020/12/31',
                'notificationDuration' => 14,
                'tags' => [
                    'updates'
                ]
            ],
        ];
        $expectedAnnouncementsItems = $this->createAnnouncementItems($expectedAnnouncements);
        $versionProvider->getEdition()->willReturn('EE');
        $versionProvider->getPatch()->willReturn('4.0');
        $findAnnouncementItems->byPimVersion(
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('string'),
            Argument::type('int')
        )->willReturn([$expectedAnnouncementsItems[1]]);

        $query = new ListAnnouncementQuery('f68a21bb-ec9a-4009-9b0b-2639c6798e5f', 1);
        $this->execute($query)->shouldReturn([$expectedAnnouncementsItems[1]]);
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
}
