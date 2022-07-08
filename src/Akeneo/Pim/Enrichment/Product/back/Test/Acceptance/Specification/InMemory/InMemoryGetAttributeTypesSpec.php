<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory;

use Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory\InMemoryGetAttributeTypes;
use PhpSpec\ObjectBehavior;

class InMemoryGetAttributeTypesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGetAttributeTypes::class);
    }

    function it_returns_attribute_types()
    {
        $this->fromAttributeCodes(['sku', 'name'])->shouldReturn([]);

        $this->saveAttribute('sku', 'pim_catalog_identifier');
        $this->fromAttributeCodes(['sku', 'name'])->shouldReturn(['sku' => 'pim_catalog_identifier']);

        $this->saveAttribute('name', 'pim_catalog_text');
        $this->fromAttributeCodes(['sku', 'name'])->shouldReturn([
            'sku' => 'pim_catalog_identifier',
            'name' => 'pim_catalog_text',
        ]);
    }

    function it_returns_attribute_types_using_case_insensitive()
    {
        $this->saveAttribute('SKU', 'pim_catalog_identifier');
        $this->saveAttribute('naME', 'pim_catalog_text');

        $this->fromAttributeCodes(['sku', 'NAME'])->shouldReturn([
            'SKU' => 'pim_catalog_identifier',
            'naME' => 'pim_catalog_text',
        ]);
    }
}
