<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet;

use Webmozart\Assert\Assert;

/**
 * The "facet" term is the old term in Elasticsearch, the new one is "aggregation". We use "facet" here to
 * differentiate the product model aggregation we have in PIM.
 *
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

    private function __construct(string $name, array $facetItems)
    {
        Assert::allIsInstanceOf($facetItems, FacetItem::class);

        $this->name = $name;
        $this->facetItems = $facetItems;
    }

    public static function createEmptyWithName(string $name): Facet
    {
        return new Facet($name, []);
    }

    public function addFacetItem(FacetItem $item): void
    {
        $this->facetItems[$item->getKey()] = $item;
    }

    public function getCountForKey(string $key): int
    {
        $facetItem = $this->facetItems[$key] ?? null;

        return null !== $facetItem ? $facetItem->getCount() : 0;
    }
}
