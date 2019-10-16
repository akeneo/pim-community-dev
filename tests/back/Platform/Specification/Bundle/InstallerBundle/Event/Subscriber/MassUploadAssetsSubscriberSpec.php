<?php

namespace Specification\Akeneo\Platform\Bundle\InstallerBundle\Event\Subscriber;

use Akeneo\Asset\Bundle\Command\CopyAssetFilesCommand;
use Akeneo\Asset\Bundle\Command\ProcessMassUploadCommand;
use Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Bundle\InstallerBundle\Event\Subscriber\MassUploadAssetsSubscriber;
use Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixturePathProvider;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

class MassUploadAssetsSubscriberSpec extends ObjectBehavior
{
    function let(FixturePathProvider $pathProvider, Filesystem $filesystem)
    {
        $pathProvider->getFixturesPath('minimal')->willReturn('/path/to/fixtures/');
        $filesystem->exists('/path/to/fixtures/assets')->willReturn(true);
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

    function it_subscribes_to_installer_post_load_fixture_event()
    {
        $this::getSubscribedEvents()->shouldHaveKey(InstallerEvents::PRE_LOAD_FIXTURE);
    }

    function it_only_triggers_before_fixtures_asset_csv(CommandExecutor $commandExecutor)
    {
        $commandExecutor->runCommand(Argument::any())->shouldNotBeCalled();

        $this->massUploadAssets(new InstallerEvent($commandExecutor->getWrappedObject(), 'foo'));
    }

    function it_does_not_do_anything_if_assets_path_does_not_exist($filesystem, CommandExecutor $commandExecutor)
    {
        $filesystem->exists('/path/to/fixtures/assets')->willReturn(false);
        $commandExecutor->runCommand(Argument::any())->shouldNotBeCalled();

        $this->massUploadAssets(
            new InstallerEvent($commandExecutor->getWrappedObject(), 'fixtures_asset_csv', ['catalog' => 'minimal'])
        );
    }

    function it_launches_copy_asset_files_and_mass_upload_assets_commands($filesystem, CommandExecutor $commandExecutor)
    {
        $filesystem->exists('/path/to/fixtures/assets')->willReturn(true);

        $commandExecutor->runCommand(
            CopyAssetFilesCommand::NAME,
            [
                '--from'  => '/path/to/fixtures/assets',
                '--user'  => UserInterface::SYSTEM_USER_NAME,
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

        $this->massUploadAssets(
            new InstallerEvent($commandExecutor->getWrappedObject(), 'fixtures_asset_csv', ['catalog' => 'minimal'])
        );
    }
}
