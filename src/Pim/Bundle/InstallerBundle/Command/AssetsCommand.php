<?php
namespace Pim\Bundle\InstallerBundle\Command;

use Pim\Bundle\InstallerBundle\Event\CommandBatchEvent;
use Pim\Bundle\InstallerBundle\Event\CommandEvent;
use Pim\Bundle\InstallerBundle\Event\InstallEvent;
use Pim\Bundle\InstallerBundle\Event\InstallEvents;
use Pim\Bundle\InstallerBundle\SimpleCommand\SimpleCommand;
use Pim\Bundle\InstallerBundle\SimpleCommand\SimpleCommandBatch;
use Pim\Bundle\InstallerBundle\SimpleCommand\SimpleCommandExecutor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Assets dump command
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetsCommand extends ContainerAwareCommand
{
    /** @var SimpleCommandExecutor */
    protected $commandExecutor;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:installer:assets')
            ->setDescription('Install assets for Akeneo PIM');
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->eventDispatcher = $this->getContainer()->get("event_dispatcher");
        $this->commandExecutor = new SimpleCommandExecutor(
            $input,
            $output,
            $this->getApplication()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->eventDispatcher->dispatch(InstallEvents::ASSETS_PRE_INSTALL, new InstallEvent());

        $output->writeln('<info>Akeneo PIM assets</info>');

        $this
            ->dumpAssets($input, $output)
            ->dumpTranslations($input, $output);

        $this->eventDispatcher->dispatch(InstallEvents::ASSETS_POST_INSTALL, new InstallEvent());
        return $this;
    }

    /**
     * Executes all asset dumping commands
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return $this
     */
    protected function dumpAssets(InputInterface $input, OutputInterface $output)
    {
        $commands = [
            "oro:navigation:init" => [],
            "fos:js-routing:dump" => ["--target" => "web/js/routes.js"],
            "assets:install" => [],
            "assetic:dump" => [],
            "oro:assetic:dump" => [],
        ];
        $commandBatch = SimpleCommandBatch::create($commands);

        $event = new CommandBatchEvent($commandBatch);
        $this->getContainer()->get("event_dispatcher")->dispatch(InstallEvents::ASSETS_DUMP, $event);
        $commandBatch = $event->getCommandBatch();

        $this->commandExecutor->runBatch($commandBatch);

        return $this;
    }

    /**
     * Executes translation asset dump command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return $this
     */
    protected function dumpTranslations(InputInterface $input, OutputInterface $output)
    {
        $defaultLocales = ['en', 'fr', 'nl', 'de', 'ru', 'ja', 'pt', 'it'];
        $command = SimpleCommand::create("oro:translation:dump", ["locale" => $defaultLocales]);

        $event = new CommandEvent($command);
        $this->getContainer()->get("event_dispatcher")->dispatch(InstallEvents::ASSETS_DUMP_TRANSLATIONS, $event);
        $command = $event->getCommand();
        $this->commandExecutor->run($command);

        return $this;
    }


}