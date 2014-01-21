<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Doctrine\ORM\Mapping\ClassMetadata;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\ProcessBuilder;

use Symfony\Component\Process\PhpExecutableFinder;

use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\InstallerBundle\Command\InstallCommand as OroInstallCommand;

/**
 * Override OroInstaller command to add PIM custom rules
 *
 * @author    Nicolas <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallCommand extends OroInstallCommand
{
    /**
     * @staticvar string
     */
    const APP_NAME = 'Akeneo PIM';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('pim:install');
    }

    /**
     * {@inheritdoc}
     */
    protected function launchCommands(InputInterface $input, OutputInterface $output)
    {
        parent::launchCommands($input, $output);

        $this->commandExecutor
            ->runCommand(
                'pim:search:reindex',
                array('locale' => $this->getContainer()->getParameter('locale'))
            )
            ->runCommand('pim:versioning:refresh')
            ->runCommand('pim:completeness:calculate');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadFixtures(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('env') === 'behat') {
            $input->setOption('fixtures', self::LOAD_ORO);
        }

        return parent::loadFixtures($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequirements()
    {
        if (!class_exists('PimRequirements')) {
            require_once $this->getContainer()->getParameter('kernel.root_dir')
                . DIRECTORY_SEPARATOR . 'PimRequirements.php';
        }

        $directories = array();
        $directories[] = $this->getContainer()->getParameter('upload_dir');
        $directories[] = $this->getContainer()->getParameter('archive_dir');

        return new \PimRequirements($directories);
    }

    /**
     * {@inheritdoc}
     */
    protected function createUser(InputInterface $input, OutputInterface $output)
    {
        $user = parent::createUser($input, $output);

        // Define catalog locale
        $localeCode = $this->getContainer()->getParameter('locale');
        $localeManager = $this->getContainer()->get('pim_catalog.manager.locale');
        $locale = $localeManager->getLocaleByCode($localeCode);
        $user->setCatalogLocale($locale);

        // Define catalog scope
        $channelManager = $this->getContainer()->get('pim_catalog.manager.channel');
        $channel = $channelManager->getChannels()[0];
        $user->setCatalogScope($channel);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderRequirements(
        InputInterface $input,
        OutputInterface $output,
        \RequirementCollection $collection
    ) {
        parent::renderRequirements($input, $output, $collection);

        $this->renderTable($collection->getPimRequirements(), 'Pim specific requirements', $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function assetsStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Preparing application.</info>');

        $this->commandExecutor
            ->runCommand('oro:navigation:init')
            ->runCommand('fos:js-routing:dump', array('--target' => 'web/js/routes.js'))
            ->runCommand('oro:localization:dump')
            ->runCommand('assets:install')
            ->runCommand('assetic:dump')
            ->runCommand('oro:assetic:dump')
            ->runCommand('oro:translation:dump');

        $output->writeln('');

        return $this;
    }
}
