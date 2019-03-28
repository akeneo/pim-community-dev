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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\AttributeOptionsMappingNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;

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
        $attributeOptionRepository,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2,
        AttributeOptionValueInterface $optionValue1,
        AttributeOptionValueInterface $optionValue2,
        AttributeOptionValueInterface $optionValue3
    ): void {
        $mapping = new AttributeOptionsMapping(new AttributeCode('color'));
        $mapping
            ->addAttributeOption(new AttributeOption('color1', 'red', 'color_1'))
            ->addAttributeOption(new AttributeOption('color2', 'blue', null))
            ->addAttributeOption(new AttributeOption('color3', 'yellow', 'color_3'));

        $optionValue1->getValue()->willReturn('red');
        $optionValue1->getLocale()->willReturn('en_US');
        $optionValue2->getValue()->willReturn('rouge');
        $optionValue2->getLocale()->willReturn('fr_FR');
        $optionValue3->getValue()->willReturn('yellow');
        $optionValue3->getLocale()->willReturn('en_US');

        $option1->getCode()->willReturn('color_1');
        $option1->getOptionValues()->willReturn([$optionValue1, $optionValue2]);

        $option2->getCode()->willReturn('color_3');
        $option2->getOptionValues()->willReturn([$optionValue3]);

        $attributeOptionRepository
            ->findBy([
                'attribute.code' => 'color',
                'code' => ['color_1', 'color_3']
            ])
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
