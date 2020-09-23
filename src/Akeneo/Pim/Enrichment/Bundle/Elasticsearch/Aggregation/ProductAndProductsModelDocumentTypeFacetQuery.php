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

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;

/**
 * Counts the number of documents by document type (product / product model).
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ProductAndProductsModelDocumentTypeFacetQuery
{
    public const NAME = 'document_types';
    private const FIELD = 'document_type';

    public function addTo(SearchQueryBuilder $searchQueryBuilder): void
    {
        $searchQueryBuilder->addTermsAggregation(static::NAME, static::FIELD);
    }
}
