<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use PhpSpec\ObjectBehavior;

final class KeyIndicatorSpec extends ObjectBehavior
{
    public function it_calculates_the_ratio_of_good_items()
    {
        $this->beConstructedWith(new KeyIndicatorCode('good_enrichment'), 51, 149, []);

        $this->getRatioGood()->shouldReturn(26);
    }

    public function it_calculates_the_ratio_of_only_good_items()
    {
        $this->beConstructedWith(new KeyIndicatorCode('good_enrichment'), 42, 0, []);

        $this->getRatioGood()->shouldReturn(100);
    }

    public function it_calculates_the_ratio_of_no_good_items()
    {
        $this->beConstructedWith(new KeyIndicatorCode('good_enrichment'), 0, 77, []);

        $this->getRatioGood()->shouldReturn(0);
    }

    public function it_rounds_down_the_right_extremity()
    {
        $this->beConstructedWith(new KeyIndicatorCode('good_enrichment'), 999, 1, []);

        $this->getRatioGood()->shouldReturn(99);
    }

    public function it_rounds_up_the_left_extremity()
    {
        $this->beConstructedWith(new KeyIndicatorCode('good_enrichment'), 1, 999, []);

        $this->getRatioGood()->shouldReturn(1);
    }

    public function it_determines_if_the_key_indicator_is_empty()
    {
        $this->beConstructedWith(new KeyIndicatorCode('good_enrichment'), 0, 0, []);

        $this->isEmpty()->shouldReturn(true);
        $this->getRatioGood()->shouldReturn(0);
    }

    public function it_determines_if_the_key_indicator_is_not_empty()
    {
        $this->beConstructedWith(new KeyIndicatorCode('good_enrichment'), 1, 0, []);

        $this->isEmpty()->shouldReturn(false);
    }
}
