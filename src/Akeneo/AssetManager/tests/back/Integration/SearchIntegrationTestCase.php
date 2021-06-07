<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration;

use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\EventAggregatorInterface;
use Akeneo\AssetManager\Integration\Persistence\Helper\SearchAssetIndexHelper;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This class is used for running integration tests testing the Search implementation of Elasticsearch queries or
 * custom filters.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class SearchIntegrationTestCase extends KernelTestCase
{
    protected SearchAssetIndexHelper $searchAssetIndexHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->searchAssetIndexHelper = $this->get('akeneoasset_manager.tests.helper.search_index_helper');
        $this->searchAssetIndexHelper->resetIndex();
    }

    protected function get(string $service)
    {
        return self::$container->get($service);
    }

    protected function flushAssetsToIndexCache(): void
    {
        /** @var EventAggregatorInterface $indexAssetsEventAggregator */
        $indexAssetsEventAggregator = $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset.index_asset_event_aggregator');
        $indexAssetsEventAggregator->flushEvents(); // Flushes the assets to index cache in the subscriber

        /** @var Client $assetClient */
        $assetClient = $this->get('akeneo_assetmanager.client.asset');
        $assetClient->refreshIndex();
    }
}
