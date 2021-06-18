<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\AttributeSelector\AttributeSelectorInterface;
use PhpSpec\ObjectBehavior;

class AttributeSelectorRegistrySpec extends ObjectBehavior
{
    public function it_returns_the_value_selected_by_the_first_valid_selector(
        AttributeSelectorInterface $firstAttributeSelector,
        AttributeSelectorInterface $secondAttributeSelector,
        AttributeSelectorInterface $thirdAttributeSelector,
        ValueInterface $value
    ) {
        $attribute = $this->createAttribute();
        $sourceConfiguration = ['type' => 'code'];
        $this->beConstructedWith([
            $firstAttributeSelector,
            $secondAttributeSelector,
            $thirdAttributeSelector
        ]);

        $firstAttributeSelector->supports($sourceConfiguration, $attribute)->shouldBeCalled()->willReturn(false);
        $secondAttributeSelector->supports($sourceConfiguration, $attribute)->shouldBeCalled()->willReturn(true);
        $thirdAttributeSelector->supports($sourceConfiguration, $attribute)->shouldNotBeCalled();

        $secondAttributeSelector
            ->applySelection($sourceConfiguration, $attribute, $value)
            ->shouldBeCalled()
            ->willReturn('The value selected');

        $this->applyAttributeSelection($sourceConfiguration, $attribute, $value)->shouldReturn('The value selected');
    }

    public function it_does_nothing_if_value_is_not_value_interface(AttributeSelectorInterface $firstAttributeSelector)
    {
        $attribute = $this->createAttribute();
        $sourceConfiguration = ['type' => 'code'];
        $this->beConstructedWith([
            $firstAttributeSelector,
        ]);

        $firstAttributeSelector->supports($sourceConfiguration, $attribute)->shouldNotBeCalled();

        $this
            ->applyAttributeSelection($sourceConfiguration, $attribute, 'Not a value interface')
            ->shouldReturn('Not a value interface');
    }

    public function it_throws_an_error_when_no_selector_is_found(
        AttributeSelectorInterface $firstAttributeSelector,
        ValueInterface $value
    ) {
        $attribute = $this->createAttribute();
        $sourceConfiguration = ['type' => 'code'];
        $this->beConstructedWith([
            $firstAttributeSelector,
        ]);

        $firstAttributeSelector->supports($sourceConfiguration, $attribute)->shouldBeCalled()->willReturn(false);

        $this->shouldThrow()->during('applyAttributeSelection', [$sourceConfiguration, $attribute, $value]);
    }

    private function createAttribute(): Attribute
    {
        return new Attribute(
            'description',
            'pim_catalog_boolean',
            [],
            false,
            false,
            null,
            null,
            null,
            'bool',
            []
        );
    }
}
