<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\TailoredExport\Infrastructure\Connector\Processor\PropertySelector\PropertySelectorInterface;
use PhpSpec\ObjectBehavior;

class PropertySelectorRegistrySpec extends ObjectBehavior
{
    public function it_returns_the_value_selected_by_the_first_valid_selector(
        PropertySelectorInterface $firstPropertySelector,
        PropertySelectorInterface $secondPropertySelector,
        PropertySelectorInterface $thirdPropertySelector
    ) {
        $sourceConfiguration = ['type' => 'code'];
        $this->beConstructedWith([
            $firstPropertySelector,
            $secondPropertySelector,
            $thirdPropertySelector
        ]);

        $firstPropertySelector->supports($sourceConfiguration)->shouldBeCalled()->willReturn(false);
        $secondPropertySelector->supports($sourceConfiguration)->shouldBeCalled()->willReturn(true);
        $thirdPropertySelector->supports($sourceConfiguration)->shouldNotBeCalled();

        $secondPropertySelector
            ->applySelection($sourceConfiguration, [])
            ->shouldBeCalled()
            ->willReturn('The value selected');

        $this->applyPropertySelection($sourceConfiguration, [])->shouldReturn('The value selected');
    }

    public function it_throws_an_error_when_no_selector_is_found(
        PropertySelectorInterface $firstPropertySelector,
        ValueInterface $value
    )
    {
        $sourceConfiguration = ['type' => 'code'];
        $this->beConstructedWith([
            $firstPropertySelector,
        ]);

        $firstPropertySelector->supports($sourceConfiguration)->shouldBeCalled()->willReturn(false);

        $this->shouldThrow()->during('applyPropertySelection', [$sourceConfiguration, $value]);
    }
}
