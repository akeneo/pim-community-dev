<?php

declare(strict_types=1);

namespace spec\Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\ListAnnouncementsQuery;
use PhpSpec\ObjectBehavior;

class ListAnnouncementsQuerySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('c7c7d1f4-ed60-46e0-aba5-fa493f6dd487', 5);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ListAnnouncementsQuery::class);
    }

    public function it_returns_the_search_after_id()
    {
        $this->searchAfter()->shouldReturn('c7c7d1f4-ed60-46e0-aba5-fa493f6dd487');
    }

    public function it_returns_the_limit()
    {
        $this->limit()->shouldReturn(5);
    }

    public function it_returns_null_if_there_is_no_search_after_id()
    {
        $this->beConstructedWith(null, 5);

        $this->searchAfter()->shouldReturn(null);
    }
}
