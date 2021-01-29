<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
interface AssetQueryBuilderInterface
{
    public function buildFromQuery(AssetQuery $assetQuery, $source): array;
}
