<?php

declare(strict_types=1);

namespace spec\Pim\Component\Enrich\FollowUp\ReadModel;

use PhpSpec\ObjectBehavior;
use Pim\Component\Enrich\FollowUp\ReadModel\LocaleCompleteness;

class LocaleCompletenessSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('French (Français)', 10);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleCompleteness::class);
    }

    function it_can_returns_locale_name()
    {
        $this->locale()->shouldReturn('French (Français)');
    }

    function it_can_returns_number_of_complete_products()
    {
        $this->numberOfCompleteProducts()->shouldReturn(10);
    }

    function it_transforms_into_an_array()
    {
        $this->toArray()->shouldReturn(['French (Français)' => 10]);
    }
}
