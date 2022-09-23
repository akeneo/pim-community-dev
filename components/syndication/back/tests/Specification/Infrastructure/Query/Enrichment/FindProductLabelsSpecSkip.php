<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Syndication\Infrastructure\Query\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface;
use Akeneo\Platform\Syndication\Infrastructure\Query\Enrichment\FindProductLabels;
use PhpSpec\ObjectBehavior;

class FindProductLabelsSpec extends ObjectBehavior
{
    public function let(
        GetProductLabelsInterface $getProductLabels
    ): void {
        $this->beConstructedWith($getProductLabels);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FindProductLabels::class);
    }

    public function it_finds_product_labels(
        GetProductLabelsInterface $getProductLabels
    ): void {
        $productCodes = ['vneck', 'watch', 'unknown'];
        $channelCode = 'ecommerce';
        $localeCode = 'fr_FR';

        $expectedLabel = ['vneck' => 'V-Neck', 'watch' => 'Montre'];
        $getProductLabels->byIdentifiersAndLocaleAndScope($productCodes, $localeCode, $channelCode)
            ->willReturn($expectedLabel);

        $this->byIdentifiers($productCodes, $channelCode, $localeCode)->shouldReturn($expectedLabel);
    }
}
