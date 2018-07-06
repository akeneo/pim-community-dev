<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class IdentifiersMappingSpec extends ObjectBehavior
{
    function let(
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $sku
    ) {
        $this->beConstructedWith([
            'brand' => $manufacturer,
            'mpn' => $model,
            'upc' => $ean,
            'asin' => $sku,
        ]);
    }

    function it_gets_identifiers($manufacturer, $model, $ean, $sku) {
        $this->getIdentifiers()->shouldReturn([
            'brand' => $manufacturer,
            'mpn' => $model,
            'upc' => $ean,
            'asin' => $sku,
        ]);
    }

    function it_gets_an_identifier($manufacturer) {
        $this->getIdentifier('brand')->shouldReturn($manufacturer);
    }

    function it_fails_to_get_an_unknown_identifier() {
        $this->getIdentifier('burger')->shouldReturn(null);
    }

    public function it_normalizes_identifiers($manufacturer, $model, $ean, $sku) {
        $manufacturer->getCode()->willReturn('brand');
        $model->getCode()->willReturn('mpn');
        $ean->getCode()->willReturn('ean');
        $sku->getCode()->willReturn('sku');

        $this->normalize()->shouldReturn([
            'brand' => 'brand',
            'mpn' => 'mpn',
            'upc' => 'ean',
            'asin' => 'sku',
        ]);
    }

    function it_is_traversable() {
        $this->shouldHaveType(\Traversable::class);

        $this->getIterator()->shouldReturnAnInstanceOf(\Iterator::class);
    }
}
