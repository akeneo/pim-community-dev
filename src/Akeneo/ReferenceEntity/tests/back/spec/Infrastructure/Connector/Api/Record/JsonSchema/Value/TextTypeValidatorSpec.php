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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\Value;

use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValueValidatorInterface;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\Value\TextTypeValidator;
use PhpSpec\ObjectBehavior;

class TextTypeValidatorSpec extends ObjectBehavior
{
    function it_is_a_record_value_validator()
    {
        $this->shouldImplement(RecordValueValidatorInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextTypeValidator::class);
    }

    function it_returns_all_the_errors_of_invalid_record_values()
    {
        $record = [
            'values' => [
                'name' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 42
                    ],
                    [
                        'channel' => null,
                    ]
                ],
                'description' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                        'data'    => 'Description',
                        'foo'     => 'bar',
                    ]
                ]
            ]
        ];

        $errors = $this->validate($record);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(4);
    }

    function it_returns_an_empty_array_if_all_the_record_values_are_valid()
    {
        $record = [
            'values' => [
                'name' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'Name'
                    ],
                ]
            ]
        ];

        $this->validate($record)->shouldReturn([]);
    }
}
