<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Twig;

use PhpSpec\ObjectBehavior;

class LocaleExtensionSpec extends ObjectBehavior
{
    function it_is_a_twig_extension()
    {
        $this->shouldHaveType('Twig_Extension');
    }

    function it_have_filters()
    {
        $filters = $this->getFilters();

        $filters->shouldHaveCount(1);
        $filters[0]->shouldBeAnInstanceOf('\Twig_SimpleFilter');
        $filters[0]->getName()->shouldReturn('pretty_locale_name');
    }

    function it_returns_empty_locale_name()
    {
        $this->prettyLocaleName(null)->shouldReturn('');
    }

    function it_returns_pretty_locale_name()
    {
        $this->prettyLocaleName('en_US')->shouldReturn('English (United States)');
    }
}
