<?php

declare(strict_types=1);

namespace spec\Pim\Component\Enrich\FollowUp\ReadModel;

use PhpSpec\ObjectBehavior;
use Pim\Component\Enrich\FollowUp\ReadModel\ChannelCompleteness;
use Pim\Component\Enrich\FollowUp\ReadModel\LocaleCompleteness;

class ChannelCompletenessSpec extends ObjectBehavior
{
    /** @var array */
    private $localeCompletenesses = [];

    function let()
    {
        $localeCompletenessFr = new LocaleCompleteness('French', 2);
        $localeCompletenessEn = new LocaleCompleteness('English', 8);
        $this->localeCompletenesses = [$localeCompletenessFr, $localeCompletenessEn];
        $this->beConstructedWith('Ecommerce', 10, 20, $this->localeCompletenesses);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelCompleteness::class);
    }

    function it_can_returns_channel()
    {
        $this->channel()->shouldReturn('Ecommerce');
    }

    function it_can_returns_number_of_complete_products()
    {
        $this->numberOfCompleteProducts()->shouldReturn(10);
    }

    function it_can_returns_number_total_of_products()
    {
        $this->numberTotalOfProducts()->shouldReturn(20);
    }

    function it_can_return_array_locale_completenesses()
    {
        $this->localeCompletenesses()->shouldReturn($this->localeCompletenesses);
    }

    function it_transforms_into_an_array()
    {
        $this->toArray()->shouldReturn([
            'total' => 20,
            'complete' => 10,
            'locales' => ['French' => 2, 'English' => 8],
        ]);
    }
}
