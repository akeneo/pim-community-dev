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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\AttributeOptionsMappingNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsMappingNormalizerSpec extends ObjectBehavior
{
    public function let(AttributeOptionRepositoryInterface $attributeOptionRepository): void
    {
        $this->beConstructedWith($attributeOptionRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AttributeOptionsMappingNormalizer::class);
    }

    public function it_normalizes_attribute_options_mapping(
        $attributeOptionRepository
    ): void {
        $attributeCode = new AttributeCode('attribute_code_1');

        $mapping = new Write\AttributeOptionsMapping($attributeCode);
        $mapping
            ->addAttributeOption(new Write\AttributeOption('color1', 'red', 'color_1'))
            ->addAttributeOption(new Write\AttributeOption('color2', 'blue', null))
            ->addAttributeOption(new Write\AttributeOption('color3', 'yellow', 'color_3'));

        $option1 = new Read\AttributeOption('color_1', $attributeCode, [
            'en_US' => 'red',
            'fr_FR' => 'rouge'
        ]);
        $option2 = new Read\AttributeOption('color_3', $attributeCode, [
            'en_US' => 'yellow'
        ]);

        $attributeOptionRepository
            ->findByCodes(['color_1', 'color_3'])
            ->willReturn([$option1, $option2])
        ;

        $expectedResult = [
            [
                'from' => [
                    'id' => 'color1',
                    'label' => [
                        'en_US' => 'red',
                    ],
                ],
                'to' => [
                    'id' => 'color_1',
                    'label' => [
                        'en_US' => 'red',
                        'fr_FR' => 'rouge',
                    ],
                ],
                'status' => OptionMapping::STATUS_ACTIVE,
            ],
            [
                'from' => [
                    'id' => 'color2',
                    'label' => [
                        'en_US' => 'blue',
                    ],
                ],
                'to' => null,
                'status' => OptionMapping::STATUS_INACTIVE,
            ],
            [
                'from' => [
                    'id' => 'color3',
                    'label' => [
                        'en_US' => 'yellow',
                    ],
                ],
                'to' => [
                    'id' => 'color_3',
                    'label' => [
                        'en_US' => 'yellow',
                    ],
                ],
                'status' => OptionMapping::STATUS_ACTIVE,
            ],
        ];

        $this->normalize($mapping)->shouldReturn($expectedResult);
    }
}
