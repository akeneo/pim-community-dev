<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel;

use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\ChannelCompleteness;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\LocaleCompleteness;
use PhpSpec\ObjectBehavior;

class ChannelCompletenessSpec extends ObjectBehavior
{
    /** @var array */
    private $localeCompletenesses = [];

    function let()
    {
        $localeCompletenessFr = new LocaleCompleteness('French', 2);
        $localeCompletenessEn = new LocaleCompleteness('English', 8);
        $this->localeCompletenesses = [$localeCompletenessFr, $localeCompletenessEn];
        $labels = [
            'en_US' => 'Ecommerce US',
            'fr_FR' => 'Ecommerce FR'
        ];

        $this->beConstructedWith('ecommerce', 10, 20, $this->localeCompletenesses, $labels);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChannelCompleteness::class);
    }

    function it_can_returns_channel()
    {
        $this->channel()->shouldReturn('ecommerce');
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
            'labels' => [
                'en_US' => 'Ecommerce US',
                'fr_FR' => 'Ecommerce FR'
            ],
            "total" => 20,
            "complete" => 10,
            "locales" => ['French' => 2, 'English' => 8],
        ]);
    }
}
