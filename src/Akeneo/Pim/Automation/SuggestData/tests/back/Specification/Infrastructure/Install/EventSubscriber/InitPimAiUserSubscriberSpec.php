<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Install\EventSubscriber;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Install\EventSubscriber\InitPimAiUserSubscriber;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony\Command\InitPimAiUserCommand;
use Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitPimAiUserSubscriberSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(InitPimAiUserSubscriber::class);
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_post_load_fixtures_event(): void
    {
        $this::getSubscribedEvents()->shouldHaveKey(InstallerEvents::POST_LOAD_FIXTURES);
    }

    public function it_launches_init_user_command(CommandExecutor $commandExecutor): void
    {
        $commandExecutor->runCommand(
            InitPimAiUserCommand::getDefaultName(),
            [
                '--quiet' => true,
            ]
        )->shouldBeCalled();

        $this->initUser(new InstallerEvent($commandExecutor->getWrappedObject()));
    }
}
