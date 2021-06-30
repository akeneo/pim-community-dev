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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector\PropertySelectorInterface;
use PhpSpec\ObjectBehavior;

class PropertySelectorRegistrySpec extends ObjectBehavior
{
    public function it_returns_the_value_selected_by_the_first_valid_selector(
        PropertySelectorInterface $firstPropertySelector,
        PropertySelectorInterface $secondPropertySelector,
        PropertySelectorInterface $thirdPropertySelector,
        ProductInterface $entity
    ) {
        $sourceConfiguration = ['type' => 'code'];
        $this->beConstructedWith([
            $firstPropertySelector,
            $secondPropertySelector,
            $thirdPropertySelector
        ]);

        $firstPropertySelector->supports($sourceConfiguration, 'categories')->shouldBeCalled()->willReturn(false);
        $secondPropertySelector->supports($sourceConfiguration, 'categories')->shouldBeCalled()->willReturn(true);
        $thirdPropertySelector->supports($sourceConfiguration, 'categories')->shouldNotBeCalled();

        $secondPropertySelector
            ->applySelection($sourceConfiguration, $entity)
            ->shouldBeCalled()
            ->willReturn('The value selected');

        $this->applyPropertySelection($sourceConfiguration, $entity, 'categories')->shouldReturn('The value selected');
    }

    public function it_throws_an_error_when_no_selector_is_found(
        PropertySelectorInterface $firstPropertySelector,
        ProductInterface $entity
    ) {
        $sourceConfiguration = ['type' => 'code'];
        $this->beConstructedWith([
            $firstPropertySelector,
        ]);

        $firstPropertySelector->supports($sourceConfiguration, 'associations')->shouldBeCalled()->willReturn(false);

        $this->shouldThrow()->during('applyPropertySelection', [$sourceConfiguration, $entity, 'associations']);
    }
}
