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
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class UpdateAttributesMappingByFamilyCommandSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith('family_code', []);
        $this->shouldHaveType(UpdateAttributesMappingByFamilyCommand::class);
    }

    public function it_returns_the_family_code(): void
    {
        $familyCode = 'family_code';

        $this->beConstructedWith($familyCode, []);
        $this->getFamilyCode()->shouldReturn($familyCode);
    }

    public function it_returns_an_attribute_mapping(): void
    {
        $mapping = ['color' => [
            'pimAiAttribute' => ['type' => 'multiselect'],
            'attribute' => 'tshirt_style',
            'status' => 1,
        ]];
        $this->beConstructedWith('family_code', $mapping);

        $attributesMapping = $this->getAttributesMapping();
        $attributesMapping->shouldHaveCount(1);

        $attributeMapping = $attributesMapping[0];
        $attributeMapping->shouldBeAnInstanceOf(AttributeMapping::class);
        $attributeMapping->getPimAttributeCode()->shouldReturn('tshirt_style');
        $attributeMapping->getTargetAttributeCode()->shouldReturn('color');
        $attributeMapping->getStatus()->shouldReturn(1);
    }

    public function it_throws_an_exception_if_target_key_is_missing(): void
    {
        $mapping = [['attribute' => 'tshirt_style', 'status' => 1]];
        $this->beConstructedWith('family_code', $mapping);

        $this
            ->shouldThrow(InvalidMappingException::class)
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_attribute_key_is_missing(): void
    {
        $mapping = ['color' => ['status' => 1]];
        $this->beConstructedWith('family_code', $mapping);

        $this
            ->shouldThrow(InvalidMappingException::expectedKey('color', 'attribute'))
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_status_key_is_missing(): void
    {
        $mapping = ['color' => ['attribute' => 'tshirt_style']];
        $this->beConstructedWith('family_code', $mapping);

        $this
            ->shouldThrow(InvalidMappingException::expectedKey('color', 'status'))
            ->duringInstantiation();
    }
}
