<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Component\SuggestData\Model;

use PhpSpec\ObjectBehavior;

class IdentifiersMappingSpec extends ObjectBehavior
{
    function let() {
        $this->beConstructedWith([
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);
    }

    function it_gets_identifiers() {
        $this->getIdentifiers()->shouldReturn([
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);
    }

    function it_get_an_identifier() {
        $this->getIdentifier('brand')->shouldReturn('manufacturer');
    }

    function it_fails_to_get_an_unknown_identifier() {
        $this->getIdentifier('burger')->shouldReturn(null);
    }

    function it_is_traversable() {
        $this->shouldHaveType(\Traversable::class);

        $this->getIterator()->shouldReturnAnInstanceOf(\Iterator::class);
    }
}
