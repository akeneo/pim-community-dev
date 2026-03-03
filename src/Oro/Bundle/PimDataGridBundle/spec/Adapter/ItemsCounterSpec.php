<?php

declare(strict_types=1);

namespace spec\Oro\Bundle\PimDataGridBundle\Adapter;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid\CountImpactedProducts;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeSearchableRepository;
use PhpSpec\ObjectBehavior;

class ItemsCounterSpec extends ObjectBehavior
{
    public function let(
        CountImpactedProducts $countImpactedProducts,
        AttributeSearchableRepository $countAttributes,
    ): void {
        $this->beConstructedWith($countImpactedProducts, $countAttributes);
    }

    public function it_counts_items_in_the_product_grid(CountImpactedProducts $countImpactedProducts): void
    {
        $countImpactedProducts->count(['filters'])->willReturn(42);

        $this->count('product-grid', ['filters'])->shouldReturn(42);
    }

    public function it_counts_items_in_the_attribute_grid(AttributeSearchableRepository $countAttributes): void
    {
        $countAttributes->count(null, [])->willReturn(6);

        $this->count('attribute-grid', [
            'search' => null,
            'options' => [],
        ])->shouldReturn(6);
    }

    public function it_counts_items_in_the_other_grids(): void
    {
        $this->count('family-grid', [
            ['value' => [1, 2, 3, 4, 5]]
        ])->shouldReturn(5);
    }

    public function it_raises_an_exception_when_unable_to_count_the_number_of_items(): void
    {
        $this->shouldThrow(\Exception::class)->during('count', ['family-grid', ['wrong filters']]);
    }
}
