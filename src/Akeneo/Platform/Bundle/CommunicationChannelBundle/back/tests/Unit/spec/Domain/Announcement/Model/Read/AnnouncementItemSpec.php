<?php

declare(strict_types=1);

namespace spec\Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use DateTimeImmutable;
use PhpSpec\ObjectBehavior;

class AnnouncementItemSpec extends ObjectBehavior
{
    public function it_normalizes_itself(): void
    {
        $this->beConstructedWith(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            new \DateTimeImmutable('2020-10-01'),
            new \DateTimeImmutable('2020-10-06'),
            ['updates']
        );

        $this->shouldBeAnInstanceOf(AnnouncementItem::class);

        $this->toArray()->shouldReturn([
            'id' => 'id',
            'title' => 'Title',
            'description' => 'Description',
            'img' => '/images/announcements/new-connection-monitoring-page.png',
            'altImg' => 'Connection monitoring page',
            'link' => 'http://link.com#easily-monitor-errors-on-your-connections',
            'startDate' => 'October, 1st 2020',
            'tags' => ['updates'],
        ]);
    }

    public function it_adds_new_as_tag_when_we_notify_the_announcement()
    {
        $this->beConstructedWith(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            new \DateTimeImmutable('2020-10-20'),
            new \DateTimeImmutable('2020-10-30'),
            ['updates']
        );

        $this->toNotify()->shouldBeLike(
            new AnnouncementItem(
                'id',
                'Title',
                'Description',
                '/images/announcements/new-connection-monitoring-page.png',
                'Connection monitoring page',
                'http://link.com#easily-monitor-errors-on-your-connections',
                new \DateTimeImmutable('2020-10-20'),
                new \DateTimeImmutable('2020-10-30'),
                ['updates', 'new']
            )
        );
    }

    public function it_should_be_notified_when_the_announcement_is_new_and_not_already_viewed()
    {
        $yesterday = new \DateTimeImmutable('yesterday');
        $tomorrow = new \DateTimeImmutable('tomorrow');

        $this->beConstructedWith(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            $yesterday,
            $tomorrow,
            ['updates']
        );

        $this->callOnWrappedObject('shouldBeNotified', [['id_2']])->shouldReturn(true);
    }

    public function it_should_not_be_notify_when_the_announcement_is_already_viewed()
    {
        $yesterday = new \DateTimeImmutable('yesterday');
        $tomorrow = new \DateTimeImmutable('tomorrow');

        $this->beConstructedWith(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            $yesterday,
            $tomorrow,
            ['updates']
        );

        $this->callOnWrappedObject('shouldBeNotified', [['id', 'id_2']])->shouldReturn(false);
    }

    public function it_should_not_be_notify_when_the_announcement_end_date_is_after_the_current_date()
    {
        $yesterday = new \DateTimeImmutable('yesterday');

        $this->beConstructedWith(
            'id',
            'Title',
            'Description',
            '/images/announcements/new-connection-monitoring-page.png',
            'Connection monitoring page',
            'http://link.com#easily-monitor-errors-on-your-connections',
            $yesterday,
            $yesterday,
            ['updates']
        );

        $this->callOnWrappedObject('shouldBeNotified', [['id_2']])->shouldReturn(false);
    }
}
