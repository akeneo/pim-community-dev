<?php

namespace Akeneo\Tool\Bundle\FileStorageBundle\Command;

use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use League\Flysystem\MountManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Store a raw file in a storage filesystem
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StoreFileCommand extends Command
{
    protected static $defaultName = 'akeneo:file-storage:store';

    /** @var FileStorerInterface */
    private $fileStorer;

    /** @var MountManager */
    private $mountManager;

    public function __construct(
        FileStorerInterface $fileStorer,
        MountManager $mountManager
    ) {
        parent::__construct();

        $this->fileStorer = $fileStorer;
        $this->mountManager = $mountManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED)
            ->addArgument('storage', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('file');
        if (!is_file($filePath)) {
            $output->writeln(sprintf('<error>"%s" is not a valid file path.</error>', $filePath));

            return 1;
        }

        $storageFsAlias = $input->getArgument('storage');
        if (!$this->hasFileSystem($storageFsAlias)) {
            $output->writeln(sprintf('<error>"%s" is not a valid filesystem.</error>', $storageFsAlias));

            return 1;
        }

        $rawFile = new \SplFileInfo($filePath);
        $file = $this->fileStorer->store($rawFile, $storageFsAlias);

        $output->writeln(
            sprintf(
                '<info>File "%s" stored under key "%s" on "%s".</info>',
                $rawFile->getPathname(),
                $file->getKey(),
                $storageFsAlias
            )
        );

        return 0;
    }

    /**
     * @param string $storageFsAlias
     *
     * @return bool
     */
    protected function hasFileSystem($storageFsAlias)
    {
        try {
            $this->mountManager->getFilesystem($storageFsAlias);
        } catch (ServiceNotFoundException $e) {
            return false;
        }

        return true;
    }
}
