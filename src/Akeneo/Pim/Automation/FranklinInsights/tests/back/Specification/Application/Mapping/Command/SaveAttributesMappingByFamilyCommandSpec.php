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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\InvalidMappingException;
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
        $this->beConstructedWith('family_code', self::VALID_MAPPING);
        $this->getFamilyCode()->shouldReturn('family_code');
    }

    public function it_returns_an_attribute_mapping(): void
    {
        $this->beConstructedWith('family_code', self::VALID_MAPPING);

        $attributesMapping = $this->getMapping()->shouldReturn(self::VALID_MAPPING);
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

    public function it_throws_an_exception_if_a_pim_attribute_is_used_twice_or_more(): void
    {
        $mapping = [
            'main_color' => [
                'franklinAttribute' => ['type' => 'multiselect'],
                'attribute' => 'pim_color',
            ],
            'secondary_color' => [
                'franklinAttribute' => ['type' => 'multiselect'],
                'attribute' => 'pim_color',
            ],
            'test' => [
                'franklinAttribute' => ['type' => 'text'],
                'attribute' => null,
            ],
            'test2' => [
                'franklinAttribute' => ['type' => 'text'],
                'attribute' => '',
            ],
        ];

        $this->beConstructedWith('family_code', $mapping);

        $this->shouldThrow(AttributeMappingException::duplicatedPimAttribute())->duringInstantiation();
    }
}
