<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Facet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FacetOnDocumentTypeInterface
{
    public function add(SearchQueryBuilder $searchQueryBuilder): void;
}
