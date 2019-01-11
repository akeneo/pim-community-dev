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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class OptionsMappingNormalizerSpec extends ObjectBehavior
{
    public function it_normalizes_attribute_options_mapping(): void
    {
        $expectedResult = [
            'family' => 'router',
            'franklinAttributeCode' => 'color',
            'mapping' => [
                'color_2' => [
                    'franklinAttributeOptionCode' => [
                        'label' => 'blue',
                    ],
                    'catalogAttributeOptionCode' => 'color2',
                    'status' => 0,
                ],
                'color_1' => [
                    'franklinAttributeOptionCode' => [
                        'label' => 'red',
                    ],
                    'catalogAttributeOptionCode' => 'color1',
                    'status' => 1,
                ],
            ],
        ];

        $mapping = [
            new AttributeOptionMapping('color_1', 'red', 1, 'color1'),
            new AttributeOptionMapping('color_2', 'blue', 0, 'color2'),
        ];
        $attributeOptionsMapping = new AttributeOptionsMapping('router', 'color', $mapping);

        $this->normalize($attributeOptionsMapping)->shouldReturn($expectedResult);
    }
}
