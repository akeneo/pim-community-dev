<?php

declare(strict_types=1);

namespace Pim\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Prepare required directories
 *
 * @author    Anael Chardan <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrepareRequiredDirectoriesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('pim:installer:prepare-required-directories')
            ->setDescription('Prepare required directories for Akeneo PIM');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('<info>Akeneo PIM required directories creation:</info>');

        $directoriesContainer = $this->getContainer()->get('pim_installer.directories_registry');
        $filesystem = new Filesystem();

        foreach ($directoriesContainer->getDirectories() as $directory) {
            if ($filesystem->exists($directory)) {
                $filesystem->remove($directory);
                $output->writeln($directory . ' directory, removed.');
            }
            $filesystem->mkdir($directory);
            $output->writeln($directory . ' directory, created.');
        }

        $output->writeln('<info>Akeneo PIM required directories : creation finished.</info>');
    }
}
