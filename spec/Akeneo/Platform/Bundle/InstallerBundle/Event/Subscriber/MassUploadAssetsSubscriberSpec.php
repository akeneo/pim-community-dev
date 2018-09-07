<?php

namespace spec\Akeneo\Platform\Bundle\InstallerBundle\Event\Subscriber;

use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixturePathProvider;
use Akeneo\Platform\Bundle\InstallerBundle\Event\Subscriber\MassUploadAssetsSubscriber;
use Akeneo\Asset\Bundle\Command\CopyAssetFilesCommand;
use Akeneo\Asset\Bundle\Command\ProcessMassUploadCommand;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Filesystem\Filesystem;

class MassUploadAssetsSubscriberSpec extends ObjectBehavior
{
    function let(
        FixturePathProvider $pathProvider,
        Filesystem $filesystem,
        GenericEvent $event,
        CommandExecutor $commandExecutor
    ) {
        $pathProvider->getFixturesPath()->willReturn('/path/to/fixtures/');
        $filesystem->exists('/path/to/fixtures/assets')->willReturn(true);
        $event->getSubject()->willReturn('fixtures_asset_csv');
        $event->hasArgument('command_executor')->willReturn(true);
        $event->getArgument('command_executor')->willReturn($commandExecutor);

        $this->beConstructedWith($pathProvider, $filesystem);
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

    function it_does_not_do_anything_if_assets_path_does_not_exist(GenericEvent $event, $filesystem)
    {
        $filesystem->exists('/path/to/fixtures/assets')->willReturn(false);

        $event->getSubject()->willReturn('fixtures_asset_csv');
        $event->hasArgument(Argument::any())->shouldNotBeCalled();

        $this->massUploadAssets($event);
    }

    function it_throws_an_exception_if_no_command_executor_argument_is_provided(GenericEvent $event)
    {
        $event->getSubject()->willReturn('fixtures_asset_csv');
        $event->hasArgument('command_executor')->willReturn(false);

        $this->shouldThrow(\Exception::class)->during('massUploadAssets', [$event]);
    }

    function it_throws_an_exception_if_argument_is_not_a_command_executor(GenericEvent $event)
    {
        $event->getSubject()->willReturn('fixtures_asset_csv');
        $event->hasArgument('command_executor')->willReturn(true);
        $event->getArgument('command_executor')->willReturn(new \stdClass());

        $this->shouldThrow(\Exception::class)->during('massUploadAssets', [$event]);
    }

    function it_launches_copy_asset_files_and_mass_upload_assets_commands
    (
        GenericEvent $event,
        CommandExecutor $commandExecutor
    ) {
        $commandExecutor->runCommand(
            CopyAssetFilesCommand::NAME,
            [
                '--from' => '/path/to/fixtures/assets',
                '--user' => UserInterface::SYSTEM_USER_NAME,
                '--quiet' => true,
            ]
        )->willReturn($commandExecutor);

        $commandExecutor->runCommand(
            ProcessMassUploadCommand::NAME,
            [
                '--user'  => UserInterface::SYSTEM_USER_NAME,
                '--quiet' => true,
            ]
        )->willReturn($commandExecutor);

        $this->massUploadAssets($event);
    }
}
