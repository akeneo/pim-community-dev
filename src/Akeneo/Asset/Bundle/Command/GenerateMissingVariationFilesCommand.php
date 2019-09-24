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

use Akeneo\Asset\Bundle\Event\AssetEvent;
use Akeneo\Asset\Component\Builder\ReferenceBuilderInterface;
use Akeneo\Asset\Component\Builder\VariationBuilderInterface;
use Akeneo\Asset\Component\Finder\AssetFinderInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Asset\Component\Persistence\Query\Sql\FindAssetCodesWithMissingVariationWithFileInterface;
use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\Asset\Component\Repository\VariationRepositoryInterface;
use Akeneo\Asset\Component\VariationFileGeneratorInterface;
use Akeneo\Asset\Component\VariationsCollectionFilesGeneratorInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Generate the missing variation files
 * It can generate all missing variations or missing variations for a specific asset code
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class GenerateMissingVariationFilesCommand extends AbstractGenerationVariationFileCommand
{
    protected static $defaultName = 'pim:asset:generate-missing-variation-files';

    const BATCH_SIZE = 100;

    /** @var Connection */
    private $connection;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var VariationRepositoryInterface */
    private $variationRepository;

    /** @var FindAssetCodesWithMissingVariationWithFileInterface */
    private $assetCodesWithMissingVariationWithFile;

    /** @var EntityManagerClearerInterface */
    private $entityManagerClearer;

    /** @var VariationsCollectionFilesGeneratorInterface */
    private $variationsCollectionFilesGenerator;

    public function __construct(
        AssetFinderInterface $assetFinder,
        ReferenceBuilderInterface $referenceBuilder,
        VariationBuilderInterface $variationBuilder,
        SaverInterface $assetSaver,
        VariationFileGeneratorInterface $variationFileGenerator,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        AssetRepositoryInterface $assetRepository,
        VariationsCollectionFilesGeneratorInterface $variationsCollectionFilesGenerator,
        VariationRepositoryInterface $variationRepository,
        FindAssetCodesWithMissingVariationWithFileInterface $assetCodesWithMissingVariationWithFile,
        EntityManagerClearerInterface $entityManagerClearer,
        Connection $connection,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct(
            $assetFinder,
            $referenceBuilder,
            $variationBuilder,
            $assetSaver,
            $variationFileGenerator,
            $channelRepository,
            $localeRepository,
            $assetRepository
        );

        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->variationRepository = $variationRepository;
        $this->assetCodesWithMissingVariationWithFile = $assetCodesWithMissingVariationWithFile;
        $this->entityManagerClearer = $entityManagerClearer;
        $this->variationsCollectionFilesGenerator = $variationsCollectionFilesGenerator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
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
        // First step : Get the assets that have missing variations in DB and build only those ones to create their missing variations in DB (can be done also for the asset passed in option).
        $assetsWithMissingVariations = $this->isGenerateForAllAssets($input)
            ? $this->findAssetsWithMissingVariations()
            : [$input->getOption('asset')];
        try {
            $this->buildAssets($assetsWithMissingVariations, $output);
        } catch (\LogicException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        // Second step : Generate missing files for the variations (variation lines in DB with null in the file_info_id field).
        $variationIdsWithMissingFile = $this->findVariationIdsWithMissingFile($input);
        if (empty($variationIdsWithMissingFile)) {
            $output->writeln('<info>No missing variation files</info>');

            return 0;
        }

        $this->generateMissingVariationFiles($output, $variationIdsWithMissingFile);

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
     * @param array $assetCodes
     *
     * @return AssetInterface[]
     */
    protected function fetchAssetsByCode($assetCodes)
    {
        return $this->getAssetRepository()->findByIdentifiers($assetCodes);
    }

    /**
     * @return string[] Assets codes
     */
    private function findAssetsWithMissingVariations(): array
    {
        return $this->assetCodesWithMissingVariationWithFile->execute();
    }

    protected function clearCache()
    {
        $this->entityManagerClearer->clear();
    }

    /**
     * @param InputInterface $input
     *
     * @return int[]
     */
    private function findVariationIdsWithMissingFile(InputInterface $input): array
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
        $sql = <<<SQL
SELECT ppav.id as id
FROM pimee_product_asset_variation ppav
WHERE ppav.file_info_id IS NULL
  AND ppav.source_file_info_id IS NOT NULL
SQL;

        $statement = $this->connection->query($sql);

        return array_column($statement->fetchAll(), 'id');
    }

    /**
     * @param OutputInterface $output
     * @param int[]           $missingVariationIds
     */
    private function generateMissingVariationFiles(OutputInterface $output, array $missingVariationIds): void
    {
        $chunks = array_chunk($missingVariationIds, static::BATCH_SIZE);

        foreach ($chunks as $missingVariationIdsToProcess) {
            $missingVariations = $this->variationRepository->findBy(['id' => $missingVariationIdsToProcess]);
            $this->generateVariationFiles($output, $missingVariations);

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
    private function generateVariationFiles(OutputInterface $output, array $missingVariations): array
    {
        $processedList = $this->variationsCollectionFilesGenerator->generate($missingVariations, true);

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
     * @param string[] $assetCodes
     */
    protected function buildAssets(array $assetCodes, OutputInterface $output): void
    {
        $chunks = array_chunk($assetCodes, static::BATCH_SIZE);
        foreach ($chunks as $assetCodesToBuild) {
            $assets = $this->fetchAssetsByCode($assetCodesToBuild);
            $builtAssets = array_map(
                function (AssetInterface $asset) use ($output) {
                    $this->buildAsset($asset);
                    $output->writeln(sprintf('<info>The asset %s is built</info>', $asset));

                    return $asset;
                },
                $assets
            );
            $this->getAssetSaver()->saveAll($builtAssets);
            $this->eventDispatcher->dispatch(AssetEvent::POST_UPLOAD_FILES, new AssetEvent($builtAssets));

            $this->clearCache();
        }
    }
}
