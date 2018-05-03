<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CopyAssetFilesCommand extends ContainerAwareCommand
{
    /**
     * @inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product-asset:copy-asset-files')
            ->setDescription('Copy files from a source directory to the default asset import directory')
            ->addOption(
                'from',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the directory containing the asset files to copy'
            )
            ->addOption(
                'user',
                UserInterface::SYSTEM_USER_NAME,
                InputOption::VALUE_OPTIONAL,
                'Username to process'
            )
        ;
    }

    /**
     * @inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();
        $sourceDirectory = realpath($input->getOption('from'));
        if (!$filesystem->exists($sourceDirectory)) {
            return;
        }

        $uploadContext = new UploadContext(
            $this->getContainer()->getParameter('tmp_storage_dir'),
            $input->getOption('user')
        );

        $output->writeln(sprintf(
            '<info>Copying the contents of %s into %s...</info>',
            $sourceDirectory,
            $uploadContext->getTemporaryUploadDirectory()
        ));
        $filesystem->mkdir($uploadContext->getTemporaryUploadDirectory());
        $filesystem->mirror($sourceDirectory, $uploadContext->getTemporaryImportDirectory());

        $output->writeln('<info>Done!</info>');
    }
}
