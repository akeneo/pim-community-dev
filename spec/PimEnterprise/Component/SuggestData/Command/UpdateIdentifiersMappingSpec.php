<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Component\SuggestData\Command;

use PimEnterprise\Component\SuggestData\Command\UpdateIdentifiersMapping;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\SuggestData\Exception\DuplicatedMappingAttributeException;

class UpdateIdentifiersMappingSpec extends ObjectBehavior
{
    function it_is_an_update_identifiers_mapping_command()
    {
        $this->beConstructedWith([
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);

        $this->shouldHaveType(UpdateIdentifiersMapping::class);
    }

    function it_returns_identifiers_mapping()
    {
        $identifiersMapping = [
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ];
        $this->beConstructedWith($identifiersMapping);

        $this->getIdentifiersMapping()->shouldReturn($identifiersMapping);
    }

    function it_does_not_fail_whatever_identifiers_order()
    {
        $identifiersMapping = [
            'mpn' => 'model',
            'brand' => 'manufacturer',
            'asin' => 'id',
            'upc' => 'ean',
        ];
        $this->beConstructedWith($identifiersMapping);

        $this->getIdentifiersMapping()->shouldReturn($identifiersMapping);
    }

    function it_throw_an_exception_if_identifiers_are_missing()
    {
        $this->beConstructedWith([
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
        ]);

        $this->shouldThrow(new \InvalidArgumentException('Some identifiers mapping are missing or invalid'))->duringInstantiation();
    }

    function it_throw_an_exception_if_an_attribute_is_used_more_than_one_time()
    {
        $this->beConstructedWith([
            'brand' => 'ean',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);

        $this->shouldThrow(new DuplicatedMappingAttributeException('An attribute cannot be used more that 1 time'))->duringInstantiation();
    }
}
