<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGeneratorInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate the missing variation files
 * It can generate all missing variations or missing variations for a specific asset code
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class GenerateMissingVariationFilesCommand extends AbstractGenerationVariationFileCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:asset:generate-missing-variation-files');
        $this->setDescription('Generate all the missing variation files.');
        $this->addOption(
            'asset',
            'a',
            InputOption::VALUE_REQUIRED,
            'Asset identifier',
            null
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $assetCode         = $input->getOption('asset');
            $missingVariations = $this->retrieveMissingVariations($assetCode);
        } catch (\LogicException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        if (0 === count($missingVariations)) {
            $output->writeln('<info>No missing variation</info>');

            return 0;
        }

        $generator     = $this->getVariationsCollectionFileGenerator();
        $processedList = $generator->generate($missingVariations, true);

        foreach ($processedList as $item) {
            $variation = $item->getItem();
            $msg       = $this->getGenerationMessage(
                $variation->getAsset(),
                $variation->getChannel(),
                $variation->getLocale()
            );

            switch ($item->getState()) {
                case ProcessedItem::STATE_ERROR:
                    $msg = sprintf('<error>%s\n%s</error>', $msg, $item->getReason());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $msg = sprintf('%s <comment>Skipped (%s)</comment>', $msg, $item->getReason());
                    break;
                default:
                    $msg = sprintf('%s <info>Done!</info>', $msg);
                    break;
            }

            $output->writeln($msg);
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }

    /**
     * @param int|null $assetCode
     *
     * @return VariationInterface[]
     */
    protected function retrieveMissingVariations($assetCode = null)
    {
        $missingVariations = [];

        if (null !== $assetCode) {
            $asset = $this->retrieveAsset($assetCode);
            foreach ($asset->getVariations() as $variation) {
                if (null === $variation->getFile() && null !== $variation->getSourceFile()) {
                    $missingVariations[] = $variation;
                }
            }
        } else {
            $variationsRepo    = $this->getContainer()->get('pimee_product_asset.repository.variation');
            $missingVariations = $variationsRepo->findNotGenerated();
        }

        return $missingVariations;
    }

    /**
     * @return VariationsCollectionFilesGeneratorInterface
     */
    protected function getVariationsCollectionFileGenerator()
    {
        return $this->getContainer()->get('pimee_product_asset.variations_collection_files_generator');
    }
}
