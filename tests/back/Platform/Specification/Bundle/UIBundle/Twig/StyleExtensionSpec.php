<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Twig;

use PhpSpec\ObjectBehavior;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StyleExtensionSpec extends ObjectBehavior
{
    function it_is_a_twig_extension()
    {
        $this->shouldHaveType(AbstractExtension::class);
    }

    function it_defines_filters()
    {
        $filters = $this->getFilters();

        $filters->shouldHaveCount(1);
        $filters[0]->shouldBeAnInstanceOf(TwigFilter::class);
        $filters[0]->getName()->shouldReturn('highlight');
    }

    function it_highlights()
    {
        $this->highlight('toto')->shouldReturn('<span class="AknRule-attribute">toto</span>');
    }
}
