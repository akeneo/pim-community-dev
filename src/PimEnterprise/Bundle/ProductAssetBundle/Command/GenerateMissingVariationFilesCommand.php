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

use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGeneratorInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate the missing variation files
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
        $this->setName('pim:asset:generate-missing-variations');
        $this->setDescription('Generate all the missing variation files.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $variations = $this->retrieveMissingVariations();
        } catch (\LogicException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        if (0 === count($variations)) {
            $output->writeln('<info>No missing variation</info>');

            return 0;
        }

        $generator     = $this->getVariationsCollectionFileGenerator();
        $processedList = $generator->generate($variations);

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
     * @return VariationInterface[]
     */
    protected function retrieveMissingVariations()
    {
        $variationsRepo    = $this->getContainer()->get('pimee_product_asset.repository.variation');
        $missingVariations = $variationsRepo->findBy(['file' => null]);

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
