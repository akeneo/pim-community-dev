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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema;

use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordPropertiesValidator;
use PhpSpec\ObjectBehavior;

class RecordPropertiesValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RecordPropertiesValidator::class);
    }

    function it_returns_all_the_errors_of_invalid_record_properties()
    {
        $record = [
            'values' => null,
            'foo' => 'bar',
        ];

        $errors = $this->validate($record);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }


    function it_returns_an_empty_array_if_all_the_record_properties_are_valid()
    {
        $record = [
            'code' => 'starck',
            'values' => [
                'favorite_color' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => 'blue'
                    ],
                ],
            ],
        ];

        $this->validate($record)->shouldReturn([]);
    }

    function it_returns_an_empty_array_if_values_is_an_empty_array_instead_of_an_object_due_to_json_decode()
    {
        $record = [
            'code' => 'starck',
            'values' => [],
        ];

        $this->validate($record)->shouldReturn([]);
    }

    function it_accepts_links_in_order_to_update_a_record_previously_requested_with_the_api()
    {
        $record = [
            'code' => 'starck',
            '_links' => [
                'self' => [
                    'href' => 'http://localhost:8082/api/rest/v1/reference-entities/ref_test_2/records/0000747832346'
                ]
            ]
        ];

        $this->validate($record)->shouldReturn([]);
    }
}
