<?php

declare(strict_types=1);

namespace spec\Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use PhpSpec\ObjectBehavior;

class AnnouncementItemSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            new \DateTimeImmutable('2020/06/04'),
            14,
            [
                'updates'
            ],
            [
                'CE',
                'EE'
            ]
        );
    }
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AnnouncementItem::class);
    }

    public function it_normalizes_itself(): void
    {
        $startDate = new \DateTimeImmutable('2020/06/04');
        $this->toArray()->shouldReturn([
            'title' => 'Title',
            'description' => 'Description',
            'img' => '/images/announcements/new-connection-monitoring-page.png',
            'altImg' => 'Connection monitoring page',
            'link' => 'http://link.com#easily-monitor-errors-on-your-connections',
            'startDate' => $startDate->format('F\, jS Y'),
            'notificationDuration' => 14,
            'tags' => ['new', 'updates'],
            'editions' => ['CE', 'EE'],
        ]);
    }
}
