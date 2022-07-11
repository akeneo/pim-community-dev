<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer\ACLGetAttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ACLGetAttributeTypesSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ACLGetAttributeTypes::class);
    }

    function it_returns_attribute_types_from_attribute_codes(AttributeRepositoryInterface $attributeRepository)
    {
        $attributeRepository->getAttributeTypeByCodes(['sku', 'name', 'unknown'])->willReturn([
            'sku' => 'pim_catalog_identifier',
            'name' => 'pim_catalog_text',
        ]);

        $this->fromAttributeCodes(['sku', 'name', 'unknown'])->shouldReturn([
            'sku' => 'pim_catalog_identifier',
            'name' => 'pim_catalog_text',
        ]);
        $this->fromAttributeCodes([])->shouldReturn([]);
    }

    function it_throws_an_exception_when_input_data_is_not_valid()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromAttributeCodes', [['sku', true]]);
    }
}
