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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SaveIdentifiersMappingCommandSpec extends ObjectBehavior
{
    public function it_is_an_update_identifiers_mapping_command(): void
    {
        $this->beConstructedWith([
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);

        $this->shouldHaveType(SaveIdentifiersMappingCommand::class);
    }

    public function it_returns_identifiers_mapping(): void
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

    public function it_does_not_fail_whatever_identifiers_order(): void
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

    public function it_throws_an_exception_if_identifiers_are_missing(): void
    {
        $mapping = [
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
        ];
        $this->beConstructedWith($mapping);

        $expected = IdentifiersMapping::FRANKLIN_IDENTIFIERS;
        $given = array_keys($mapping);
        sort($expected);
        sort($given);

        $this->shouldThrow(
            InvalidMappingException::missingOrInvalidIdentifiersInMapping(
                $given,
                SaveIdentifiersMappingCommand::class
            )
        )->duringInstantiation();
    }

    public function it_throws_an_exception_if_an_attribute_is_used_more_than_once(): void
    {
        $this->beConstructedWith([
            'brand' => 'ean',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);

        $this->shouldThrow(
            InvalidMappingException::duplicateAttributeCode(SaveIdentifiersMappingCommand::class)
        )->duringInstantiation();
    }
}
