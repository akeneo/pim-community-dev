<?php

declare(strict_types=1);

namespace spec\Akeneo\Platform\CommunicationChannel\Application\Announcement\Command;

use Akeneo\Platform\CommunicationChannel\Application\Announcement\Command\AddViewedAnnouncementsByUserCommand;
use Akeneo\Platform\CommunicationChannel\Application\Announcement\Command\AddViewedAnnouncementsByUserHandler;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Repository\ViewedAnnouncementRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddViewedAnnouncementsByUserHandlerSpec extends ObjectBehavior
{
    public function let(ViewedAnnouncementRepositoryInterface $viewedAnnouncementRepository): void
    {
        $this->beConstructedWith($viewedAnnouncementRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AddViewedAnnouncementsByUserHandler::class);
    }

    public function it_handles_the_add_viewed_announcements_by_user($viewedAnnouncementRepository): void
    {
        $command = new AddViewedAnnouncementsByUserCommand(
            ['announcement_id_1', 'announcement_id_2'],
            1
        );
        $this->execute($command);

        $viewedAnnouncementRepository->create(Argument::type('array'))->shouldBeCalled();
    }
}
