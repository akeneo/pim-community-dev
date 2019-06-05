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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Exception\AttributeOptionsMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeOptionCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsSpec extends ObjectBehavior
{
    private const OPTIONS_EXAMPLE = [
        'color_1' => [
            'franklinAttributeOptionCode' => [
                'label' => 'Color 1',
            ],
            'catalogAttributeOptionCode' => 'color1',
            'status' => 0,
        ],
        'color_2' => [
            'franklinAttributeOptionCode' => [
                'label' => 'Color 2',
            ],
            'catalogAttributeOptionCode' => 'color2',
            'status' => 1,
        ],
    ];

    public function let(): void
    {
        $this->beConstructedWith(self::OPTIONS_EXAMPLE);
    }

    public function it_is_a_attribute_options(): void
    {
        $this->shouldBeAnInstanceOf(AttributeOptions::class);
    }

    public function it_is_iterable(): void
    {
        $this->shouldHaveType(\IteratorAggregate::class);
    }

    public function it_gets_catalog_option_codes(): void
    {
        $this->getCatalogOptionCodes()->shouldBeLike([new AttributeOptionCode('color1'), new AttributeOptionCode('color2')]);
    }

    public function it_throws_an_exception_if_mapping_is_empty(): void
    {
        $this->beConstructedWith([]);
        $this->shouldThrow()->duringInstantiation(AttributeOptionsMappingException::class);
    }

    public function it_throws_an_exception_if_franklin_code_is_empty(): void
    {
        $this->beConstructedWith([
            '' => [
                'franklinAttributeOptionCode' => [
                    'label' => 'Color 1',
                ],
                'catalogAttributeOptionCode' => 'color1',
                'status' => 0,
            ],
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_an_expected_key_is_missing(): void
    {
        $this->beConstructedWith([
            'color_1' => [
                'franklinAttributeOptionCode' => [
                    'label' => 'Color 1',
                ],
                'catalogAttributeOptionCode' => 'color1',
            ],
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
