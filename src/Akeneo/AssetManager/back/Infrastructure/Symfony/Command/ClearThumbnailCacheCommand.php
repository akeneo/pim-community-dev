<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

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
use Symfony\Component\Console\Output\OutputInterface;

class ClearThumbnailCacheCommand extends Command
{
    protected static $defaultName = 'akeneo:asset-manager:thumbnail-cache:clear';

    const ASSET_MANAGER_CACHE_RESOLVER = 'asset_manager_flysystem_cache';

    private CacheManager $cacheManager;

    private FilterConfiguration $filterConfiguration;

    public function __construct(CacheManager $cacheManager, FilterConfiguration $filterConfiguration)
    {
        parent::__construct();

        $this->cacheManager = $cacheManager;
        $this->filterConfiguration = $filterConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'preview_type',
                InputArgument::REQUIRED,
                'Preview type of thumbnail'
            )
            ->setDescription('Remove cache entries for given preview type.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $previewType = $input->getArgument('preview_type');
        $supportedPreviewTypes = $this->getSupportedPreviewTypes();
        if (! in_array($previewType, $supportedPreviewTypes)) {
            throw new \RuntimeException(
                sprintf('The preview type "%s" is not supported.\n Supported formats are: %s',
                    $previewType,
                    implode(', ', $supportedPreviewTypes)
                )
            );
        }

        $output->writeln(sprintf('<info>Clear thumbnail cache for "%s".</info>', $previewType));

        $this->cacheManager->remove(null, [$previewType]);
    }

    private function getSupportedPreviewTypes(): array
    {
        return array_keys(array_filter($this->filterConfiguration->all(), fn($filterConfiguration) => isset($filterConfiguration['cache']) && $filterConfiguration['cache'] === self::ASSET_MANAGER_CACHE_RESOLVER));
    }
}
