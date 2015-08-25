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

use PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver\AssetSaver;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\Upload\MassUploadProcessor;
use PimEnterprise\Component\ProductAsset\Upload\UploaderInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Process uploaded assets files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ProcessMassUploadCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product-asset:mass-upload')
            ->addOption(
                'dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Source directory'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceDir = $input->getOption('dir');
        $processor = $this->getMassUploadProcessor();

        $processor->getUploader()->setSubDirectory($sourceDir);

        $processedList = $processor->applyMassUpload();

        foreach ($processedList as $item) {
            $file = $item->getItem();

            switch ($item->getState()) {
                case ProcessedItem::STATE_ERROR:
                    $msg = sprintf('<error>%s\n%s</error>', $file->getFilename(), $item->getReason());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $msg = sprintf('%s <comment>Skipped (%s)</comment>', $file->getFilename(), $item->getReason());
                    break;
                default:
                    $msg = sprintf('%s <info>Done!</info>', $file->getFilename());
                    break;
            }

            $output->writeln($msg);
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }

    /**
     * @return MassUploadProcessor
     */
    protected function getMassUploadProcessor()
    {
        return $this->getContainer()->get('pimee_product_asset.mass_upload_processor');
    }

    /**
     * @return AssetSaver
     */
    protected function getAssetSaver()
    {
        return $this->getContainer()->get('pimee_product_asset.saver.asset');
    }

    /**
     * @return UploaderInterface
     */
    protected function getUploader()
    {
        return $this->getContainer()->get('pimee_product_asset.uploader');
    }
}
