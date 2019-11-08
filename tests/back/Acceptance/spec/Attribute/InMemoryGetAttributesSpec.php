<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\Attribute\InMemoryGetAttributes;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use PhpSpec\ObjectBehavior;

class InMemoryGetAttributesSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(new InMemoryAttributeRepository([
            (new Builder())->withCode('sku')->aIdentifier()->build(),
            (new Builder())->withCode('sku_2')->aIdentifier()->build()
        ]));
    }

    function it_is_a_query_to_get_attributes()
    {
        $this->shouldImplement(GetAttributes::class);
    }

    function it_is_a_in_memory_query()
    {
        $this->shouldBeAnInstanceOf(InMemoryGetAttributes::class);
    }

    function it_returns_attributes()
    {
        $this->forCodes(['sku', 'sku_2', 'foo'])->shouldBeLike([
            'sku' => new Attribute(
                'sku',
                AttributeTypes::IDENTIFIER,
                [],
                false,
                false,
                null,
                false,
                AttributeTypes::BACKEND_TYPE_TEXT,
                []
            ),
            'sku_2' => new Attribute(
                'sku_2',
                AttributeTypes::IDENTIFIER,
                [],
                false,
                false,
                null,
                false,
                AttributeTypes::BACKEND_TYPE_TEXT,
                []
            ),
        ]);
    }
}
