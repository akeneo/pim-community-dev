<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Facet\FacetOnDocumentTypeInterface;

/**
 * Counts the number of documents by document type (product / product model).
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FacetOnDocumentType implements FacetOnDocumentTypeInterface
{
    public const NAME = 'document_type_facet';
    private const FIELD = 'document_type';

    public function add(SearchQueryBuilder $searchQueryBuilder): void
    {
        $searchQueryBuilder->addTermsAggregation(static::NAME, static::FIELD);
    }
}
