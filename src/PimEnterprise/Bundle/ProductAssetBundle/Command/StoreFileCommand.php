<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Store a raw file in a storage filesystem
 *
 * TODO: should be moved from there
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class StoreFileCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product-asset:store-file')
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

        $file = new \SplFileInfo($filePath);
        $storer = $this->getRawFileStorer();
        $storer->store($file, $storageFsAlias);

        return 0;
    }

    /**
     * @return RawFileStorerInterface
     */
    protected function getRawFileStorer()
    {
        return $this->getContainer()->get('akeneo_file_storage.file_storage.raw_file.storer');
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
