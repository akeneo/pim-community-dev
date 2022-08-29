<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Converter\InternalAPI;

use Akeneo\Category\Application\Converter\AttributeRequirementChecker;
use Akeneo\Category\Application\Converter\FieldsRequirementChecker;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InternalAPIToStdSpec extends ObjectBehavior
{
    public function let(
        FieldsRequirementChecker $fieldsRequirementChecker,
        AttributeRequirementChecker $attributeChecker
    ): void
    {
        $this->beConstructedWith($fieldsRequirementChecker, $attributeChecker);
    }

    public function it_converts()
    {
        $data = [
            'properties' => [
                'code' => 'mycode',
                'labels' => [
                    'fr_FR' => 'Chaussettes',
                    'en_US' => 'Socks'
                ]
            ],
            'attributes' => [
                'attribute_codes' => [
                    "title_87939c45-1d85-4134-9579-d594fff65030",
                ],
                'title_87939c45-1d85-4134-9579-d594fff65030_fr_FR' => [
                    "data" => "Les chaussures dont vous avez besoin !",
                    "locale" => "fr_FR",
                    "attribute_code" => "title_87939c45-1d85-4134-9579-d594fff65030"
                ],
            ]
        ];
        $expected = [
            'code' => 'mycode',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ],
            'values' => [
                'attribute_codes' => [
                    "title_87939c45-1d85-4134-9579-d594fff65030",
                ],
                'title_87939c45-1d85-4134-9579-d594fff65030_fr_FR' => [
                    'data' => "Les chaussures dont vous avez besoin !",
                    'locale' => "fr_FR",
                    'attribute_code' => "title_87939c45-1d85-4134-9579-d594fff65030"
                ]
            ]
        ];
        $this->convert($data)->shouldReturn($expected);
    }
}
