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
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\Value\RecordCollectionTypeValidator;
use PhpSpec\ObjectBehavior;

class RecordCollectionTypeValidatorSpec extends ObjectBehavior
{
    function it_is_a_record_value_validator()
    {
        $this->shouldImplement(RecordValueValidatorInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordCollectionTypeValidator::class);
    }

    function it_returns_all_the_errors_of_invalid_record_values()
    {
        $record = [
            'values' => [
                'materials' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => "plastic"
                    ],
                ],
                'colors' => [
                    [
                        'channel' => "ecommerce",
                        'locale'  => null,
                        'data'    => ["red", "blue"]
                    ],
                    [
                        'channel' => "mobile",
                        'locale'  => null,
                        'data'    => ["red", null]
                    ]
                ]
            ]
        ];

        $errors = $this->validate($record);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_an_empty_array_if_all_the_record_values_are_valid()
    {
        $record = [
            'values' => [
                'materials' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => ["plastic", "metal"]
                    ],
                ],
            ]
        ];

        $this->validate($record)->shouldReturn([]);
    }
}
