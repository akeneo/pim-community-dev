<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Symfony\Command;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\Installer\AssetsInstaller;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\Installer\FixturesInstaller;
use Akeneo\ReferenceEntity\Infrastructure\Symfony\Command\InstallerCommand;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class InstallerCommandSpec extends ObjectBehavior
{
    function let(FixturesInstaller $fixturesInstaller, AssetsInstaller $assetsInstaller)
    {
        $this->beConstructedWith($fixturesInstaller, $assetsInstaller, 'catalog_name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InstallerCommand::class);
    }

    function it_subscribes_to_events_dispatched_by_the_pim_installation_process()
    {
        $this::getSubscribedEvents()->shouldReturn([
            InstallerEvents::POST_SYMFONY_ASSETS_DUMP => ['installAssets'],
            InstallerEvents::POST_ASSETS_DUMP         => ['installAssets'],
            InstallerEvents::POST_DB_CREATE           => ['createSchema'],
            InstallerEvents::POST_LOAD_FIXTURES       => ['loadFixtures'],
        ]);
    }

    function it_creates_the_schema(FixturesInstaller $fixturesInstaller)
    {
        $fixturesInstaller->createSchema()->shouldBeCalled();
        $this->createSchema();
    }

    function it_loads_the_fixtures(FixturesInstaller $fixturesInstaller)
    {
        $fixturesInstaller->loadCatalog('catalog_name')->shouldBeCalled();
        $this->loadFixtures();
    }

    function it_installs_the_assets(AssetsInstaller $assetsInstaller)
    {
        $event = new GenericEvent();
        $event->setArgument('symlink', true);
        $assetsInstaller->installAssets(true)->shouldBeCalled();

        $this->installAssets($event);
    }

    function it_resets_the_schema_and_loads_the_fixtures_when_called_by_the_command_line(
        InputInterface $input,
        OutputInterface $output,
        FixturesInstaller $fixturesInstaller
    ) {
        $fixturesInstaller->createSchema()->shouldBeCalled();
        $fixturesInstaller->loadCatalog(FixturesInstaller::ICE_CAT_DEMO_DEV_CATALOG)->shouldBeCalled();

        $this->execute($input, $output);
    }
}
