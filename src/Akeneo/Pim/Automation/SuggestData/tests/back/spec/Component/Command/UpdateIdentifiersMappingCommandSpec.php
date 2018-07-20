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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Automation\SuggestData\Component\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Component\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Component\Model\IdentifiersMapping;
use PhpSpec\ObjectBehavior;

class UpdateIdentifiersMappingCommandSpec extends ObjectBehavior
{
    function it_is_an_update_identifiers_mapping_command()
    {
        $this->beConstructedWith([
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);

        $this->shouldHaveType(UpdateIdentifiersMappingCommand::class);
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

    function it_throws_an_exception_if_identifiers_are_missing()
    {
        $mapping = [
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
        ];
        $this->beConstructedWith($mapping);

        $expected = IdentifiersMapping::PIM_AI_IDENTIFIERS;
        $given = array_keys($mapping);
        sort($expected);
        sort($given);

        $this->shouldThrow(
            InvalidMappingException::missingOrInvalidIdentifiersInMapping(
                $expected,
                $given,
                UpdateIdentifiersMappingCommand::class
            )
        )->duringInstantiation();
    }

    function it_throws_an_exception_if_an_attribute_is_used_more_than_once()
    {
        $this->beConstructedWith([
            'brand' => 'ean',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);

        $this->shouldThrow(
            InvalidMappingException::duplicateAttributeCode('2', 'ean', UpdateIdentifiersMappingCommand::class)
        )->duringInstantiation();
    }
}
