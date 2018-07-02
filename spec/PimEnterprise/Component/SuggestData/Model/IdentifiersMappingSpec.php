<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Component\SuggestData\Model;

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

    function it_gets_identifiers(
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $sku
    ) {
        $this->getIdentifiers()->shouldReturn([
            'brand' => $manufacturer,
            'mpn' => $model,
            'upc' => $ean,
            'asin' => $sku,
        ]);
    }

    function it_get_an_identifier(AttributeInterface $manufacturer) {
        $this->getIdentifier('brand')->shouldReturn($manufacturer);
    }

    function it_fails_to_get_an_unknown_identifier() {
        $this->getIdentifier('burger')->shouldReturn(null);
    }

    public function it_normalize_identifiers(
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $sku
    )
    {
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
