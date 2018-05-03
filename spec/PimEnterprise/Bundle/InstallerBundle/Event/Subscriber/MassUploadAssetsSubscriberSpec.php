<?php

namespace spec\PimEnterprise\Bundle\InstallerBundle\Event\Subscriber;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\InstallerBundle\CommandExecutor;
use Pim\Bundle\InstallerBundle\Event\InstallerEvents;
use Pim\Bundle\InstallerBundle\FixtureLoader\FixturePathProvider;
use PimEnterprise\Bundle\InstallerBundle\Event\Subscriber\MassUploadAssetsSubscriber;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class MassUploadAssetsSubscriberSpec extends ObjectBehavior
{
    function let(FixturePathProvider $pathProvider)
    {
        $pathProvider->getFixturesPath()->willReturn('/tmp/');
        $this->beConstructedWith($pathProvider);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MassUploadAssetsSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn(
            [
                InstallerEvents::PRE_LOAD_FIXTURE => 'massUploadAssets',
            ]
        );
    }

    function it_only_triggers_before_fixtures_asset_csv(GenericEvent $event)
    {
        $event->getSubject()->willReturn('foo');
        $event->hasArgument(Argument::any())->shouldNotBeCalled();

        $this->massUploadAssets($event);
    }

    function it_does_not_do_anything_if_no_command_executor_is_provided(GenericEvent $event)
    {
        $event->getSubject()->willReturn('fixtures_asset_csv');
        $event->hasArgument('command_executor')->willReturn(false);
        $event->getArgument(Argument::any())->shouldNotBeCalled();

        $this->massUploadAssets($event);
    }

    function it_launches_copy_asset_files_and_mass_upload_assets_commands
    (
        GenericEvent $event,
        CommandExecutor $commandExecutor
    ) {
        $event->getSubject()->willReturn('fixtures_asset_csv');
        $event->hasArgument('command_executor')->willReturn(true);
        $event->getArgument('command_executor')->willReturn($commandExecutor);

        $commandExecutor->runCommand(
            MassUploadAssetsSubscriber::COPY_FILES_COMMAND,
            [
                '--from' => '/tmp/assets',
                '--user' => UserInterface::SYSTEM_USER_NAME,
                '--quiet' => true,
            ]
        )->willReturn($commandExecutor);

        $commandExecutor->runCommand(
            MassUploadAssetsSubscriber::MASS_UPLOAD_COMMAND,
            [
                '--user'  => UserInterface::SYSTEM_USER_NAME,
                '--quiet' => true,
            ]
        )->willReturn($commandExecutor);

        $this->massUploadAssets($event);
    }
}
