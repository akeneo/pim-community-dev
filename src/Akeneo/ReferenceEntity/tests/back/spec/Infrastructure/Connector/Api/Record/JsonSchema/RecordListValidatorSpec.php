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

use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordListValidator;
use PhpSpec\ObjectBehavior;

class RecordListValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RecordListValidator::class);
    }

    function it_returns_the_errors_of_an_invalid_list_of_records()
    {
        $recordList = [
            [
                'not a object'
            ],
            'not an array'
        ];

        $errors = $this->validate($recordList);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(2);
    }

    function it_returns_an_empty_array_if_the_list_of_records_is_valid()
    {
        $recordList = [
            [
                'code' => 'starck',
                'labels' => [
                    'en_US' => 'Philippe Starck',
                ],
            ],
            [
                'code' => 'dyson',
                'values' => [],
            ]
        ];

        $this->validate($recordList)->shouldBe([]);
    }
}
