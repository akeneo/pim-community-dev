<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Query\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use PhpSpec\ObjectBehavior;

class FindProductModelLabelsSpec extends ObjectBehavior
{
    public function let(
        GetProductModelLabelsInterface $getProductModelLabels
    ): void {
        $this->beConstructedWith($getProductModelLabels);
    }

    public function it_is_initializable(): void
    {
        $this->beAnInstanceOf(FindProductLabels::class);
    }

    public function it_finds_product_labels(
        GetProductModelLabelsInterface $getProductModelLabels
    ): void {
        $productModelCodes = ['vneck', 'watch'];
        $channelCode = 'ecommerce';
        $localeCode = 'fr_FR';

        $expectedLabel = ['vneck' => 'V-Neck', 'watch' => 'Montre'];
        $getProductModelLabels->byCodesAndLocaleAndScope($productModelCodes, $localeCode, $channelCode)
            ->willReturn($expectedLabel);

        $this->byCodes($productModelCodes, $channelCode, $localeCode)->shouldReturn($expectedLabel);
    }
}
