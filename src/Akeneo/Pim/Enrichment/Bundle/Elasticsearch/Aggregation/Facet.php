<?php


declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Aggregation;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Facet
{
    /** @var string */
    private $name;

    /** @var FacetItem[] */
    private $facetItems = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addFacetItem(string $key, int $count): void
    {
        $this->facetItems[$key] =  new FacetItem($key, $count);
    }

    public function getCountForKey(string $key): int
    {
        $facetItem = $this->facetItems[$key] ?? null;

        return null !== $facetItem ? $facetItem->getCount() : 0;
    }
}
