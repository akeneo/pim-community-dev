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

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Aggregation;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ProductAndProductsModelDocumentTypeFacetFactory
{
    public function build(array $queryResult): Aggregation
    {
        $aggregation = new Aggregation(ProductAndProductsModelDocumentTypeFacetQuery::NAME);

        $aggregations = $queryResult['aggregations'] ?? [];
        $documentTypeAggregation = $aggregations[ProductAndProductsModelDocumentTypeFacetQuery::NAME] ?? null;
        if (!is_array($documentTypeAggregation)) {
            return $aggregation;
        }

        foreach ($documentTypeAggregation['buckets'] ?? [] as $bucket) {
            $aggregation->addBucket($bucket['key'], $bucket['doc_count']);
        }

        return $aggregation;
    }
}
