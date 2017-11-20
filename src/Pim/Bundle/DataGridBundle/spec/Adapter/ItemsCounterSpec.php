<?php

declare(strict_types=1);

namespace spec\Pim\Bundle\DataGridBundle\Adapter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Query\CountImpactedProducts;

class ItemsCounterSpec extends ObjectBehavior
{
    function let(CountImpactedProducts $countImpactedProducts)
    {
        $this->beConstructedWith($countImpactedProducts);
    }

    function it_counts_items_in_the_product_grid($countImpactedProducts)
    {
        $countImpactedProducts->count(['filters'])->willReturn(42);

        $this->count('product-grid', ['filters'])->shouldReturn(42);
    }

    function it_counts_items_in_the_other_grids()
    {
        $this->count('family-grid', [
            ['value' => [1, 2, 3, 4, 5]]
        ])->shouldReturn(5);
    }

    function it_raises_an_exception_when_unable_to_count_the_number_of_items()
    {
        $this->shouldThrow(\Exception::class)->during('count', ['family-grid', ['wrong filters']]);
    }
}
