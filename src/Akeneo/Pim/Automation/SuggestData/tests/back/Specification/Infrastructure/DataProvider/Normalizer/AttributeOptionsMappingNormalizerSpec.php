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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeOption;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributeOptionsMappingNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionsMappingNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AttributeOptionsMappingNormalizer::class);
    }

    public function it_normalizes_attribute_options_mapping(): void
    {
        $mapping = new AttributeOptionsMapping();
        $mapping
            ->addAttributeOption(new AttributeOption('color1', 'red', 'color_1', 'red'))
            ->addAttributeOption(new AttributeOption('color2', 'blue', null, null))
            ->addAttributeOption(new AttributeOption('color3', 'yellow', 'color_3', null));

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
                    ],
                ],
            ],
            [
                'from' => [
                    'id' => 'color2',
                    'label' => [
                        'en_US' => 'blue',
                    ],
                ],
                'to' => null,
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
                    'label' => null,
                ],
            ],
        ];

        $this->normalize($mapping)->shouldReturn($expectedResult);
    }
}
