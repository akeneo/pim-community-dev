<?php

namespace Akeneo\Tool\Bundle\FileStorageBundle\Command;

use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

    private FileStorerInterface $fileStorer;
    private FilesystemProvider $filesystemProvider;

    public function __construct(
        FileStorerInterface $fileStorer,
        FilesystemProvider $filesystemProvider
    ) {
        parent::__construct();

        $this->fileStorer = $fileStorer;
        $this->filesystemProvider = $filesystemProvider;
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
    protected function execute(InputInterface $input, OutputInterface $output): int
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
            $this->filesystemProvider->getFilesystem($storageFsAlias);
        } catch (\LogicException $e) {
            return false;
        }

        return true;
    }
}
