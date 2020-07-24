<?php

declare(strict_types=1);

namespace spec\Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\HasNewAnnouncementsHandler;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Query\HasNewAnnouncementsQuery;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindNewAnnouncementIdsInterface;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Query\InMemoryFindViewedAnnouncementIds;
use Akeneo\Platform\CommunicationChannel\Infrastructure\Persistence\InMemory\Repository\InMemoryViewedAnnouncementRepository;
use PhpSpec\ObjectBehavior;

class HasNewAnnouncementsHandlerSpec extends ObjectBehavior
{
    /** @var InMemoryViewedAnnouncementRepository */
    private $viewedAnnouncementsRepository;

    public function let(FindNewAnnouncementIdsInterface $findNewAnnouncementIds): void
    {
        $this->viewedAnnouncementsRepository = new InMemoryViewedAnnouncementRepository();
        $findViewedAnnouncementIds = new InMemoryFindViewedAnnouncementIds($this->viewedAnnouncementsRepository);

        $this->beConstructedWith($findNewAnnouncementIds, $findViewedAnnouncementIds);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(HasNewAnnouncementsHandler::class);
    }

    public function it_returns_true_if_it_has_new_announcements_not_seen_by_the_user($findNewAnnouncementIds)
    {
        $userId = 1;
        $edition = 'Serenity';
        $version = '20201015';
        $query = new HasNewAnnouncementsQuery($edition, $version, $userId);
        $this->viewedAnnouncementsRepository->dataRows[] = ['announcement_id' => 'new_announcement_viewed', 'user_id' => $userId];
        $findNewAnnouncementIds->find($edition, $version)->willReturn(['new_announcement_viewed', 'other_new_announcement']);

        $this->execute($query)->shouldReturn(true);
    }

    public function it_returns_false_if_it_has_only_new_announcements_already_seen_by_the_user($findNewAnnouncementIds)
    {
        $userId = 1;
        $edition = 'Serenity';
        $version = '20201015';
        $query = new HasNewAnnouncementsQuery($edition, $version, $userId);
        $this->viewedAnnouncementsRepository->dataRows =
        [
            [
                'announcement_id' => 'new_announcement_viewed',
                'user_id' => $userId
            ],
            [
                'announcement_id' => 'other_new_announcement_viewed',
                'user_id' => $userId
            ],
        ];
        $findNewAnnouncementIds->find($edition, $version)->willReturn(['new_announcement_viewed', 'other_new_announcement_viewed']);

        $this->execute($query)->shouldReturn(false);
    }
}
