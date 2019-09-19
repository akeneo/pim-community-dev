<?php

declare(strict_types=1);

namespace spec\Pim\Component\Enrich\FollowUp\ReadModel;

use PhpSpec\ObjectBehavior;
use Pim\Component\Enrich\FollowUp\ReadModel\ChannelCompleteness;
use Pim\Component\Enrich\FollowUp\ReadModel\CompletenessWidget;
use Pim\Component\Enrich\FollowUp\ReadModel\LocaleCompleteness;

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
            'Ecommerce' => [
                'total' => 20,
                'complete' => 10,
                'locales' => ['French' => 2, 'English' => 8],
            ],
            'Mobile' => [
                'total' => 9,
                'complete' => 5,
                'locales' => ['French' => 3, 'English' => 5],
            ],
        ]);
    }
}
