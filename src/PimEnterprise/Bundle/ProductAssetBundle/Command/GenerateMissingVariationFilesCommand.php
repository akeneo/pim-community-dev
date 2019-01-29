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

use Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface;
use PimEnterprise\Component\ProductAsset\Completeness\CompletenessRemoverInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;
use PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGeneratorInterface;
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
        $assetCodes = $this->isGenerateForAllAssets($input) ? $this->getAllAssetsCodes() : [$input->getOption('asset')];
        try {
            $this->buildAssets($assetCodes);
        } catch (\LogicException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        $missingVariationIds = $this->findMissingVariationIds($input);
        if (empty($missingVariationIds)) {
            $output->writeln('<info>No missing variation</info>');

            return 0;
        }

        $this->generateMissingVariations($output, $missingVariationIds);

        return 0;
    }

    /**
     * @param InputInterface $input
     *
     * @return bool
     */
    private function isGenerateForAllAssets(InputInterface $input): bool
    {
        return null === $input->getOption('asset');
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
     * @return VariationRepositoryInterface
     */
    protected function getVariationRepository(): VariationRepositoryInterface
    {
        return $this->getContainer()->get('pimee_product_asset.repository.variation');
    }

    /**
     * @param array $assetCodes
     *
     * @return AssetInterface[]
     */
    protected function fetchAssetsByCode($assetCodes)
    {
        $assetRepository = $this->getContainer()->get('pimee_product_asset.repository.asset');

        return $assetRepository->findByIdentifiers($assetCodes);
    }

    /**
     * @return string[]
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

    protected function clearCache()
    {
        $this->getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    /**
     * @param InputInterface $input
     *
     * @return int[]
     */
    protected function findMissingVariationIds(InputInterface $input): array
    {
        $asset = null;
        if (!$this->isGenerateForAllAssets($input)) {
            $asset = $this->retrieveAsset($input->getOption('asset'));
            $missingVariations = $this->getAssetFinder()->retrieveVariationsNotGenerated($asset);

            $missingVariationIds = array_map(function (VariationInterface $variation) {
                return $variation->getId();
            }, $missingVariations);
        } else {
            $missingVariationIds = $this->retrieveIdsOfNotGeneratedVariations();
        }

        return $missingVariationIds;
    }

    /**
     * @return int[]
     */
    protected function retrieveIdsOfNotGeneratedVariations(): array
    {
        $connection = $this->getContainer()->get('database_connection');
        $sql = <<<SQL
SELECT ppav.id as id
FROM pimee_product_asset_variation ppav
WHERE ppav.file_info_id IS NULL
  AND ppav.source_file_info_id IS NOT NULL
SQL;

        $statement = $connection->query($sql);

        return array_column($statement->fetchAll(), 'id');
    }

    /**
     * @param OutputInterface $output
     * @param int[]           $missingVariationIds
     */
    protected function generateMissingVariations(OutputInterface $output, array $missingVariationIds): void
    {
        $chunks = array_chunk($missingVariationIds, static::BATCH_SIZE);

        foreach ($chunks as $missingVariationIdsToProcess) {
            $missingVariations = $this->getVariationRepository()->findBy(['id' => $missingVariationIdsToProcess]);

            $processedAssets = $this->generateVariation($output, $missingVariations);
            $this->scheduleCompleteness($output, $processedAssets);

            $this->clearCache();
        }

        $output->writeln('<info>Done!</info>');
    }

    /**
     * @param OutputInterface      $output
     * @param VariationInterface[] $missingVariations
     *
     * @return array
     */
    protected function generateVariation(OutputInterface $output, array $missingVariations): array
    {
        $generator = $this->getVariationsCollectionFileGenerator();
        $processedList = $generator->generate($missingVariations, true);

        $processedAssets = [];
        foreach ($processedList as $item) {
            $variation = $item->getItem();

            if (!$variation instanceof VariationInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expects a "PimEnterprise\Component\ProductAsset\Model\VariationInterface", "%s" provided.',
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

        return $processedAssets;
    }

    /**
     * @param OutputInterface  $output
     * @param AssetInterface[] $processedAssets
     */
    protected function scheduleCompleteness(OutputInterface $output, array $processedAssets): void
    {
        $output->writeln('<info>Schedule completeness calculation</info>');

        foreach ($processedAssets as $asset) {
            $output->writeln(sprintf('<info>Schedule completeness for asset %s</info>', $asset->getCode()));
            $this->getCompletenessRemover()->removeForAsset($asset);
        }
    }

    /**
     * @param string[] $assetCodes
     */
    protected function buildAssets(array $assetCodes): void
    {
        $chunks = array_chunk($assetCodes, static::BATCH_SIZE);
        foreach ($chunks as $assetCodesToBuild) {
            $assets = $this->fetchAssetsByCode($assetCodesToBuild);
            $builtAssets = array_map(
                function (AssetInterface $asset) {
                    $this->buildAsset($asset);

                    return $asset;
                },
                $assets
            );
            $this->getAssetSaver()->saveAll($builtAssets);

            $this->clearCache();
        }
    }
}
