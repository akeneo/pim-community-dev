<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Infrastructure\Converter\InternalAPI;

use Akeneo\Category\Application\Converter\FieldsRequirementChecker;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InternalAPIToStdSpec extends ObjectBehavior
{
    public function let(FieldsRequirementChecker $fieldsRequirementChecker): void
    {
        $this->beConstructedWith($fieldsRequirementChecker);
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
            ]
        ];
        $expected = [
            'code' => 'mycode',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ],
        ];
        $this->convert($data)->shouldReturn($expected);
    }
}
