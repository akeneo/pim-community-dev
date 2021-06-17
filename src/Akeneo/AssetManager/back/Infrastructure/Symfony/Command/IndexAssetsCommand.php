<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command indexes all the assets loaded in the database
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexAssetsCommand extends Command
{
    protected static $defaultName = self::INDEX_ASSETS_COMMAND_NAME;

    public const INDEX_ASSETS_COMMAND_NAME = 'akeneo:asset-manager:index-assets';
    private const ERROR_CODE_USAGE = 1;

    private Client $assetClient;

    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private AssetIndexerInterface $assetIndexer;

    private AssetFamilyExistsInterface $assetFamilyExists;

    private string $assetIndexName;

    public function __construct(
        Client $client,
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetIndexerInterface $assetIndexer,
        AssetFamilyExistsInterface $assetFamilyExists,
        string $assetIndexName
    ) {
        parent::__construct();

        $this->assetClient = $client;
        $this->assetFamilyRepository = $assetFamilyRepository;
        $this->assetIndexer = $assetIndexer;
        $this->assetFamilyExists = $assetFamilyExists;
        $this->assetIndexName = $assetIndexName;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'asset_family_codes',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of asset family codes to index',
                []
            )
            ->addOption(
                'all',
                true,
                InputOption::VALUE_NONE,
                'Index all existing assets into Elasticsearch'
            )
            ->setDescription('Index all the assets belonging to the given asset families.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkAssetIndexExists();

        $isIndexAll = $input->getOption('all');
        $assetFamilyCodes = $input->getArgument('asset_family_codes');

        if ($isIndexAll) {
            $this->indexAll($output);
        } elseif (0 < count($assetFamilyCodes)) {
            $this->indexByAssetFamily($assetFamilyCodes, $output);
        } else {
            $output->writeln('<error>Please specify a list of asset family codes to index or use the flag --all to index all assets</error>');

            return self::ERROR_CODE_USAGE;
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function checkAssetIndexExists()
    {
        if (!$this->assetClient->hasIndex()) {
            throw new \RuntimeException(
                sprintf(
                    'The index "%s" does not exist in Elasticsearch.',
                    $this->assetIndexName
                )
            );
        }
    }

    /**
     * @param OutputInterface $output
     *
     */
    protected function indexAll(OutputInterface $output): void
    {
        $allAssetFamilies = $this->assetFamilyRepository->all();
        $count = 0;
        foreach ($allAssetFamilies as $assetFamily) {
            /** @var AssetFamily $assetFamily */
            $this->assetIndexer->indexByAssetFamily($assetFamily->getIdentifier());
            $count++;
        }

        $output->writeln(sprintf('<info>The assets of %d asset families have been indexed.</info>', $count));
    }

    /**
     * @param string[] $assetFamilyCodes
     */
    private function indexByAssetFamily(array $assetFamilyCodes, OutputInterface $output): void
    {
        $existingAssetFamilyCodes = $this->getExistingAssetFamilyCodes($assetFamilyCodes, $output);

        foreach ($existingAssetFamilyCodes as $i => $assetFamilyIdentifier) {
            $output->writeln(sprintf('<info>Indexing the assets of "%s".</info>', $assetFamilyCodes[$i]));
            $this->assetIndexer->indexByAssetFamily($assetFamilyIdentifier);
        }
    }

    /**
     * @param String[] $assetFamilyCodes
     *
     * @return AssetFamilyIdentifier[]
     */
    private function getExistingAssetFamilyCodes(array $assetFamilyCodes, OutputInterface $output): array
    {
        $existingAssetFamilyCodes = [];
        foreach ($assetFamilyCodes as $assetFamilyCode) {
            if ($this->assetFamilyExists->withIdentifier(AssetFamilyIdentifier::fromString($assetFamilyCode))) {
                $existingAssetFamilyCodes[] = AssetFamilyIdentifier::fromString($assetFamilyCode);
            } else {
                $output->writeln(
                    sprintf('<info>Skip "%s", this asset family does not exist.</info>',
                        AssetFamilyIdentifier::fromString($assetFamilyCode))
                );
            }
        }

        return $existingAssetFamilyCodes;
    }
}
