<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Asset\Bundle\Command;

use Akeneo\Asset\Component\Upload\UploadContext;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Command to copy the content of a source directory to the default assets mass upload import directory
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CopyAssetFilesCommand extends ContainerAwareCommand
{
    const NAME = 'pim:product-asset:copy-asset-files';

    /**
     * @inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(static::NAME)
            ->setDescription(
                'Copy the content of a source directory to the default assets mass upload import directory'
            )
            ->addOption(
                'from',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the directory containing the asset files to copy'
            )
            ->addOption(
                'user',
                null,
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
        if (!$filesystem->exists($sourceDirectory) || !is_dir($sourceDirectory)) {
            $output->writeln(
                sprintf(
                    '<comment>The source directory %s does not exist, aborting.</comment>',
                    $sourceDirectory
                )
            );
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
