<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use PhpSpec\ObjectBehavior;

final class KeyIndicatorSpec extends ObjectBehavior
{
    public function it_returns_the_key_indicator_in_array_format()
    {
        $this->beConstructedWith(new KeyIndicatorCode('good_enrichment'), 1, 999, []);

        $this->toArray()->shouldReturn([
            'totalGood' => 1,
            'totalToImprove' => 999,
            'extraData' => [],
        ]);
    }

    public function it_determines_if_the_key_indicator_is_empty()
    {
        $this->beConstructedWith(new KeyIndicatorCode('good_enrichment'), 0, 0, []);

        $this->isEmpty()->shouldReturn(true);
    }

    public function it_determines_if_the_key_indicator_is_not_empty()
    {
        $this->beConstructedWith(new KeyIndicatorCode('good_enrichment'), 1, 0, []);

        $this->isEmpty()->shouldReturn(false);
    }
}
