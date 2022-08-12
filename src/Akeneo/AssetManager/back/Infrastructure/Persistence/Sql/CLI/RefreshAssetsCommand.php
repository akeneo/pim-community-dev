<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\CLI;

use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
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
    protected static $defaultDescription = 'Refresh all assets referencing a deleted asset or a deleted attribute option.';

    public const REFRESH_ASSETS_COMMAND_NAME = 'akeneo:asset-manager:refresh-assets';
    private const BULK_SIZE = 100;

    public function __construct(
        private FindAllAssetIdentifiers $findAllAssetIdentifiers,
        private RefreshAsset $refreshAsset,
        private CountAssetsInterface $countAssets
    ) {
        parent::__construct(self::REFRESH_ASSETS_COMMAND_NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'all',
                true,
                InputOption::VALUE_NONE,
                'Refresh all existing assets'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $verbose = $input->getOption('verbose') ?? false;
        $isIndexAll = $input->getOption('all');
        if (!$isIndexAll) {
            $output->writeln('Please use the flag --all to refresh all assets');
        }

        $totalAssets = $this->countAssets->all();
        $progressBar = new ProgressBar($output, $totalAssets);
        if ($verbose) {
            $progressBar->start();
        }

        $assetIdentifiers = $this->findAllAssetIdentifiers->fetch();
        $i = 0;
        foreach ($assetIdentifiers as $assetIdentifier) {
            try {
                $this->refreshAsset->refresh($assetIdentifier);
            } catch (AssetNotFoundException) {
                continue;
            } finally {
                $i++;
                if ($i % self::BULK_SIZE === 0 && $verbose) {
                    $progressBar->advance(self::BULK_SIZE);
                }
            }
        }

        if ($verbose) {
            $progressBar->finish();
        }

        return Command::SUCCESS;
    }
}
