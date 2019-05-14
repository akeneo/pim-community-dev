<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EmptyValuesCleanerSpec extends ObjectBehavior
{
    public function it_has_a_type()
    {
        $this->shouldHaveType(EmptyValuesCleaner::class);
    }

    public function it_cleans_empty_or_null_values()
    {
        $rawValues = [
            'productA' => [
                'color' => [
                    'ecommerce' => [
                        '<all_locales>' => ''
                    ],
                    'tablet' => [
                        '<all_locales>' => 'red'
                    ]
                ],
                'colors' => [
                    'ecommerce' => [
                        '<all_locales>' => []
                    ],
                    'tablet' => [
                        '<all_locales>' => ['blue']
                    ]
                ]
            ],
            'productB' => [
                'an_attribute' => [
                    '<all_channels>' => [
                        'en_US' => null,
                        'fr_FR' => 'a_value',
                        'be_BE' => ''
                    ]
                ]
            ]
        ];

        $expected = [
            'productA' => [
                'color' => [
                    'tablet' => [
                        '<all_locales>' => 'red'
                    ]
                ],
                'colors' => [
                    'tablet' => [
                        '<all_locales>' => ['blue']
                    ]
                ]
            ],
            'productB' => [
                'an_attribute' => [
                    '<all_channels>' => [
                        'fr_FR' => 'a_value',
                    ]
                ]
            ]
        ];

        $this->cleanAllValues($rawValues)->shouldBeLike($expected);
    }
}
