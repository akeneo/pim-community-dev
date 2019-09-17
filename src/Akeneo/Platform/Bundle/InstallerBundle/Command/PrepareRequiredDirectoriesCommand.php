<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Akeneo\Platform\Bundle\InstallerBundle\PimDirectoriesRegistry;
use Symfony\Component\Console\Command\Command;
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
class PrepareRequiredDirectoriesCommand extends Command
{
    protected static $defaultName = 'pim:installer:prepare-required-directories';

    /** @var PimDirectoriesRegistry */
    private $directoriesContainer;

    public function __construct(
        PimDirectoriesRegistry $directoriesContainer
    ) {
        parent::__construct();

        $this->directoriesContainer = $directoriesContainer;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Prepare required directories for Akeneo PIM');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('<info>Akeneo PIM required directories creation:</info>');

        $filesystem = new Filesystem();

        foreach ($this->directoriesContainer->getDirectories() as $directory) {
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
