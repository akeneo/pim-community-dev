<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\CategoryUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveCategories;
use PhpSpec\ObjectBehavior;

class RemoveCategoriesSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['categoryA', 'categoryB']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveCategories::class);
        $this->shouldImplement(CategoryUserIntent::class);

        $this->categoryCodes()->shouldReturn(['categoryA', 'categoryB']);
    }

    function it_requires_non_empty_array()
    {
        $this->beConstructedWith([]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_requires_non_empty_values_in_array()
    {
        $this->beConstructedWith(['']);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_requires_string_values_in_the_array()
    {
        $this->beConstructedWith(['test', 42]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
