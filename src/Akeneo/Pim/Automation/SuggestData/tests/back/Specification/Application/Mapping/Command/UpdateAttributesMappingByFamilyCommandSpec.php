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

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributesMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class UpdateAttributesMappingByFamilyCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('family_code', []);
        $this->shouldHaveType(UpdateAttributesMappingByFamilyCommand::class);
    }

    function it_returns_the_family_code()
    {
        $familyCode = 'family_code';

        $this->beConstructedWith($familyCode, []);
        $this->getFamilyCode()->shouldReturn($familyCode);
    }

    function it_returns_an_attribute_mapping()
    {
        $mapping = ['color' => ['attribute' => 'tshirt_style', 'status' => 1]];
        $this->beConstructedWith('family_code', $mapping);

        $attributesMapping = $this->getAttributesMapping();
        $attributesMapping->shouldHaveCount(1);

        $attributeMapping = $attributesMapping[0];
        $attributeMapping->shouldBeAnInstanceOf(AttributeMapping::class);
        $attributeMapping->getPimAttributeCode()->shouldReturn('tshirt_style');
        $attributeMapping->getTargetAttributeCode()->shouldReturn('color');
        $attributeMapping->getStatus()->shouldReturn(1);
    }

    function it_throws_an_exception_if_target_key_is_missing()
    {
        $mapping = [['attribute' => 'tshirt_style', 'status' => 1]];
        $this->beConstructedWith('family_code', $mapping);

        $this
            ->shouldThrow(InvalidMappingException::class)
            ->duringInstantiation();
    }

    function it_throws_an_exception_if_attribute_key_is_missing()
    {
        $mapping = ['color' => ['status' => 1]];
        $this->beConstructedWith('family_code', $mapping);

        $this
            ->shouldThrow(InvalidMappingException::expectedKey('color', 'attribute'))
            ->duringInstantiation();
    }

    function it_throws_an_exception_if_status_key_is_missing()
    {
        $mapping = ['color' => ['attribute' => 'tshirt_style']];
        $this->beConstructedWith('family_code', $mapping);

        $this
            ->shouldThrow(InvalidMappingException::expectedKey('color', 'status'))
            ->duringInstantiation();
    }
}
