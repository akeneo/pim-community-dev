<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Converter\InternalApi;

use Akeneo\Category\Application\Converter\Checker\InternalApiRequirementChecker;
use Akeneo\Category\Application\Converter\ConverterInterface;
use Akeneo\Category\Infrastructure\Converter\InternalApi\InternalApiToStd;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InternalApiToStdSpec extends ObjectBehavior
{
    public function let(InternalApiRequirementChecker $checker): void
    {
        $this->beConstructedWith($checker);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(InternalApiToStd::class);
        $this->shouldImplement(ConverterInterface::class);
    }

    public function it_converts($checker)
    {
        $data = [
            'id' => 1,
            'properties' => [
                'code' => 'mycode',
                'labels' => [
                    'fr_FR' => 'Chaussettes',
                    'en_US' => 'Socks'
                ]
            ],
            'attributes' => [
                'title_87939c45-1d85-4134-9579-d594fff65030_fr_FR' => [
                    "data" => "Les chaussures dont vous avez besoin !",
                    "locale" => "fr_FR",
                    "attribute_code" => "title_87939c45-1d85-4134-9579-d594fff65030"
                ],
            ]
        ];
        $expected = [
            'id' => 1,
            'code' => 'mycode',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ],
            'values' => [
                'title_87939c45-1d85-4134-9579-d594fff65030_fr_FR' => [
                    'data' => "Les chaussures dont vous avez besoin !",
                    'locale' => "fr_FR",
                    'attribute_code' => "title_87939c45-1d85-4134-9579-d594fff65030"
                ]
            ]
        ];
        $checker->check($data)->shouldBeCalled();
        $this->convert($data)->shouldReturn($expected);
    }
}
