<?php

declare(strict_types=1);

namespace Specification\AkeneoEnterprise\Category\Infrastructure\Converter\InternalApi;

use Akeneo\Category\Application\Converter\ConverterInterface;
use AkeneoEnterprise\Category\Application\Converter\Checker\InternalApiPermissionRequirementChecker;
use AkeneoEnterprise\Category\Infrastructure\Converter\InternalApi\InternalApiPermissionToStd;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InternalApiPermissionToStdSpec extends ObjectBehavior
{
    public function let(InternalApiPermissionRequirementChecker $checker): void
    {
        $this->beConstructedWith($checker);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(InternalApiPermissionToStd::class);
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
                'attribute_codes' => [
                    "title_87939c45-1d85-4134-9579-d594fff65030",
                ],
                'title_87939c45-1d85-4134-9579-d594fff65030_fr_FR' => [
                    "data" => "Les chaussures dont vous avez besoin !",
                    "locale" => "fr_FR",
                    "attribute_code" => "title_87939c45-1d85-4134-9579-d594fff65030"
                ],
            ],
            'permissions' => [
                'view' => [
                    ['id' => 1, 'label' => 'User group 1'],
                    ['id' => 2, 'label' => 'User group 2'],
                ],
                'edit' => [
                    ['id' => 1, 'label' => 'User group 1'],
                    ['id' => 2, 'label' => 'User group 2'],
                ],
                'own' => [
                    ['id' => 1, 'label' => 'User group 1'],
                    ['id' => 2, 'label' => 'User group 2'],
                ]
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
                'attribute_codes' => [
                    "title_87939c45-1d85-4134-9579-d594fff65030",
                ],
                'title_87939c45-1d85-4134-9579-d594fff65030_fr_FR' => [
                    'data' => "Les chaussures dont vous avez besoin !",
                    'locale' => "fr_FR",
                    'attribute_code' => "title_87939c45-1d85-4134-9579-d594fff65030"
                ]
            ],
            'permissions' => [
                'view' => [
                    ['id' => 1, 'label' => 'User group 1'],
                    ['id' => 2, 'label' => 'User group 2'],
                ],
                'edit' => [
                    ['id' => 1, 'label' => 'User group 1'],
                    ['id' => 2, 'label' => 'User group 2'],
                ],
                'own' => [
                    ['id' => 1, 'label' => 'User group 1'],
                    ['id' => 2, 'label' => 'User group 2'],
                ]
            ]
        ];
        $checker->check($data)->shouldBeCalled();
        $this->convert($data)->shouldReturn($expected);
    }
}
