<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel;

use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\ChannelCompleteness;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\CompletenessWidget;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\LocaleCompleteness;
use PhpSpec\ObjectBehavior;

class CompletenessWidgetSpec extends ObjectBehavior
{
    /** @var array */
    private $channelCompletenesses = [];

    function let()
    {
        $localeCompletenessFr = new LocaleCompleteness('French', 2);
        $localeCompletenessEn = new LocaleCompleteness('English', 8);
        $channelCompletenessEcommerce = new ChannelCompleteness('Ecommerce', 10, 20, [$localeCompletenessFr, $localeCompletenessEn]);
        $this->channelCompletenesses[] = $channelCompletenessEcommerce;

        $localeCompletenessFr = new LocaleCompleteness('French', 3);
        $localeCompletenessEn = new LocaleCompleteness('English', 5);
        $channelCompletenessMobile = new ChannelCompleteness('Mobile', 5, 9, [$localeCompletenessFr, $localeCompletenessEn]);
        $this->channelCompletenesses[] = $channelCompletenessMobile;

        $this->beConstructedWith($this->channelCompletenesses);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessWidget::class);
    }

    function it_transforms_into_an_array()
    {
        $this->toArray()->shouldReturn([
            "Ecommerce" => [
                'labels' => [],
                "total" => 20,
                "complete" => 10,
                "locales" => ['French' => 2, 'English' => 8]
            ],
            "Mobile" => [
                'labels' => [],
                "total" => 9,
                "complete" => 5,
                "locales" => ['French' => 3, 'English' => 5]
            ]
        ]);
    }
}
