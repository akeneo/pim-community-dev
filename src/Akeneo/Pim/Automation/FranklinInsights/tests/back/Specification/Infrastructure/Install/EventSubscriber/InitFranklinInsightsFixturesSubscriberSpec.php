<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install\EventSubscriber;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install\EventSubscriber\InitFranklinInsightsFixturesSubscriber;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command\InitFranklinUserCommand;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command\InitJobInstancesCommand;
use Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitFranklinInsightsFixturesSubscriberSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(InitFranklinInsightsFixturesSubscriber::class);
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
            InitFranklinUserCommand::getDefaultName(),
            [
                '--quiet' => true,
            ]
        )->shouldBeCalled();
        $commandExecutor->runCommand(
            InitJobInstancesCommand::NAME,
            ['--quiet' => true]
        )->shouldBeCalled();

        $this->initFixtures(new InstallerEvent($commandExecutor->getWrappedObject()));
    }
}
