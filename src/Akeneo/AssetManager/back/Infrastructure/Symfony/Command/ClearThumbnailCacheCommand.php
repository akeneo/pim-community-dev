<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ClearThumbnailCacheCommand extends Command
{
    protected static $defaultName = 'akeneo:asset-manager:thumbnail-cache:clear';

    public const ASSET_MANAGER_CACHE_RESOLVER = 'asset_manager_flysystem_cache';

    public function __construct(
        private CacheManager $cacheManager,
        private FilterConfiguration $filterConfiguration
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'preview_type',
                InputArgument::OPTIONAL,
                'Preview type of thumbnail'
            )
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Remove the thumbnails for all preview types of the asset manager'
            )
            ->setDescription('Remove cache entries for preview types of the asset manager.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $previewType = $input->getArgument('preview_type');
        $shouldClearAllPreviewTypes = $input->getOption('all');

        if ($shouldClearAllPreviewTypes && $previewType) {
            $output->writeln('<error>Preview type cannot be cleared with the --all option.</error>');

            return Command::FAILURE;
        }
        if (empty($previewType) && !$shouldClearAllPreviewTypes) {
            $output->writeln(
                '<error>Provide a preview type to clear its cache or use the --all option to clear all preview type caches at once.</error>'
            );

            return Command::FAILURE;
        }

        if ($shouldClearAllPreviewTypes) {
            $output->writeln('<info>Clearing all the thumbnail caches will cause the PIM to recreate the thumbnail caches the next time they are needed in the PIM which can cause performance issues.</info>');
            if (!$io->confirm('Are you sure you want to clear all the caches ?', true)) {
                return Command::FAILURE;
            }
        }
        $previewTypesToClear = $this->getSupportedPreviewTypes();
        if (!$shouldClearAllPreviewTypes) {
            if (!$this->checkPreviewTypeIsValid($io, $previewType, $previewTypesToClear)) {
                return Command::FAILURE;
            }
            $previewTypesToClear = [$previewType];
        }

        $output->writeln('<info>Clear thumbnail cache preview types:</info>');
        $io->listing($previewTypesToClear);
        $this->cacheManager->remove(null, $previewTypesToClear);

        $output->writeln('');
        $output->writeln('<info>Thumbnail cache successfully cleared</info>');

        return  Command::SUCCESS;
    }

    private function getSupportedPreviewTypes(): array
    {
        return array_keys(
            array_filter($this->filterConfiguration->all(), fn ($filterConfiguration) => isset($filterConfiguration['cache']) && $filterConfiguration['cache'] === self::ASSET_MANAGER_CACHE_RESOLVER)
        );
    }

    private function checkPreviewTypeIsValid(
        SymfonyStyle $io,
        string $previewType,
        array $previewTypesToClear
    ): bool {
        if (!in_array($previewType, $previewTypesToClear)) {
            $io->writeln(sprintf('<error>The preview type "%s" is not supported, Supported formats are:</error>', $previewType));
            $io->listing($previewTypesToClear);

            return false;
        }

        return true;
    }
}
