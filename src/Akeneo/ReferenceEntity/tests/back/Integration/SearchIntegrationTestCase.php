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

namespace Akeneo\ReferenceEntity\Integration;

use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\IndexAssetEventAggregator;
use Akeneo\ReferenceEntity\Integration\Persistence\Helper\SearchRecordIndexHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

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
    /** @var SearchRecordIndexHelper */
    protected $searchRecordIndexHelper;

    /** @var IndexAssetEventAggregator*/
    protected $indexAssetSubscriber;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->searchRecordIndexHelper = $this->get('akeneoreference_entity.tests.helper.search_index_helper');
        $this->indexAssetSubscriber = $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset.index_asset_event_aggregator');
        $this->searchRecordIndexHelper->resetIndex();
    }

    protected function indexAssets()
    {
        $this->indexAssetSubscriber->flushCache();
    }

    protected function get(string $service)
    {
        return self::getContainer()->get($service);
    }
}
