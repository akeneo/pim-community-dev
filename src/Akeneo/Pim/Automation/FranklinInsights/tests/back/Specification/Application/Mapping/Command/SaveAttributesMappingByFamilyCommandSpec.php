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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class SaveAttributesMappingByFamilyCommandSpec extends ObjectBehavior
{
    private const VALID_MAPPING = [
        'color' => [
            'franklinAttribute' => ['type' => 'multiselect'],
            'attribute' => 'tshirt_style',
        ],
    ];

    public function it_is_initializable(): void
    {
        $this->beConstructedWith('family_code', self::VALID_MAPPING);
        $this->shouldHaveType(SaveAttributesMappingByFamilyCommand::class);
    }

    public function it_returns_the_family_code(): void
    {
        $familyCode = 'family_code';

        $this->beConstructedWith($familyCode, self::VALID_MAPPING);
        $this->getFamilyCode()->shouldReturn($familyCode);
    }

    public function it_returns_an_attribute_mapping(): void
    {
        $this->beConstructedWith('family_code', self::VALID_MAPPING);

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
        $mapping = [['attribute' => 'tshirt_style']];
        $this->beConstructedWith('family_code', $mapping);

        $this
            ->shouldThrow(InvalidMappingException::class)
            ->duringInstantiation();
    }

    public function it_throws_an_exception_if_attribute_key_is_missing(): void
    {
        $mapping = ['color' => []];
        $this->beConstructedWith('family_code', $mapping);

        $this
            ->shouldThrow(InvalidMappingException::expectedKey('color', 'attribute'))
            ->duringInstantiation();
    }

    public function it_does_not_keep_in_account_status_key(): void
    {
        $mapping = ['color' => ['attribute' => 'tshirt_style', 'status' => 1]];
        $this->beConstructedWith('family_code', $mapping);

        $this
            ->shouldNotThrow(InvalidMappingException::expectedKey('color', 'status'))
            ->duringInstantiation();
    }
}
