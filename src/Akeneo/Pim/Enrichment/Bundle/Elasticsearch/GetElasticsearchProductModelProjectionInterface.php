<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetElasticsearchProductModelProjectionInterface
{
    /**
     * @param string[] $productModelCodes
     *
     * @return ElasticsearchProductModelProjection[]
     */
    public function fromProductModelCodes(array $productModelCodes): array;
}
