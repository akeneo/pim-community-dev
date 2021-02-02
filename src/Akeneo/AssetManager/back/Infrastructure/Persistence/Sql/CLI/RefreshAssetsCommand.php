<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\CLI;

use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\RefreshAssets\FindAllAssetIdentifiers;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\RefreshAssets\RefreshAsset;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\CountAssets;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command refreshes all the assets after to have a asset linked, all assets of an asset family or attribute options linked deleted.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshAssetsCommand extends Command
{
    protected static $defaultName = self::REFRESH_ASSETS_COMMAND_NAME;

    public const REFRESH_ASSETS_COMMAND_NAME = 'akeneo:asset-manager:refresh-assets';
    private const BULK_SIZE = 100;

    private FindAllAssetIdentifiers $findAllAssetIdentifiers;
    private RefreshAsset $refreshAsset;
    private CountAssets $countAssets;

    public function __construct(
        FindAllAssetIdentifiers $findAllAssetIdentifiers,
        RefreshAsset $refreshAsset,
        CountAssetsInterface $countAssets
    ) {
        parent::__construct(self::REFRESH_ASSETS_COMMAND_NAME);

        $this->findAllAssetIdentifiers = $findAllAssetIdentifiers;
        $this->refreshAsset = $refreshAsset;
        $this->countAssets = $countAssets;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::REFRESH_ASSETS_COMMAND_NAME)
            ->addOption(
                'all',
                true,
                InputOption::VALUE_NONE,
                'Refresh all existing assets'
            )
            ->setDescription('Refresh all assets referencing a deleted asset or a deleted attribute option.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $isIndexAll = $input->getOption('all');
        if (!$isIndexAll) {
            $output->writeln('Please use the flag --all to refresh all assets');
        }

        $totalAssets = $this->countAssets->all();
        $progressBar = new ProgressBar($output, $totalAssets);
        $progressBar->start();

        $assetIdentifiers = $this->findAllAssetIdentifiers->fetch();
        $i = 0;
        foreach ($assetIdentifiers as $assetIdentifier) {
            $this->refreshAsset->refresh($assetIdentifier);
            if ($i % self::BULK_SIZE === 0) {
                $progressBar->advance(self::BULK_SIZE);
            }
            $i++;
        }
        $progressBar->finish();
    }
}
