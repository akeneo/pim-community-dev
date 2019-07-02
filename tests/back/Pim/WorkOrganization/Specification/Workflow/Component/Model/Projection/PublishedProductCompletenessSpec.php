<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use PhpSpec\ObjectBehavior;

class PublishedProductCompletenessSpec extends ObjectBehavior
{
    function it_is_a_product_completeness()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            30,
            ['name', 'brand', 'description', 'picture']
        );
        $this->shouldHaveType(PublishedProductCompleteness::class);
    }

    function it_throws_an_exception_if_required_count_is_negative()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            -5,
            ['name', 'brand', 'description', 'picture']
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_calculates_the_completeness_ratio()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            30,
            ['name', 'brand', 'description', 'picture']
        );
        $this->ratio()->shouldReturn(86);
    }

    function it_calculates_the_completeness_ratio_when_required_count_is_zero()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            0,
            []
        );
        $this->ratio()->shouldReturn(100);
    }
}
