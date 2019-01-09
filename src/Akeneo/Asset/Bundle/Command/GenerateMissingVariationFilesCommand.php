<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Command;

use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\VariationsCollectionFilesGeneratorInterface;
use Akeneo\Pim\Enrichment\Asset\Component\Completeness\CompletenessRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessGeneratorInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
    const BATCH_SIZE = 100;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:asset:generate-missing-variation-files');
        $this->setDescription('Generate missing variation files for one asset or for all assets.');
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
            $asset = null;
            if (null !== $assetCode = $input->getOption('asset')) {
                $asset = $this->retrieveAsset($assetCode);

                $this->buildAsset($asset);
                $this->getAssetSaver()->save($asset);
            } else {
                $assetsCodes = $this->getAllAssetsCodes();
                $chunks = array_chunk($assetsCodes, static::BATCH_SIZE);
                foreach ($chunks as $assetCodes) {
                    $assets = $this->buildAssets($assetCodes);

                    $this->getAssetSaver()->saveAll($assets);
                    $this->detachAll($assets);
                }
            }

            $missingVariations = $this->getAssetFinder()->retrieveVariationsNotGenerated($asset);
        } catch (\LogicException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        if (0 === count($missingVariations)) {
            $output->writeln('<info>No missing variation</info>');

            return 0;
        }

        $generator = $this->getVariationsCollectionFileGenerator();
        $processedList = $generator->generate($missingVariations, true);

        $processedAssets = [];
        foreach ($processedList as $item) {
            $variation = $item->getItem();

            if (!$variation instanceof VariationInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Akeneo\Asset\Component\Model\VariationInterface", "%s" provided.',
                        get_class($variation)
                    )
                );
            }

            $msg = $this->getGenerationMessage(
                $variation->getAsset(),
                $variation->getChannel(),
                $variation->getLocale()
            );

            switch ($item->getState()) {
                case ProcessedItem::STATE_ERROR:
                    $msg = sprintf("<error>%s\n%s</error>", $msg, $item->getReason());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $msg = sprintf('%s <comment>Skipped (%s)</comment>', $msg, $item->getReason());
                    break;
                default:
                    $asset = $variation->getAsset();
                    if (!array_key_exists($asset->getCode(), $processedAssets)) {
                        $processedAssets[$asset->getCode()] = $asset;
                    }
                    $msg = sprintf('%s <info>Done!</info>', $msg);
                    break;
            }

            $output->writeln($msg);
        }

        $output->writeln('<info>Schedule completeness calculation</info>');

        foreach ($processedAssets as $asset) {
            $output->writeln(sprintf('<info>Schedule completeness for asset %s</info>', $asset->getCode()));
            $this->getCompletenessRemover()->removeForAsset($asset);
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }

    /**
     * @return VariationsCollectionFilesGeneratorInterface
     */
    protected function getVariationsCollectionFileGenerator()
    {
        return $this->getContainer()->get('pimee_product_asset.variations_collection_files_generator');
    }

    /**
     * @deprecated will be remove in 3.0
     *
     * @return CompletenessGeneratorInterface
     */
    protected function getCompletenessGenerator()
    {
        return $this->getContainer()->get('pim_catalog.completeness.generator');
    }

    /**
     *
     * @return CompletenessRemoverInterface
     */
    protected function getCompletenessRemover()
    {
        return $this->getContainer()->get('pimee_product_asset.remover.completeness');
    }

    /**
     * @param array $assetCodes asset codes
     *
     * @return array|ArrayCollection
     */
    protected function buildAssets($assetCodes)
    {
        $assets = $this->fetchAssetsByCode($assetCodes);
        foreach ($assets as $asset) {
            $this->buildAsset($asset);
        }

        return $assets;
    }

    /**
     * @param array $assetCodes
     *
     * @return array|ArrayCollection
     */
    protected function fetchAssetsByCode($assetCodes)
    {
        $assetRepository = $this->getContainer()->get('pimee_product_asset.repository.asset');

        return $assetRepository->findByIdentifiers($assetCodes);
    }

    /**
     * @return array
     */
    protected function getAllAssetsCodes()
    {
        $connection = $this->getContainer()->get('database_connection');
        $sql = <<<SQL
            SELECT code
            FROM pimee_product_asset_asset
SQL;
        $statement = $connection->query($sql);

        return array_column($statement->fetchAll(), 'code');
    }

    /**
     * @param array $objects
     */
    protected function detachAll($objects)
    {
        $this->getContainer()->get('akeneo_storage_utils.doctrine.object_detacher')->detachAll($objects);
    }
}
