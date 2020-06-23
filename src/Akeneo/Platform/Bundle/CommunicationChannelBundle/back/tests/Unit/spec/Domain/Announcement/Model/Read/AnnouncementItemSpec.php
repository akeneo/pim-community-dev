<?php

declare(strict_types=1);

namespace spec\Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use DateTimeImmutable;
use PhpSpec\ObjectBehavior;

class AnnouncementItemSpec extends ObjectBehavior
{
    /** @var DateTimeImmutable */
    private $startDate;

    public function let(): void
    {
        $this->startDate = new DateTimeImmutable();

        $this->beConstructedWith(
            sprintf('update-title-%s', $this->startDate->format('YYYY-MM-dd')),
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            $this->startDate,
            14,
            [
                'updates'
            ]
        );
    }
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AnnouncementItem::class);
    }

    public function it_normalizes_itself(): void
    {
        $this->toArray()->shouldReturn([
            'id' => sprintf('update-title-%s', $this->startDate->format('YYYY-MM-dd')),
            'title' => 'Title',
            'description' => 'Description',
            'img' => '/images/announcements/new-connection-monitoring-page.png',
            'altImg' => 'Connection monitoring page',
            'link' => 'http://link.com#easily-monitor-errors-on-your-connections',
            'startDate' => $this->startDate->format('F\, jS Y'),
            'tags' => ['new', 'updates'],
        ]);
    }
}
