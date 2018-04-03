<?php

namespace Akeneo\Bundle\FileStorageBundle\Command;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class StoreFileCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('akeneo:file-storage:store')
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
        $storer = $this->getFileStorer();
        $file = $storer->store($rawFile, $storageFsAlias);

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
     * @return FileStorerInterface
     */
    protected function getFileStorer()
    {
        return $this->getContainer()->get('akeneo_file_storage.file_storage.file.file_storer');
    }

    /**
     * @param string $storageFsAlias
     *
     * @return bool
     */
    protected function hasFileSystem($storageFsAlias)
    {
        try {
            $this->getContainer()->get(sprintf('oneup_flysystem.%s_filesystem', $storageFsAlias));
        } catch (ServiceNotFoundException $e) {
            return false;
        }

        return true;
    }
}
