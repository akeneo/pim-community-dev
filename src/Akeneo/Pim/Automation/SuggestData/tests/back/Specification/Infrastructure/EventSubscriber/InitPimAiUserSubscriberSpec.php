<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\EventSubscriber;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Command\InitPimAiUserCommand;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\EventSubscriber\InitPimAiUserSubscriber;
use Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitPimAiUserSubscriberSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InitPimAiUserSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_post_load_fixtures_event()
    {
        $this::getSubscribedEvents()->shouldHaveKey(InstallerEvents::POST_LOAD_FIXTURES);
    }

    function it_launches_init_user_command(CommandExecutor $commandExecutor)
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
