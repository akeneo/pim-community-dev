<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ElasticsearchResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\ResultInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ProductAndProductsModelDocumentTypeFacetFactory
{
    public function build(ResultInterface $result): ?Facet
    {
        Assert::isInstanceOf($result, ElasticsearchResult::class);

        $rawResult = $result->getRawResult();
        $aggregations = $rawResult['aggregations'] ?? [];
        $documentTypeAggregation = $aggregations[ProductAndProductsModelDocumentTypeFacetQuery::NAME] ?? null;
        if (!is_array($documentTypeAggregation)) {
            return null;
        }

        $facet = Facet::createEmptyWithName(ProductAndProductsModelDocumentTypeFacetQuery::NAME);
        foreach ($documentTypeAggregation['buckets'] ?? [] as $bucket) {
            $item = FacetItem::fromArray($bucket);
            $facet->addFacetItem($item);
        }

        return $facet;
    }
}
